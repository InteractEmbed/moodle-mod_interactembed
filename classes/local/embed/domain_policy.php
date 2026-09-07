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

namespace mod_interactembed\local\embed;

/**
 * Applies the administrator's policy to unrecognised embed domains.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class domain_policy {
    /**
     * Determines whether a generic URL is allowed.
     *
     * An allow-list entry matches the exact host and its subdomains. IP addresses
     * only match exactly. Known providers are handled before this policy is used.
     *
     * @param string $url validated HTTP or HTTPS URL
     * @param string $alloweddomains newline, comma, or whitespace separated domains
     * @param bool $allowall whether any otherwise valid generic URL is allowed
     * @return bool
     */
    public static function allows(string $url, string $alloweddomains, bool $allowall): bool {
        if ($allowall) {
            return true;
        }

        $host = strtolower((string)(parse_url($url, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return false;
        }

        foreach (self::normalise_entries($alloweddomains) as $domain) {
            if ($host === $domain) {
                return true;
            }
            $suffix = '.' . $domain;
            if (
                filter_var($domain, FILTER_VALIDATE_IP) === false &&
                    substr($host, -strlen($suffix)) === $suffix
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Normalises administrator-entered host names.
     *
     * @param string $value raw setting
     * @return array unique valid hosts
     */
    private static function normalise_entries(string $value): array {
        $domains = [];
        $entries = preg_split('/[\s,;]+/', strtolower($value), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($entries ?: [] as $entry) {
            if (strpos($entry, '://') !== false) {
                $entry = (string)(parse_url($entry, PHP_URL_HOST) ?? '');
            }
            $entry = ltrim(rtrim($entry, '.'), '*.');
            if ($entry === '') {
                continue;
            }
            if (
                filter_var($entry, FILTER_VALIDATE_IP) !== false ||
                filter_var($entry, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false
            ) {
                $domains[$entry] = $entry;
            }
        }
        return array_values($domains);
    }
}
