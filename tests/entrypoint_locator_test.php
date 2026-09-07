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

use mod_interactembed\local\package\entrypoint_locator;

/**
 * Tests entry-point validation.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \mod_interactembed\local\package\entrypoint_locator
 */
final class entrypoint_locator_test extends \advanced_testcase {
    /**
     * Verifies valid relative HTML paths.
     */
    public function test_normalise_valid_path(): void {
        $this->assertSame('index.html', entrypoint_locator::normalise_path('index.html'));
        $this->assertSame('course/start.htm', entrypoint_locator::normalise_path('course\\start.htm'));
    }

    /**
     * Verifies unsafe and non-HTML paths.
     *
     * @dataProvider invalid_path_provider
     * @param string $path Invalid path.
     */
    public function test_normalise_rejects_invalid_path(string $path): void {
        $this->expectException(\moodle_exception::class);
        entrypoint_locator::normalise_path($path);
    }

    /**
     * Supplies invalid entry-point paths.
     *
     * @return array
     */
    public static function invalid_path_provider(): array {
        return [
            'empty' => [''],
            'parent traversal' => ['../index.html'],
            'current segment' => ['./index.html'],
            'nested current segment' => ['course/./index.html'],
            'absolute path' => ['/index.html'],
            'windows absolute path' => ['C:/index.html'],
            'double separator' => ['course//index.html'],
            'non html' => ['manifest.json'],
            'null byte' => ["index.html\0.js"],
        ];
    }
}
