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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace mod_interactembed\local\package;

use context_module;

/**
 * Locates a safe HTML entry point in one immutable package revision.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class entrypoint_locator {
    /**
     * Finds the configured entry point or an unambiguous index.html.
     *
     * @param context_module $context Activity context.
     * @param int $itemid Active package item id.
     * @param string $configured Configured relative path.
     * @return \stored_file
     */
    public static function locate(context_module $context, int $itemid, string $configured): \stored_file {
        $configured = self::normalise_path($configured);
        $pathinfo = pathinfo($configured);
        $filepath = empty($pathinfo['dirname']) || $pathinfo['dirname'] === '.'
            ? '/'
            : '/' . trim($pathinfo['dirname'], '/') . '/';
        $filename = $pathinfo['basename'] ?? '';

        $fs = get_file_storage();
        $file = $fs->get_file(
            $context->id,
            'mod_interactembed',
            'content',
            $itemid,
            $filepath,
            $filename
        );
        if ($file && !$file->is_directory() && self::is_html($filename)) {
            return $file;
        }

        $matches = [];
        $files = $fs->get_area_files(
            $context->id,
            'mod_interactembed',
            'content',
            $itemid,
            'filepath,filename',
            false
        );
        foreach ($files as $candidate) {
            if (strtolower($candidate->get_filename()) === 'index.html') {
                $matches[] = $candidate;
            }
        }
        if (count($matches) === 1) {
            return reset($matches);
        }

        throw new \moodle_exception('packageentrymissing', 'mod_interactembed');
    }

    /**
     * Validates and normalises a relative HTML entry point.
     *
     * @param string $path Submitted path.
     * @return string
     */
    public static function normalise_path(string $path): string {
        $path = str_replace('\\', '/', trim($path));
        if (
            $path === '' ||
            substr($path, 0, 1) === '/' ||
            preg_match('~^[A-Za-z]:/~', $path) ||
            strpos($path, "\0") !== false ||
            preg_match('~(^|/)\.\.?(/|$)~', $path) ||
            strpos($path, '//') !== false ||
            !self::is_html($path)
        ) {
            throw new \moodle_exception('packageentryinvalid', 'mod_interactembed');
        }
        return $path;
    }

    /**
     * Checks for an HTML extension.
     *
     * @param string $filename Filename or relative path.
     * @return bool
     */
    private static function is_html(string $filename): bool {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), ['html', 'htm'], true);
    }
}
