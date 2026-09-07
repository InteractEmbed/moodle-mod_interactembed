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

use mod_interactembed\local\package\package_access_key;

/**
 * Tests package access-key scope and expiry.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_access_key_test extends \advanced_testcase {
    /**
     * Verifies that context, revision, and item id are all signed.
     */
    public function test_key_scope_and_expiry(): void {
        $time = 1788271200;
        $key = package_access_key::create(51, 12, 34, $time);

        $this->assertTrue(package_access_key::validate($key, 51, 12, 34, $time));
        $this->assertFalse(package_access_key::validate($key, 52, 12, 34, $time));
        $this->assertFalse(package_access_key::validate($key, 51, 13, 34, $time));
        $this->assertFalse(package_access_key::validate($key, 51, 12, 35, $time));
        $this->assertTrue(package_access_key::validate($key, 51, 12, 34, $time + 3600));
        $this->assertFalse(package_access_key::validate($key, 51, 12, 34, $time + 7200));
    }
}
