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
 * Progress nonce tests.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed;

use mod_interactembed\local\progress_nonce;

/**
 * Tests per-display progress nonce scope.
 */
final class progress_nonce_test extends \advanced_testcase {
    public function test_nonce_is_bound_to_its_course_module(): void {
        $this->resetAfterTest();
        $nonce = progress_nonce::issue(42);
        $this->assertTrue(progress_nonce::validate(42, $nonce));
        $this->assertFalse(progress_nonce::validate(43, $nonce));
        $this->assertFalse(progress_nonce::validate(42, str_repeat('x', 32)));
    }
}
