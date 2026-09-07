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
 * Progress-updated event.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed\event;

/**
 * Event emitted when interactive content updates a learner's progress.
 */
class progress_updated extends \core\event\base {
    /**
     * Defines event metadata.
     */
    protected function init(): void {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'interactembed_progress';
    }

    /**
     * Returns the localised event name.
     *
     * @return string Localised event name.
     */
    public static function get_name(): string {
        return get_string('eventprogressupdated', 'mod_interactembed');
    }

    /**
     * Returns the human-readable event description.
     *
     * @return string Human-readable event description.
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' updated progress id '{$this->objectid}' " .
            "in InteractEmbed activity '{$this->other['interactembedid']}'.";
    }

    /**
     * Returns the related activity URL.
     *
     * @return \moodle_url Related activity URL.
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/interactembed/view.php', ['id' => $this->contextinstanceid]);
    }
}
