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
 * Archive validator tests.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed;

use mod_interactembed\local\package\archive_validator;

/**
 * Tests ZIP archive security validation in the activity component.
 */
final class archive_validator_test extends \advanced_testcase {
    public function test_safe_archive_is_accepted(): void {
        $this->resetAfterTest();
        archive_validator::validate($this->create_archive('safe.zip', ['index.html' => '<h1>Safe</h1>']));
        $this->addToAssertionCount(1);
    }

    /**
     * Ensures unsafe archive paths are rejected.
     *
     * @dataProvider unsafe_paths_provider
     * @param string $path Unsafe path under test.
     */
    public function test_unsafe_paths_are_rejected(string $path): void {
        $this->resetAfterTest();
        $archive = $this->create_archive('unsafe.zip', [$path => '<h1>Unsafe</h1>']);
        $this->expectException(\moodle_exception::class);
        archive_validator::validate($archive);
    }

    /**
     * Provides unsafe archive paths.
     *
     * @return array Test cases.
     */
    public static function unsafe_paths_provider(): array {
        return [
            'parent traversal' => ['../outside.html'],
            'dot segment' => ['assets/./index.html'],
            'absolute path' => ['/index.html'],
            'windows path' => ['C:/index.html'],
            'double separator' => ['assets//index.html'],
            'nested archive' => ['assets/content.zip'],
        ];
    }

    public function test_symbolic_link_is_rejected(): void {
        $this->resetAfterTest();
        $pathname = make_request_directory() . '/symlink.zip';
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($pathname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        $this->assertTrue($zip->addFromString('index.html', '<h1>Safe</h1>'));
        $this->assertTrue($zip->addFromString('linked-file', 'index.html'));
        $this->assertTrue($zip->setExternalAttributesName('linked-file', 3, 0120777 << 16));
        $this->assertTrue($zip->close());
        $archive = get_file_storage()->create_file_from_pathname($this->file_record('symlink.zip'), $pathname);
        $this->expectException(\moodle_exception::class);
        archive_validator::validate($archive);
    }

    public function test_unpacked_collection_enforces_file_count_and_duplicate_paths(): void {
        $this->resetAfterTest();
        set_config('maxarchivefiles', 1, 'mod_interactembed');
        $first = get_file_storage()->create_file_from_string($this->file_record('index.html'), '<h1>One</h1>');
        $secondrecord = $this->file_record('styles.css');
        $secondrecord['itemid'] = 1;
        $second = get_file_storage()->create_file_from_string($secondrecord, 'body {}');
        try {
            archive_validator::validate_files([$first, $second]);
            $this->fail('The configured file-count limit must be enforced.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageunsafearchive', $exception->errorcode);
        }

        set_config('maxarchivefiles', 5000, 'mod_interactembed');
        $duplicaterecord = $this->file_record('index.html');
        $duplicaterecord['itemid'] = 2;
        $duplicate = get_file_storage()->create_file_from_string($duplicaterecord, '<h1>Two</h1>');
        $this->expectException(\moodle_exception::class);
        archive_validator::validate_files([$first, $duplicate]);
    }

    public function test_unpacked_collection_enforces_size_and_depth(): void {
        $this->resetAfterTest();
        set_config('maxarchivebytes', 1048576, 'mod_interactembed');
        $large = get_file_storage()->create_file_from_string(
            $this->file_record('large.bin'),
            str_repeat('x', 1048577)
        );
        try {
            archive_validator::validate_files([$large]);
            $this->fail('The configured unpacked-size limit must be enforced.');
        } catch (\moodle_exception $exception) {
            $this->assertSame('packageunsafearchive', $exception->errorcode);
        }

        set_config('maxarchivebytes', 209715200, 'mod_interactembed');
        $deeprecord = $this->file_record('deep.txt');
        $deeprecord['itemid'] = 3;
        $deeprecord['filepath'] = '/' . str_repeat('a/', 50);
        $deep = get_file_storage()->create_file_from_string($deeprecord, 'deep');
        $this->expectException(\moodle_exception::class);
        archive_validator::validate_files([$deep]);
    }

    /**
     * Creates a stored ZIP archive for a test.
     *
     * @param string $filename Archive name.
     * @param array $contents Files indexed by archive path.
     * @return \stored_file Stored archive.
     */
    private function create_archive(string $filename, array $contents): \stored_file {
        $pathname = make_request_directory() . '/' . $filename;
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($pathname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE));
        foreach ($contents as $path => $content) {
            $this->assertTrue($zip->addFromString($path, $content));
        }
        $this->assertTrue($zip->close());
        return get_file_storage()->create_file_from_pathname($this->file_record($filename), $pathname);
    }

    /**
     * Builds a Files API record for a test archive.
     *
     * @param string $filename File name.
     * @return array File record.
     */
    private function file_record(string $filename): array {
        return [
            'contextid' => \context_system::instance()->id,
            'component' => 'mod_interactembed',
            'filearea' => 'test',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => $filename,
        ];
    }
}
