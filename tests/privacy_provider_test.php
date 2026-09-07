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
 * Privacy provider tests.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed;

use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\types\database_table;
use core_privacy\local\metadata\types\external_location;
use mod_interactembed\privacy\provider;

/**
 * Tests privacy metadata for stored progress and external iframe connections.
 *
 * @covers \mod_interactembed\privacy\provider
 */
final class privacy_provider_test extends \basic_testcase {
    public function test_all_personal_data_flows_are_declared(): void {
        $metadata = provider::get_metadata(new collection('mod_interactembed'))->get_collection();
        $this->assertCount(2, $metadata);
        $this->assertInstanceOf(external_location::class, $metadata[0]);
        $this->assertSame('externalcontent', $metadata[0]->get_name());
        $this->assertArrayHasKey('ipaddress', $metadata[0]->get_privacy_fields());
        $this->assertArrayHasKey('useragent', $metadata[0]->get_privacy_fields());
        $this->assertInstanceOf(database_table::class, $metadata[1]);
        $this->assertSame('interactembed_progress', $metadata[1]->get_name());
        $this->assertArrayHasKey('userid', $metadata[1]->get_privacy_fields());
        $this->assertArrayHasKey('score', $metadata[1]->get_privacy_fields());
    }
}
