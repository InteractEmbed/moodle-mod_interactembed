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
 * Converts YouTube share and viewing URLs.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class youtube extends base_provider implements provider_interface {
    /**
     * Checks whether the URL belongs to YouTube.
     *
     * @param string $url validated URL
     * @return bool
     */
    public function supports(string $url): bool {
        $host = $this->host($url);
        return $this->host_is($host, 'youtube.com') || $this->host_is($host, 'youtu.be') ||
            $this->host_is($host, 'youtube-nocookie.com');
    }

    /**
     * Converts the URL to a privacy-enhanced YouTube player URL.
     *
     * @param string $url validated URL
     * @return embed_result
     */
    public function convert(string $url): embed_result {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $id = '';

        if ($this->host_is($host, 'youtu.be')) {
            $id = explode('/', $path)[0] ?? '';
        } else if (preg_match('~^(?:embed|shorts)/([A-Za-z0-9_-]{6,})~', $path, $matches)) {
            $id = $matches[1];
        } else {
            parse_str($parts['query'] ?? '', $query);
            $id = (string)($query['v'] ?? '');
        }

        if (!preg_match('/^[A-Za-z0-9_-]{6,}$/', $id)) {
            throw new \InvalidArgumentException('Invalid YouTube video identifier.');
        }

        return new embed_result('https://www.youtube-nocookie.com/embed/' . rawurlencode($id), 'youtube');
    }
}
