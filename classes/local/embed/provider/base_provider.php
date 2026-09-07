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

namespace mod_interactembed\local\embed\provider;

/**
 * Shared URL helpers for embed providers.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_provider {
    /**
     * Returns the normalised hostname.
     *
     * @param string $url URL
     * @return string
     */
    protected function host(string $url): string {
        return strtolower((string)parse_url($url, PHP_URL_HOST));
    }

    /**
     * Checks the host against an exact domain or its subdomains.
     *
     * @param string $host hostname
     * @param string $domain allowed domain
     * @return bool
     */
    protected function host_is(string $host, string $domain): bool {
        return $host === $domain || substr($host, -(strlen($domain) + 1)) === '.' . $domain;
    }
}
