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

/**
 * Immutable result of an embed conversion.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class embed_result {
    /** @var string Safe canonical iframe URL. */
    private $url;

    /** @var string Provider identifier. */
    private $provider;

    /** @var string|null Expected message origin. */
    private $origin;

    /**
     * Constructor.
     *
     * @param string $url canonical URL
     * @param string $provider provider identifier
     */
    public function __construct(string $url, string $provider) {
        $this->url = $url;
        $this->provider = $provider;
        $parts = parse_url($url);
        $this->origin = isset($parts['scheme'], $parts['host'])
            ? strtolower($parts['scheme']) . '://' . strtolower($parts['host']) .
                (isset($parts['port']) ? ':' . (int)$parts['port'] : '')
            : null;
    }

    /**
     * Returns the canonical iframe URL.
     *
     * @return string canonical URL
     */
    public function get_url(): string {
        return $this->url;
    }

    /**
     * Returns the provider identifier.
     *
     * @return string provider identifier
     */
    public function get_provider(): string {
        return $this->provider;
    }

    /**
     * Returns the expected message origin.
     *
     * @return string|null expected origin
     */
    public function get_origin(): ?string {
        return $this->origin;
    }
}
