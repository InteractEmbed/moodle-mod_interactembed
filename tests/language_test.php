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
 * Tests the Marketplace language-pack layout.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversNothing
 */
final class language_test extends \advanced_testcase {
    /**
     * Ensures that the distributable plugin contains only English source strings.
     */
    public function test_distribution_contains_only_english_source_strings(): void {
        $languages = array_map('basename', glob(__DIR__ . '/../lang/*', GLOB_ONLYDIR));
        sort($languages);
        $this->assertSame(['en'], $languages);

        $string = [];
        require(__DIR__ . '/../lang/en/interactembed.php');
        $this->assertNotEmpty($string);
        $this->assertArrayHasKey('pluginname', $string);
    }
}
