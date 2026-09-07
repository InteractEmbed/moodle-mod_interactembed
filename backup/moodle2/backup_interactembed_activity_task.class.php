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
 * Backup task for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @category  backup
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/interactembed/backup/moodle2/backup_interactembed_stepslib.php');

/**
 * Defines the complete InteractEmbed activity backup.
 */
class backup_interactembed_activity_task extends backup_activity_task {
    /**
     * Defines activity-specific backup settings.
     */
    protected function define_my_settings(): void {
    }

    /**
     * Defines the structure step.
     */
    protected function define_my_steps(): void {
        $this->add_step(new backup_interactembed_activity_structure_step(
            'interactembed_structure',
            'interactembed.xml'
        ));
    }

    /**
     * Encodes links to this activity.
     *
     * @param string $content Content containing Moodle URLs.
     * @return string
     */
    public static function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, '/');
        $content = preg_replace(
            '/(' . $base . '\/mod\/interactembed\/index.php\?id=)([0-9]+)/',
            '$@INTERACTEMBEDINDEX*$2@$',
            $content
        );
        return preg_replace(
            '/(' . $base . '\/mod\/interactembed\/view.php\?id=)([0-9]+)/',
            '$@INTERACTEMBEDVIEWBYID*$2@$',
            $content
        );
    }
}
