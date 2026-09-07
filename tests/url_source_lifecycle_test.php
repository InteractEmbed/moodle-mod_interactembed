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

use mod_interactembed\local\embed\embed_builder;

/**
 * Tests the URL source through Moodle's activity lifecycle.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class url_source_lifecycle_test extends \advanced_testcase {
    /**
     * Verifies creation, safe conversion and rejected forged updates.
     */
    public function test_url_source_lifecycle(): void {
        global $CFG, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/modlib.php');
        $course = $this->getDataGenerator()->create_course();
        [, , , , $data] = prepare_new_moduleinfo_data($course, 'interactembed', 0);
        $data->name = 'Remote interaction';
        $data->intro = '';
        $data->introformat = FORMAT_HTML;
        $data->sourcetype = 'url';
        $data->sourceurl = '<iframe src="https://youtu.be/dQw4w9WgXcQ" onload="alert(1)"></iframe>';
        $data->entrypoint = 'index.html';
        $data->showdescription = 0;
        $data->visibleoncoursepage = 1;

        $created = add_moduleinfo($data, $course);
        $record = $DB->get_record('interactembed', ['id' => (int) $created->instance], '*', MUST_EXIST);
        $result = embed_builder::build($record->sourceurl);
        $this->assertSame('youtube', $result->get_provider());
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $result->get_url());
        $this->assertSame(0, (int) $record->packageitemid);

        $record->instance = (int) $created->instance;
        $record->coursemodule = (int) $created->coursemodule;
        $record->packagefiles = 0;
        $record->sourceurl = 'https://unapproved.example.org/demo';
        try {
            interactembed_update_instance($record);
            $this->fail('An unapproved generic domain was accepted.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('sourceurlinvalid', $exception->errorcode);
        }

        $stored = $DB->get_record('interactembed', ['id' => (int) $created->instance], '*', MUST_EXIST);
        $this->assertStringContainsString('youtu.be', $stored->sourceurl);
        course_delete_module((int) $created->coursemodule, false);
    }
}
