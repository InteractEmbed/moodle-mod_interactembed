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
 * Converts supported Genially viewing URLs.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class genially extends base_provider implements provider_interface {
    /**
     * Checks whether the URL is a public Genially viewing URL.
     *
     * @param string $url validated URL
     * @return bool
     */
    public function supports(string $url): bool {
        $host = $this->host($url);
        return $host === 'view.genially.com' || $host === 'genial.ly';
    }

    /**
     * Converts the URL to the canonical Genially viewer.
     *
     * @param string $url validated URL
     * @return embed_result
     */
    public function convert(string $url): embed_result {
        $parts = parse_url($url);
        $path = trim($parts['path'] ?? '', '/');
        if ($path === '') {
            throw new \InvalidArgumentException('Unsupported Genially URL.');
        }
        if ($this->host($url) === 'genial.ly') {
            return new embed_result($url, 'genially');
        }
        return new embed_result('https://view.genially.com/' . $path, 'genially');
    }
}
