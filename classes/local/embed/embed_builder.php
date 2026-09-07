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

use mod_interactembed\local\embed\provider\canva;
use mod_interactembed\local\embed\provider\genially;
use mod_interactembed\local\embed\provider\google_workspace;
use mod_interactembed\local\embed\provider\passthrough;
use mod_interactembed\local\embed\provider\youtube;

/**
 * Extracts and safely converts an editor-provided URL or iframe.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class embed_builder {
    /**
     * Builds a canonical embed result.
     *
     * @param string $input URL or iframe markup
     * @param bool|null $allowgeneric whether unknown providers may pass through
     * @param string|null $alloweddomains additional generic domains allowed by the administrator
     * @return embed_result
     */
    public static function build(
        string $input,
        ?bool $allowgeneric = null,
        ?string $alloweddomains = null
    ): embed_result {
        $url = self::extract_url($input);
        self::validate_url($url);

        $providers = [new youtube(), new google_workspace(), new canva(), new genially(), new passthrough()];
        foreach ($providers as $provider) {
            if ($provider->supports($url)) {
                return $provider->convert($url);
            }
        }

        if ($allowgeneric === null) {
            $configured = get_config('mod_interactembed', 'allowgenericurls');
            $allowgeneric = $configured === false ? false : (bool)$configured;
        }
        if ($alloweddomains === null) {
            $configured = get_config('mod_interactembed', 'alloweddomains');
            $alloweddomains = $configured === false ? '' : (string)$configured;
        }
        if (!domain_policy::allows($url, $alloweddomains, $allowgeneric)) {
            throw new \InvalidArgumentException('Generic embed URLs are disabled.');
        }

        return new embed_result($url, 'generic');
    }

    /**
     * Extracts only the src value from iframe markup.
     *
     * @param string $input URL or iframe markup
     * @return string
     */
    private static function extract_url(string $input): string {
        $input = trim($input);
        if ($input === '') {
            throw new \InvalidArgumentException('Empty embed source.');
        }

        if (stripos($input, '<iframe') === false) {
            if (strpos($input, '<') !== false || strpos($input, '>') !== false) {
                throw new \InvalidArgumentException('Unexpected markup.');
            }
            return html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (!preg_match('~<iframe\b[^>]*\bsrc\s*=\s*([\"\'])(.*?)\1[^>]*>~is', $input, $matches)) {
            throw new \InvalidArgumentException('Iframe source is missing.');
        }
        return trim(html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Rejects dangerous or malformed remote URLs.
     *
     * @param string $url URL to validate
     */
    private static function validate_url(string $url): void {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Malformed URL.');
        }
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            throw new \InvalidArgumentException('Unsupported URL scheme.');
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new \InvalidArgumentException('Credentials are not permitted in embed URLs.');
        }
    }
}
