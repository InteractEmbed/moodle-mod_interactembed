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

use mod_interactembed\local\embed\embed_result;
use mod_interactembed\local\embed\provider_interface;

/**
 * Handles known providers whose shared URL is already embeddable.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class passthrough extends base_provider implements provider_interface {
    /** @var array known domain to provider map. */
    private const DOMAINS = [
        'padlet.com' => 'padlet',
        'wayground.com' => 'wayground',
        'quizizz.com' => 'wayground',
    ];

    /**
     * Checks whether the URL belongs to a known passthrough provider.
     *
     * @param string $url validated URL
     * @return bool
     */
    public function supports(string $url): bool {
        $host = $this->host($url);
        foreach (array_keys(self::DOMAINS) as $domain) {
            if ($this->host_is($host, $domain)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the validated URL with its provider identifier.
     *
     * @param string $url validated URL
     * @return embed_result
     */
    public function convert(string $url): embed_result {
        $host = $this->host($url);
        foreach (self::DOMAINS as $domain => $provider) {
            if ($this->host_is($host, $domain)) {
                return new embed_result($url, $provider);
            }
        }
        throw new \InvalidArgumentException('Unsupported provider URL.');
    }
}
