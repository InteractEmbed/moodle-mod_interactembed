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

namespace mod_interactembed\local\package;

use mod_interactembed\local\sandbox_policy;

/**
 * Builds security and CORS headers for signed HTML5 package resources.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_response_policy {
    /**
     * Returns response headers for one validated package file.
     *
     * The wildcard origin is safe here because package URLs are short-lived bearer
     * URLs, this endpoint never accepts Moodle cookies, and credentialed CORS is not
     * enabled. It lets an opaque-origin sandbox fetch its own JSON and other assets.
     *
     * @param string $filename Validated package filename.
     * @param string $sandboxprofile Stored sandbox profile.
     * @return array<string, string> Header names and values.
     */
    public static function headers(string $filename, string $sandboxprofile): array {
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Cross-Origin-Resource-Policy' => 'cross-origin',
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($extension, ['html', 'htm'], true)) {
            $headers['Content-Security-Policy'] = sandbox_policy::package_document_csp($sandboxprofile);
        }

        return $headers;
    }
}
