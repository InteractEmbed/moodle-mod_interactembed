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
 * Package importer tests.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed;

use mod_interactembed\local\package\package_importer;
use mod_interactembed\local\package\package_hash;

/**
 * Tests draft-package validation.
 *
 * @covers \mod_interactembed\local\package\package_importer
 * @covers \mod_interactembed\local\package\package_hash
 */
final class package_importer_test extends \advanced_testcase {
    public function test_non_html_entrypoint_is_rejected(): void {
        global $USER;
        $this->resetAfterTest();
        $this->setAdminUser();
        $itemid = file_get_unused_draft_itemid();
        get_file_storage()->create_file_from_string([
            'contextid' => \context_user::instance($USER->id)->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => 'start.js',
        ], 'document.body.textContent = "Not HTML";');
        try {
            package_importer::validate_draft($itemid, 'start.js');
            $this->fail('A JavaScript entry point must not be accepted.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageentryinvalid', $exception->errorcode);
        }
    }

    public function test_creator_snapshot_is_copied_and_hash_verified_atomically(): void {
        global $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/modlib.php');
        $course = $this->getDataGenerator()->create_course();
        [, , , , $data] = prepare_new_moduleinfo_data($course, 'interactembed', 0);
        $data->name = 'Creator import target';
        $data->intro = '';
        $data->introformat = FORMAT_HTML;
        $data->sourcetype = 'guide';
        $data->entrypoint = 'index.html';
        $data->showdescription = 0;
        $data->visibleoncoursepage = 1;
        $created = add_moduleinfo($data, $course);
        $context = \context_module::instance((int) $created->coursemodule);

        $sourcefiles = [
            $this->create_source_file('/', 'index.html', '<h1>Creator</h1>', 801),
            $this->create_source_file('/assets/', 'styles.css', 'body {}', 801),
        ];
        $hash = package_hash::calculate($sourcefiles);
        $entry = package_importer::import_stored_files($context, $sourcefiles, 'index.html', 901, $hash);
        $this->assertSame('index.html', $entry->get_filename());
        $this->assertNotFalse(get_file_storage()->get_file(
            $context->id,
            'mod_interactembed',
            'content',
            901,
            '/assets/',
            'styles.css'
        ));

        try {
            package_importer::import_stored_files(
                $context,
                $sourcefiles,
                'index.html',
                902,
                str_repeat('0', 64)
            );
            $this->fail('A falsified Creator hash must be rejected.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageinvalidarchive', $exception->errorcode);
        }
        $this->assertEmpty(get_file_storage()->get_area_files(
            $context->id,
            'mod_interactembed',
            'content',
            902,
            'id ASC',
            false
        ));
    }

    /**
     * Creates a source publication file.
     *
     * @param string $filepath File path.
     * @param string $filename File name.
     * @param string $content File content.
     * @param int $itemid Source item id.
     * @return \stored_file Stored file.
     */
    private function create_source_file(
        string $filepath,
        string $filename,
        string $content,
        int $itemid
    ): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => \context_system::instance()->id,
            'component' => 'mod_interactembed',
            'filearea' => 'creatorfixture',
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename,
        ], $content);
    }
}
