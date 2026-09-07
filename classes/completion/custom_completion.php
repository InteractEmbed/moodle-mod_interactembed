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
 * Custom completion implementation.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed\completion;

use core_completion\activity_custom_completion;

/**
 * Evaluates completion reported explicitly by the embedded content.
 */
class custom_completion extends activity_custom_completion {
    /**
     * Returns the current state of a custom completion rule.
     *
     * @param string $rule Rule identifier.
     * @return int Moodle completion state.
     */
    public function get_state(string $rule): int {
        global $DB;
        $this->validate_rule($rule);
        $complete = $DB->record_exists('interactembed_progress', [
            'interactembedid' => $this->cm->instance,
            'userid' => $this->userid,
            'status' => 'completed',
        ]);
        return $complete ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Returns the custom rules implemented by this activity.
     *
     * @return string[] Defined custom rules.
     */
    public static function get_defined_custom_rules(): array {
        return ['completioncontent'];
    }

    /**
     * Returns human-readable custom rule descriptions.
     *
     * @return array<string, string> Human-readable rule descriptions.
     */
    public function get_custom_rule_descriptions(): array {
        return ['completioncontent' => get_string('completiondetail:content', 'mod_interactembed')];
    }

    /**
     * Returns completion rules in display order.
     *
     * @return string[] Completion rules in display order.
     */
    public function get_sort_order(): array {
        return ['completionview', 'completioncontent', 'completionusegrade', 'completionpassgrade'];
    }
}
