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

namespace mod_interactembed;

use mod_interactembed\local\configuration_guard;

/**
 * Tests server-side protection of security-sensitive configuration.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \mod_interactembed\local\configuration_guard
 */
final class configuration_guard_test extends \basic_testcase {
    /**
     * Tests that only an authorised administrator can save the Trusted profile.
     */
    public function test_trusted_profile_requires_permission(): void {
        $this->assertSame('standard', configuration_guard::sandbox_profile('trusted', false));
        $this->assertSame('trusted', configuration_guard::sandbox_profile('trusted', true));
    }

    /**
     * Tests ordinary profiles and forged values.
     */
    public function test_profiles_are_normalised(): void {
        $this->assertSame('strict', configuration_guard::sandbox_profile('strict', false));
        $this->assertSame('standard', configuration_guard::sandbox_profile('standard', false));
        $this->assertSame('standard', configuration_guard::sandbox_profile('forged', true));
    }

    /**
     * Tests supported and forged display modes.
     */
    public function test_display_modes_are_normalised(): void {
        $this->assertSame('inline', configuration_guard::display_mode('inline'));
        $this->assertSame('card', configuration_guard::display_mode('card'));
        $this->assertSame('inline', configuration_guard::display_mode('forged'));
    }

    /**
     * Tests that rapid successive saves cannot reuse a revision.
     */
    public function test_revision_is_strictly_increasing(): void {
        $this->assertSame(101, configuration_guard::next_revision(100, 99));
        $this->assertSame(101, configuration_guard::next_revision(100, 100));
        $this->assertSame(150, configuration_guard::next_revision(100, 150));
    }
}
