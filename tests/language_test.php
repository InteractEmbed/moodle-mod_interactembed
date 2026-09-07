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
 * Tests language pack parity.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class language_test extends \advanced_testcase {
    /**
     * Ensures that bundled translations define the same keys.
     */
    public function test_bundled_language_keys_match(): void {
        $english = $this->load_language_keys('en');
        $this->assertSame($english, $this->load_language_keys('fr'));
        $this->assertSame($english, $this->load_language_keys('es'));
    }

    /**
     * Loads and sorts language keys from one bundled language file.
     *
     * @param string $language Language code.
     * @return array
     */
    private function load_language_keys(string $language): array {
        $string = [];
        require(__DIR__ . '/../lang/' . $language . '/interactembed.php');
        $keys = array_keys($string);
        sort($keys);
        return $keys;
    }
}
