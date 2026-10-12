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
 * Checks resizer loading on pages without an InteractEmbed player.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \mod_interactembed\hook_callbacks
 */
final class embed_resizer_loading_test extends \advanced_testcase {
    /**
     * Verifies the supported output callback requests the compiled AMD module.
     */
    public function test_destination_page_loads_resizer(): void {
        global $CFG, $PAGE;
        $this->resetAfterTest();
        require_once($CFG->dirroot . '/mod/interactembed/lib.php');
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/my/index.php');
        if (class_exists(\core\hook\output\before_footer_html_generation::class)) {
            $renderer = $PAGE->get_renderer('core');
            $hook = new \core\hook\output\before_footer_html_generation($renderer);
            hook_callbacks::before_footer($hook);
            $this->assertSame('', $hook->get_output());
        } else {
            $this->assertSame('', mod_interactembed_before_footer());
        }
        $html = $PAGE->requires->get_end_code();
        $this->assertStringContainsString('mod_interactembed/embed_resizer', $html);
    }
}
