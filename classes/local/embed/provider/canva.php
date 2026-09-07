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
 * Converts Canva design share URLs.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class canva extends base_provider implements provider_interface {
    /**
     * Checks whether the URL belongs to Canva.
     *
     * @param string $url validated URL
     * @return bool
     */
    public function supports(string $url): bool {
        return $this->host_is($this->host($url), 'canva.com');
    }

    /**
     * Converts the URL to a Canva embed URL.
     *
     * @param string $url validated URL
     * @return embed_result
     */
    public function convert(string $url): embed_result {
        $parts = parse_url($url);
        if (!preg_match('~^/design/([^/]+)/(?:view|watch)~', $parts['path'] ?? '', $matches)) {
            throw new \InvalidArgumentException('Unsupported Canva URL.');
        }
        $target = 'https://www.canva.com/design/' . rawurlencode($matches[1]) . '/view?embed';
        return new embed_result($target, 'canva');
    }
}
