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
 * Reads and enforces administrator-defined site policies.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class site_policy {
    /**
     * Returns whether the configured source type is permitted site-wide.
     *
     * @param string $sourcetype url, package or bundled guide
     * @return bool
     */
    public static function allows_source(string $sourcetype): bool {
        if ($sourcetype === 'guide') {
            return true;
        }
        $policy = self::string_setting('sourcepolicy', 'both');
        if (!in_array($policy, ['both', 'urlonly', 'packageonly'], true)) {
            $policy = 'both';
        }
        return $policy === 'both' ||
            ($policy === 'urlonly' && $sourcetype === 'url') ||
            ($policy === 'packageonly' && $sourcetype === 'package');
    }

    /**
     * Returns whether editors may expose same-site embed code.
     *
     * @return bool
     */
    public static function allows_embed_code(): bool {
        return self::bool_setting('allowembedcode', true);
    }

    /**
     * Returns whether remote sources must wait for visitor interaction.
     *
     * @return bool
     */
    public static function forces_remote_consent(): bool {
        return self::bool_setting('forceremoteconsent', false);
    }

    /**
     * Reads a Boolean plugin setting with a safe fallback.
     *
     * @param string $name setting name
     * @param bool $fallback value used before a setting exists
     * @return bool
     */
    public static function bool_setting(string $name, bool $fallback): bool {
        $value = get_config('mod_interactembed', $name);
        return $value === false ? $fallback : (bool)$value;
    }

    /**
     * Reads a string plugin setting with a fallback.
     *
     * @param string $name setting name
     * @param string $fallback value used before a setting exists
     * @return string
     */
    public static function string_setting(string $name, string $fallback): string {
        $value = get_config('mod_interactembed', $name);
        return $value === false ? $fallback : (string)$value;
    }
}
