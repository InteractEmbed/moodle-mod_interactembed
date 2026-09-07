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

/**
 * Validates ZIP metadata before extraction.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class archive_validator {
    /** Maximum number of path components in one entry. */
    private const MAX_PATH_DEPTH = 50;

    /**
     * Validates one stored ZIP archive.
     *
     * @param \stored_file $archive Archive file.
     * @return array Archive entries.
     */
    public static function validate(\stored_file $archive): array {
        self::validate_zip_metadata($archive);
        $entries = $archive->list_files(get_file_packer('application/zip'));
        if (!is_array($entries)) {
            throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
        }

        $maxfiles = max(1, (int) (get_config('mod_interactembed', 'maxarchivefiles') ?: 5000));
        $maxbytes = max(1048576, (int) (get_config('mod_interactembed', 'maxarchivebytes') ?: 209715200));
        if (count($entries) > $maxfiles) {
            throw new \moodle_exception('packageunsafearchive', 'mod_interactembed');
        }

        $totalbytes = 0;
        foreach ($entries as $entry) {
            $path = str_replace('\\', '/', (string) ($entry->original_pathname ?? $entry->pathname));
            $totalbytes += max(0, (int) ($entry->size ?? 0));
            if (self::is_unsafe_path($path) || $totalbytes > $maxbytes) {
                throw new \moodle_exception('packageunsafearchive', 'mod_interactembed');
            }
        }
        return $entries;
    }

    /**
     * Validates one unpacked relative asset path.
     *
     * @param string $path Relative path.
     */
    public static function validate_path(string $path): void {
        if (self::is_unsafe_path(str_replace('\\', '/', $path))) {
            throw new \moodle_exception('packageunsafefile', 'mod_interactembed', '', $path);
        }
    }

    /**
     * Validates the limits and paths of an unpacked stored-file collection.
     *
     * @param \stored_file[] $files Files to validate.
     * @return string[] Safe relative paths in source order.
     */
    public static function validate_files(array $files): array {
        $maxfiles = max(1, (int) (get_config('mod_interactembed', 'maxarchivefiles') ?: 5000));
        $maxbytes = max(1048576, (int) (get_config('mod_interactembed', 'maxarchivebytes') ?: 209715200));
        $paths = [];
        $totalbytes = 0;

        foreach ($files as $file) {
            if (!$file instanceof \stored_file || $file->is_directory()) {
                continue;
            }
            if (count($paths) >= $maxfiles) {
                throw new \moodle_exception('packageunsafearchive', 'mod_interactembed');
            }
            $directory = trim($file->get_filepath(), '/');
            $path = ($directory === '' ? '' : $directory . '/') . $file->get_filename();
            self::validate_path($path);
            $depth = substr_count($path, '/') + 1;
            $totalbytes += max(0, (int) $file->get_filesize());
            if ($depth > self::MAX_PATH_DEPTH || $totalbytes > $maxbytes || isset($paths[$path])) {
                throw new \moodle_exception('packageunsafearchive', 'mod_interactembed');
            }
            $paths[$path] = true;
        }

        return array_keys($paths);
    }

    /**
     * Detects unsafe package paths.
     *
     * @param string $path Relative path.
     * @return bool
     */
    private static function is_unsafe_path(string $path): bool {
        if ($path === '' || strpos($path, "\0") !== false || substr($path, 0, 1) === '/') {
            return true;
        }
        return (bool) (
            preg_match('~^[A-Za-z]:/~', $path) ||
            preg_match('~(^|/)\.\.(/|$)~', $path) ||
            preg_match('~(^|/)\.(/|$)~', $path) ||
            strpos($path, '//') !== false ||
            preg_match('/[\x00-\x1F\x7F]/', $path) ||
            preg_match('~\.zip$~i', rtrim($path, '/'))
        );
    }

    /**
     * Checks metadata exposed by ZipArchive.
     *
     * @param \stored_file $archive Archive file.
     */
    private static function validate_zip_metadata(\stored_file $archive): void {
        $pathname = tempnam(make_request_directory(), 'interactembed-zip-');
        if ($pathname === false || !$archive->copy_content_to($pathname)) {
            throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
        }

        $zip = new \ZipArchive();
        $opened = false;
        try {
            if ($zip->open($pathname) !== true) {
                throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
            }
            $opened = true;
            $seenpaths = [];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index, \ZipArchive::FL_UNCHANGED);
                if (!is_array($stat) || !isset($stat['name'])) {
                    throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
                }
                $path = str_replace('\\', '/', (string) $stat['name']);
                $normalised = rtrim($path, '/');
                $depth = $normalised === '' ? 0 : substr_count($normalised, '/') + 1;
                if ($depth > self::MAX_PATH_DEPTH || isset($seenpaths[$normalised]) || self::is_unsafe_path($path)) {
                    throw new \moodle_exception('packageunsafearchive', 'mod_interactembed');
                }
                $seenpaths[$normalised] = true;

                $opsys = 0;
                $attributes = 0;
                if (
                    $zip->getExternalAttributesIndex($index, $opsys, $attributes) &&
                        (($attributes >> 16) & 0170000) === 0120000
                ) {
                    throw new \moodle_exception('packageunsafearchive', 'mod_interactembed');
                }
            }
        } finally {
            if ($opened) {
                $zip->close();
            }
            if (file_exists($pathname)) {
                unlink($pathname);
            }
        }
    }
}
