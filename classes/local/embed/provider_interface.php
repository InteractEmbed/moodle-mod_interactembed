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

namespace mod_interactembed\local\embed;

/**
 * Contract for smart URL conversion providers.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
interface provider_interface {
    /**
     * Whether this provider owns the URL.
     *
     * @param string $url validated URL
     * @return bool
     */
    public function supports(string $url): bool;

    /**
     * Converts a share URL to a canonical iframe URL.
     *
     * @param string $url validated URL
     * @return embed_result
     */
    public function convert(string $url): embed_result;
}
