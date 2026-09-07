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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_interactembed;

/**
 * Tests the minimal activity lifecycle callbacks.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class lifecycle_test extends \advanced_testcase {
    /**
     * Verifies creation, update, and deletion through module callbacks.
     */
    public function test_instance_lifecycle(): void {
        global $CFG, $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/modlib.php');
        $course = $this->getDataGenerator()->create_course();
        $draftitemid = file_get_unused_draft_itemid();
        $usercontext = \context_user::instance($USER->id);
        $fs = get_file_storage();
        $fs->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => 'index.html',
        ], '<!doctype html><title>Test</title>');
        $fs->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $draftitemid,
            'filepath' => '/',
            'filename' => 'README.txt',
        ], 'Auxiliary file');

        [, , , , $data] = prepare_new_moduleinfo_data($course, 'interactembed', 0);
        $data->name = 'Minimal InteractEmbed';
        $data->intro = '<p>Foundation test</p>';
        $data->introformat = FORMAT_HTML;
        $data->packagefiles = $draftitemid;
        $data->entrypoint = 'index.html';
        $data->showdescription = 0;
        $data->visibleoncoursepage = 1;
        $created = add_moduleinfo($data, $course);
        $instanceid = (int) $created->instance;
        $cmid = (int) $created->coursemodule;

        $record = $DB->get_record('interactembed', ['id' => $instanceid], '*', MUST_EXIST);
        $this->assertSame('Minimal InteractEmbed', $record->name);
        $this->assertGreaterThan(0, (int) $record->timecreated);
        $this->assertGreaterThan(0, (int) $record->packageitemid);
        $context = \context_module::instance($cmid);
        $this->assertTrue($fs->file_exists(
            $context->id,
            'mod_interactembed',
            'content',
            (int) $record->packageitemid,
            '/',
            'README.txt'
        ));

        $updatedraftitemid = 0;
        file_prepare_draft_area(
            $updatedraftitemid,
            $context->id,
            'mod_interactembed',
            'content',
            (int) $record->packageitemid,
            ['subdirs' => true, 'maxfiles' => -1]
        );
        $record->instance = $instanceid;
        $record->coursemodule = $cmid;
        $record->packagefiles = $updatedraftitemid;
        $record->name = 'Updated InteractEmbed';
        $this->assertTrue(\interactembed_update_instance($record));
        $this->assertSame(
            'Updated InteractEmbed',
            $DB->get_field('interactembed', 'name', ['id' => $instanceid])
        );

        course_delete_module($cmid, false);
        $this->assertFalse($DB->record_exists('interactembed', ['id' => $instanceid]));
        $this->assertFalse(\interactembed_delete_instance($instanceid));
    }

    /**
     * Verifies that only implemented Moodle features are declared.
     */
    public function test_declared_features(): void {
        $this->assertTrue(\interactembed_supports(FEATURE_MOD_INTRO));
        $this->assertTrue(\interactembed_supports(FEATURE_SHOW_DESCRIPTION));
        $this->assertTrue(\interactembed_supports(FEATURE_COMPLETION_TRACKS_VIEWS));
        $this->assertTrue(\interactembed_supports(FEATURE_GRADE_HAS_GRADE));
        $this->assertTrue(\interactembed_supports(FEATURE_COMPLETION_HAS_RULES));
        $this->assertTrue(\interactembed_supports(FEATURE_GROUPS));
        $this->assertTrue(\interactembed_supports(FEATURE_GROUPINGS));
        $this->assertTrue(\interactembed_supports(FEATURE_BACKUP_MOODLE2));
    }
}
