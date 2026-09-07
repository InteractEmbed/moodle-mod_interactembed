<?php
// This file is part of Moodle - http://moodle.org/
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
 * Backup structure for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @category  backup
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the record and file areas included in an activity backup.
 */
class backup_interactembed_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the activity data and Files API areas to back up.
     *
     * @return backup_nested_element Prepared activity structure.
     */
    protected function define_structure() {
        $activity = new backup_nested_element('interactembed', ['id'], [
            'name',
            'intro',
            'introformat',
            'sourcetype',
            'sourceurl',
            'entrypoint',
            'packageitemid',
            'revision',
            'coveralt',
            'aspectratio',
            'customheight',
            'displaymode',
            'loadonrequest',
            'showfullscreen',
            'lazyload',
            'sandboxprofile',
            'grade',
            'completioncontent',
            'showembedcode',
            'timecreated',
            'timemodified',
        ]);
        $package = new backup_nested_element('package', ['id'], ['revision']);
        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', ['id'], [
            'userid', 'status', 'score', 'scoremax', 'firstinteraction', 'lastinteraction', 'timecompleted',
        ]);
        $activity->add_child($package);
        $activity->add_child($progresses);
        $progresses->add_child($progress);
        $activity->set_source_table('interactembed', ['id' => backup::VAR_ACTIVITYID]);
        $package->set_source_sql(
            "SELECT packageitemid AS id, revision
               FROM {interactembed}
              WHERE id = ? AND sourcetype = 'package' AND packageitemid > 0",
            [backup::VAR_PARENTID]
        );
        if ($this->get_setting_value('userinfo')) {
            $progress->set_source_table(
                'interactembed_progress',
                ['interactembedid' => backup::VAR_PARENTID],
                'id ASC'
            );
            $progress->annotate_ids('user', 'userid');
        }
        $activity->annotate_files('mod_interactembed', 'intro', null);
        $activity->annotate_files('mod_interactembed', 'cover', null);
        $package->annotate_files('mod_interactembed', 'content', 'id');
        return $this->prepare_activity_structure($activity);
    }
}
