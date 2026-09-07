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

use mod_interactembed\local\embed\domain_policy;

/**
 * Tests the administrator-defined generic domain policy.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class domain_policy_test extends \basic_testcase {
    /**
     * Tests exact domains, subdomains, and deceptive suffixes.
     */
    public function test_allow_list_matches_domain_boundaries(): void {
        $list = "example.org\nhttps://media.example.net/path";
        $this->assertTrue(domain_policy::allows('https://example.org/demo', $list, false));
        $this->assertTrue(domain_policy::allows('https://slides.example.org/demo', $list, false));
        $this->assertTrue(domain_policy::allows('https://media.example.net/demo', $list, false));
        $this->assertFalse(domain_policy::allows('https://example.org.evil.test/demo', $list, false));
        $this->assertFalse(domain_policy::allows('https://notexample.org/demo', $list, false));
    }

    /**
     * Tests permissive mode and an empty policy.
     */
    public function test_allow_all_is_explicit(): void {
        $this->assertFalse(domain_policy::allows('https://example.org/demo', '', false));
        $this->assertTrue(domain_policy::allows('https://example.org/demo', '', true));
    }

    /**
     * Tests that IP allow-list entries never authorise neighbouring addresses.
     */
    public function test_ip_addresses_match_exactly(): void {
        $this->assertTrue(domain_policy::allows('https://192.0.2.10/demo', '192.0.2.10', false));
        $this->assertFalse(domain_policy::allows('https://192.0.2.11/demo', '192.0.2.10', false));
    }
}
