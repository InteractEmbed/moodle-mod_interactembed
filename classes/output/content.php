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

namespace mod_interactembed\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Template data for one InteractEmbed player.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class content implements renderable, templatable {
    /** @var array template data */
    private $data;

    /**
     * Constructor.
     *
     * @param array $data validated template data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * Returns the unique player DOM identifier.
     *
     * @return string unique DOM id
     */
    public function get_dom_id(): string {
        return $this->data['domid'];
    }

    /**
     * Returns the origin expected for messages from the content frame.
     *
     * @return string Origin expected for messages from the content frame.
     */
    public function get_expected_origin(): string {
        return $this->data['expectedorigin'];
    }

    /**
     * Exports the validated player values for the Mustache template.
     *
     * @param renderer_base $output renderer instance
     * @return array template values
     */
    public function export_for_template(renderer_base $output): array {
        return $this->data;
    }
}
