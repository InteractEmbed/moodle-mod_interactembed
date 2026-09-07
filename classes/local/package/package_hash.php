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
 * Canonical package hashing compatible with the Creator Consumer API v1.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed\local\package;

/**
 * Calculates a stable hash for an unpacked stored-file collection.
 */
final class package_hash {
    /**
     * Calculates the Consumer API v1 SHA-256 package hash.
     *
     * @param \stored_file[] $files Files to hash.
     * @return string Lowercase SHA-256 hash.
     */
    public static function calculate(array $files): string {
        $records = [];
        foreach ($files as $file) {
            if (!$file instanceof \stored_file || $file->is_directory()) {
                continue;
            }
            $directory = trim($file->get_filepath(), '/');
            $path = ($directory === '' ? '' : $directory . '/') . $file->get_filename();
            if (isset($records[$path])) {
                throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
            }
            $records[$path] = $file;
        }
        ksort($records, SORT_STRING);
        $hash = hash_init('sha256');
        foreach ($records as $path => $file) {
            hash_update(
                $hash,
                $path . "\0" . $file->get_contenthash() . "\0" . $file->get_filesize() . "\0"
            );
        }
        return hash_final($hash);
    }

    /**
     * Verifies a canonical package hash using constant-time comparison.
     *
     * @param \stored_file[] $files Files to verify.
     * @param string $expectedhash Expected lowercase SHA-256 hash.
     * @return bool Whether the collection matches the expected hash.
     */
    public static function verify(array $files, string $expectedhash): bool {
        $expectedhash = strtolower(trim($expectedhash));
        return (bool) preg_match('/^[a-f0-9]{64}$/', $expectedhash)
            && hash_equals($expectedhash, self::calculate($files));
    }
}
