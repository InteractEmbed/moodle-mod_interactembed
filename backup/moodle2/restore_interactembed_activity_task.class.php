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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Restore task for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @category  backup
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/interactembed/backup/moodle2/restore_interactembed_stepslib.php');

/**
 * Defines a complete InteractEmbed activity restore.
 */
class restore_interactembed_activity_task extends restore_activity_task {
    /**
     * Defines activity-specific restore settings.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines the structure step.
     */
    protected function define_my_steps(): void {
        $this->add_step(new restore_interactembed_activity_structure_step(
            'interactembed_structure',
            'interactembed.xml'
        ));
    }

    /**
     * Returns content fields requiring link decoding.
     *
     * @return array Content fields requiring link decoding.
     */
    public static function define_decode_contents(): array {
        return [new restore_decode_content('interactembed', ['intro'], 'interactembed')];
    }

    /**
     * Returns activity link decoding rules.
     *
     * @return array Link decoding rules.
     */
    public static function define_decode_rules(): array {
        return [
            new restore_decode_rule(
                'INTERACTEMBEDVIEWBYID',
                '/mod/interactembed/view.php?id=$1',
                'course_module'
            ),
            new restore_decode_rule('INTERACTEMBEDINDEX', '/mod/interactembed/index.php?id=$1', 'course'),
        ];
    }
}
