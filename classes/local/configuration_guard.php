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

namespace mod_interactembed\local;

/**
 * Normalises security-sensitive instance configuration.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class configuration_guard {
    /**
     * Returns an allowed sandbox profile for the current editor.
     *
     * @param string $requested submitted profile
     * @param bool $canusetrusted whether the editor may grant same-origin access
     * @return string normalised profile
     */
    public static function sandbox_profile(string $requested, bool $canusetrusted): string {
        if ($requested === 'trusted') {
            return $canusetrusted ? 'trusted' : 'standard';
        }
        return in_array($requested, ['strict', 'standard'], true) ? $requested : 'standard';
    }

    /**
     * Returns a supported display mode.
     *
     * @param string $requested submitted display mode
     * @return string normalised display mode
     */
    public static function display_mode(string $requested): string {
        return in_array($requested, ['inline', 'card'], true) ? $requested : 'inline';
    }

    /**
     * Returns a strictly increasing revision for a newly saved package state.
     *
     * @param int $previous previous saved revision
     * @param int|null $now testable current timestamp
     * @return int next revision
     */
    public static function next_revision(int $previous, ?int $now = null): int {
        return max($now ?? time(), $previous + 1);
    }
}
