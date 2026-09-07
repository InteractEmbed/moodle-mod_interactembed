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

/**
 * Creator Consumer API package-hash tests.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed;

use mod_interactembed\local\package\package_hash;

/**
 * Tests the local implementation of the Creator canonical hash.
 *
 * @covers \mod_interactembed\local\package\package_hash
 */
final class package_hash_test extends \advanced_testcase {
    public function test_hash_is_path_sorted_and_detects_tampering(): void {
        $this->resetAfterTest();
        $files = [
            $this->create_file('/assets/', 'player.js', 'console.log("ready");', 401),
            $this->create_file('/', 'index.html', '<h1>Ready</h1>', 401),
        ];
        $expected = hash_init('sha256');
        $sorted = $files;
        foreach ($sorted as $file) {
            $path = ltrim($file->get_filepath(), '/') . $file->get_filename();
            hash_update($expected, $path . "\0" . $file->get_contenthash() . "\0" . $file->get_filesize() . "\0");
        }
        $hash = hash_final($expected);

        $this->assertSame($hash, package_hash::calculate($files));
        $this->assertTrue(package_hash::verify(array_reverse($files), $hash));
        $this->assertFalse(package_hash::verify($files, str_repeat('0', 64)));
        $this->assertFalse(package_hash::verify($files, 'not-a-sha256'));
    }

    /**
     * Creates a stored file used by the hash tests.
     *
     * @param string $filepath File path.
     * @param string $filename File name.
     * @param string $content File content.
     * @param int $itemid Test item id.
     * @return \stored_file Stored file.
     */
    private function create_file(string $filepath, string $filename, string $content, int $itemid): \stored_file {
        return get_file_storage()->create_file_from_string([
            'contextid' => \context_system::instance()->id,
            'component' => 'mod_interactembed',
            'filearea' => 'test',
            'itemid' => $itemid,
            'filepath' => $filepath,
            'filename' => $filename,
        ], $content);
    }
}
