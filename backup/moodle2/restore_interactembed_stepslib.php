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
 * Restore structure for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @category  backup
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restores the activity record and remaps its immutable package revision.
 */
class restore_interactembed_activity_structure_step extends restore_activity_structure_step {
    /**
     * Defines the paths restored for this activity.
     *
     * @return array Restore paths.
     */
    protected function define_structure(): array {
        $paths = [
            new restore_path_element('interactembed', '/activity/interactembed'),
            new restore_path_element('interactembed_package', '/activity/interactembed/package'),
        ];
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element(
                'interactembed_progress',
                '/activity/interactembed/progresses/progress'
            );
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Restores the main activity record.
     *
     * @param array $data Backup record data.
     */
    protected function process_interactembed($data): void {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();
        if (($data->sourcetype ?? 'package') !== 'package') {
            $data->packageitemid = 0;
            $data->revision = 0;
        }
        $data->timemodified = time();
        $newitemid = $DB->insert_record('interactembed', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * Creates a fresh immutable item id and maps the backed-up package files to it.
     *
     * @param array $data Package child data.
     */
    protected function process_interactembed_package($data): void {
        global $DB;

        $data = (object) $data;
        $instanceid = $this->get_new_parentid('interactembed');
        $record = $DB->get_record('interactembed', ['id' => $instanceid], '*', MUST_EXIST);
        $revision = max(time(), (int) $record->revision + 1);
        $DB->set_field('interactembed', 'packageitemid', $revision, ['id' => $instanceid]);
        $DB->set_field('interactembed', 'revision', $revision, ['id' => $instanceid]);
        $this->set_mapping('interactembed_package', (int) $data->id, $revision, true);
    }

    /**
     * Restores learner progress when user data was included.
     *
     * @param array $data Progress record data.
     */
    protected function process_interactembed_progress($data): void {
        global $DB;
        $data = (object) $data;
        $data->interactembedid = $this->get_new_parentid('interactembed');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if (!$data->userid) {
            return;
        }
        $DB->insert_record('interactembed_progress', $data);
    }

    /**
     * Restores related Files API areas after the module context exists.
     */
    protected function after_execute(): void {
        $this->add_related_files('mod_interactembed', 'intro', null);
        $this->add_related_files('mod_interactembed', 'cover', null);
        $this->add_related_files('mod_interactembed', 'content', 'interactembed_package');
    }
}
