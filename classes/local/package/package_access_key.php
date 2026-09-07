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

namespace mod_interactembed\local\package;

/**
 * Creates short-lived access keys for one active package revision.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_access_key {
    /** Token validity window in seconds. */
    private const WINDOW = 3600;

    /** Token prefix. */
    private const PREFIX = 'ieak';

    /**
     * Creates a signed virtual path segment.
     *
     * @param int $contextid Activity context id.
     * @param int $revision Active revision.
     * @param int $itemid Active package item id.
     * @param int|null $time Optional test time.
     * @return string
     */
    public static function create(int $contextid, int $revision, int $itemid, ?int $time = null): string {
        $slot = (int) floor(($time ?? time()) / self::WINDOW);
        return self::PREFIX . $slot . '-' . self::signature($contextid, $revision, $itemid, $slot);
    }

    /**
     * Validates a key for the current or previous time window.
     *
     * @param string $key Submitted key.
     * @param int $contextid Activity context id.
     * @param int $revision Active revision.
     * @param int $itemid Active package item id.
     * @param int|null $time Optional test time.
     * @return bool
     */
    public static function validate(
        string $key,
        int $contextid,
        int $revision,
        int $itemid,
        ?int $time = null
    ): bool {
        if (!preg_match('/^' . self::PREFIX . '(\d+)-[a-f0-9]{64}$/', $key, $matches)) {
            return false;
        }
        $slot = (int) $matches[1];
        $currentslot = (int) floor(($time ?? time()) / self::WINDOW);
        if ($slot !== $currentslot && $slot !== $currentslot - 1) {
            return false;
        }
        $expected = self::PREFIX . $slot . '-' . self::signature($contextid, $revision, $itemid, $slot);
        return hash_equals($expected, $key);
    }

    /**
     * Calculates the HMAC signature.
     *
     * @param int $contextid Activity context id.
     * @param int $revision Active revision.
     * @param int $itemid Active package item id.
     * @param int $slot Validity window.
     * @return string
     */
    private static function signature(int $contextid, int $revision, int $itemid, int $slot): string {
        $secret = (string) get_site_identifier();
        if ($secret === '') {
            throw new \coding_exception('Moodle site identifier is unavailable.');
        }
        return hash_hmac('sha256', $contextid . ':' . $revision . ':' . $itemid . ':' . $slot, $secret);
    }
}
