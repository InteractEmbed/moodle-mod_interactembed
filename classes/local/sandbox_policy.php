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

namespace mod_interactembed\local;

/**
 * Defines the effective sandbox permissions used by package frames and responses.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class sandbox_policy {
    /** Sandbox profile definitions. */
    private const PROFILES = [
        'strict' => 'allow-scripts allow-forms allow-presentation',
        'standard' => 'allow-scripts allow-forms allow-popups allow-presentation allow-downloads',
        'trusted' => 'allow-scripts allow-forms allow-popups allow-presentation allow-downloads allow-same-origin',
    ];

    /**
     * Returns a known profile name, defaulting safely to Standard for legacy records.
     *
     * @param string $profile Stored profile name.
     * @return string Normalised profile name.
     */
    public static function normalise(string $profile): string {
        return array_key_exists($profile, self::PROFILES) ? $profile : 'standard';
    }

    /**
     * Returns the iframe/CSP sandbox permissions for a profile.
     *
     * @param string $profile Stored profile name.
     * @return string Space-separated sandbox permissions.
     */
    public static function permissions(string $profile): string {
        return self::PROFILES[self::normalise($profile)];
    }

    /**
     * Builds the CSP applied to HTML package documents.
     *
     * @param string $profile Stored profile name.
     * @return string Content-Security-Policy value.
     */
    public static function package_document_csp(string $profile): string {
        return 'sandbox ' . self::permissions($profile) . "; frame-ancestors 'self'";
    }
}
