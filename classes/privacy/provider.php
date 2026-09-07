<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Privacy provider.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy API implementation for learner progress.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describes stored progress and browser connections to external content.
     *
     * @param collection $collection Metadata collection.
     * @return collection Updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link('externalcontent', [
            'ipaddress' => 'privacy:metadata:externalcontent:ipaddress',
            'useragent' => 'privacy:metadata:externalcontent:useragent',
        ], 'privacy:metadata:externalcontent');
        $collection->add_database_table('interactembed_progress', [
            'userid' => 'privacy:metadata:progress:userid',
            'status' => 'privacy:metadata:progress:status',
            'score' => 'privacy:metadata:progress:score',
            'scoremax' => 'privacy:metadata:progress:scoremax',
            'firstinteraction' => 'privacy:metadata:progress:firstinteraction',
            'lastinteraction' => 'privacy:metadata:progress:lastinteraction',
            'timecompleted' => 'privacy:metadata:progress:timecompleted',
        ], 'privacy:metadata:progress');
        return $collection;
    }

    /**
     * Returns contexts containing progress for one user.
     *
     * @param int $userid User id.
     * @return contextlist Context list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $list = new contextlist();
        $sql = "SELECT ctx.id FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :level
                  JOIN {modules} m ON m.id = cm.module AND m.name = :module
                  JOIN {interactembed_progress} p ON p.interactembedid = cm.instance
                 WHERE p.userid = :userid";
        $list->add_from_sql($sql, ['level' => CONTEXT_MODULE, 'module' => 'interactembed', 'userid' => $userid]);
        return $list;
    }

    /**
     * Adds users whose progress is stored in a context.
     *
     * @param userlist $userlist Context user list to populate.
     */
    public static function get_users_in_context(userlist $userlist): void {
        if (!$userlist->get_context() instanceof \context_module) {
            return;
        }
        $sql = "SELECT p.userid FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :module
                  JOIN {interactembed_progress} p ON p.interactembedid = cm.instance
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, [
            'module' => 'interactembed', 'cmid' => $userlist->get_context()->instanceid,
        ]);
    }

    /**
     * Exports progress belonging to the approved user and contexts.
     *
     * @param approved_contextlist $contextlist Approved export contexts.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_module) {
                continue;
            }
            $cm = get_coursemodule_from_id('interactembed', $context->instanceid);
            $progress = $cm ? $DB->get_record('interactembed_progress', [
                'interactembedid' => $cm->instance, 'userid' => $userid,
            ]) : false;
            if (!$progress) {
                continue;
            }
            unset($progress->id, $progress->interactembedid, $progress->userid);
            foreach (['firstinteraction', 'lastinteraction', 'timecompleted'] as $field) {
                if (!empty($progress->{$field})) {
                    $progress->{$field} = transform::datetime($progress->{$field});
                }
            }
            writer::with_context($context)->export_data([get_string('pluginname', 'mod_interactembed')], $progress);
        }
    }

    /**
     * Deletes all progress in one activity context.
     *
     * @param \context $context Context whose progress must be deleted.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if (
            $context instanceof \context_module &&
                ($cm = get_coursemodule_from_id('interactembed', $context->instanceid))
        ) {
            $DB->delete_records('interactembed_progress', ['interactembedid' => $cm->instance]);
        }
    }

    /**
     * Deletes progress for one user in approved contexts.
     *
     * @param approved_contextlist $contextlist Approved deletion contexts.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        foreach ($contextlist->get_contexts() as $context) {
            if (
                $context instanceof \context_module &&
                    ($cm = get_coursemodule_from_id('interactembed', $context->instanceid))
            ) {
                $DB->delete_records('interactembed_progress', [
                    'interactembedid' => $cm->instance, 'userid' => $contextlist->get_user()->id,
                ]);
            }
        }
    }

    /**
     * Deletes progress for approved users in one context.
     *
     * @param approved_userlist $userlist Approved users in one context.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;
        $context = $userlist->get_context();
        if (
            !$context instanceof \context_module ||
                !($cm = get_coursemodule_from_id('interactembed', $context->instanceid))
        ) {
            return;
        }
        [$insql, $params] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params['activity'] = $cm->instance;
        $DB->delete_records_select('interactembed_progress', "interactembedid = :activity AND userid $insql", $params);
    }
}
