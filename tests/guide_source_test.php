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
 * Tests the bundled interactive guide source.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class guide_source_test extends \advanced_testcase {
    /**
     * Ensures the static guide remains complete, local and trilingual.
     */
    public function test_guide_is_complete_and_trilingual(): void {
        $guide = dirname(__DIR__) . '/guide/';
        foreach (['index.html', 'app.js', 'course-data.js', 'styles.css', 'icon.svg'] as $filename) {
            $this->assertFileExists($guide . $filename);
        }

        $index = file_get_contents($guide . 'index.html');
        $app = file_get_contents($guide . 'app.js');
        $course = file_get_contents($guide . 'course-data.js');
        $styles = file_get_contents($guide . 'styles.css');
        $this->assertStringContainsString('course-data.js', $index);
        $this->assertStringContainsString("new URLSearchParams(window.location.search).get('lang')", $app);
        foreach (['fr:', 'en:', 'es:'] as $language) {
            $this->assertStringContainsString($language, $course);
        }
        $this->assertStringContainsString('L’activité et son bloc compagnon', $course);
        $this->assertStringContainsString('The activity and its companion block', $course);
        $this->assertStringContainsString('La actividad y su bloque complementario', $course);
        $this->assertStringNotContainsString('A block, not an activity', $course);
        $this->assertStringNotContainsString('Un bloc, pas une activité', $course);
        $this->assertStringNotContainsString('Un bloque, no una actividad', $course);
        $this->assertStringContainsString('@media (max-width: 640px)', $styles);
        $this->assertStringNotContainsString('https://', $index);
    }

    /**
     * Ensures unsupported persisted values cannot select an unintended source.
     */
    public function test_source_type_is_restricted(): void {
        $this->assertSame('guide', interactembed_normalise_sourcetype('guide'));
        $this->assertSame('url', interactembed_normalise_sourcetype('url'));
        $this->assertSame('package', interactembed_normalise_sourcetype('package'));
        $this->assertSame('package', interactembed_normalise_sourcetype('unsupported'));
    }

    /**
     * Ensures a guide activity does not create package storage.
     */
    public function test_guide_activity_lifecycle_uses_no_package_files(): void {
        global $CFG, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/modlib.php');
        $course = $this->getDataGenerator()->create_course();
        [, , , , $data] = prepare_new_moduleinfo_data($course, 'interactembed', 0);
        $data->name = 'Included guide';
        $data->intro = '';
        $data->introformat = FORMAT_HTML;
        $data->sourcetype = 'guide';
        $data->entrypoint = 'index.html';
        $data->showdescription = 0;
        $data->visibleoncoursepage = 1;

        $created = add_moduleinfo($data, $course);
        $record = $DB->get_record('interactembed', ['id' => (int) $created->instance], '*', MUST_EXIST);
        $this->assertSame('guide', $record->sourcetype);
        $this->assertSame(0, (int) $record->packageitemid);
        $this->assertSame(0, (int) $record->revision);
        $context = \context_module::instance((int) $created->coursemodule);
        $this->assertEmpty(get_file_storage()->get_area_files(
            $context->id,
            'mod_interactembed',
            'content',
            false,
            'id',
            false
        ));

        course_delete_module((int) $created->coursemodule, false);
        $this->assertFalse($DB->record_exists('interactembed', ['id' => (int) $created->instance]));
    }
}
