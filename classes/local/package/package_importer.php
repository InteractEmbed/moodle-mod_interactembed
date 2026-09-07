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
use context_user;

/**
 * Validates and atomically imports HTML5 packages.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class package_importer {
    /**
     * Validates files in the current user's draft area.
     *
     * @param int $draftitemid Draft item id.
     * @param string $entrypoint Configured entry point.
     */
    public static function validate_draft(int $draftitemid, string $entrypoint): void {
        global $USER;

        $entrypoint = entrypoint_locator::normalise_path($entrypoint);
        $files = get_file_storage()->get_area_files(
            context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftitemid,
            'id ASC',
            false
        );
        if (!$files) {
            throw new \moodle_exception('packagefilesrequired', 'mod_interactembed');
        }

        $archive = self::archive_from_collection($files);
        $paths = [];
        if ($archive) {
            foreach (archive_validator::validate($archive) as $entry) {
                if (empty($entry->is_directory)) {
                    $paths[] = str_replace('\\', '/', (string) $entry->pathname);
                }
            }
        } else {
            $paths = archive_validator::validate_files($files);
        }
        self::validate_entrypoint_paths($paths, $entrypoint);
    }

    /**
     * Imports a draft into an inactive package item id.
     *
     * @param context_module $context Activity context.
     * @param int $draftitemid Draft item id.
     * @param string $entrypoint Configured entry point.
     * @param int $itemid New package item id.
     * @return \stored_file Located entry point.
     */
    public static function import_draft(
        context_module $context,
        int $draftitemid,
        string $entrypoint,
        int $itemid
    ): \stored_file {
        global $USER;

        $entrypoint = entrypoint_locator::normalise_path($entrypoint);
        $fs = get_file_storage();
        self::delete_revision($context, $itemid);

        try {
            file_save_draft_area_files(
                $draftitemid,
                $context->id,
                'mod_interactembed',
                'packagestage',
                $itemid,
                ['subdirs' => true, 'maxfiles' => -1]
            );
            $files = $fs->get_area_files(
                $context->id,
                'mod_interactembed',
                'packagestage',
                $itemid,
                'id ASC',
                false
            );
            if (!$files) {
                throw new \moodle_exception('packagefilesrequired', 'mod_interactembed');
            }

            $archive = self::archive_from_collection($files);
            if ($archive) {
                archive_validator::validate($archive);
                $result = get_file_packer('application/zip')->extract_to_storage(
                    $archive,
                    $context->id,
                    'mod_interactembed',
                    'content',
                    $itemid,
                    '/',
                    $USER->id
                );
                if (!is_array($result)) {
                    throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
                }
            } else {
                archive_validator::validate_files($files);
                foreach ($files as $file) {
                    $fs->create_file_from_storedfile([
                        'contextid' => $context->id,
                        'component' => 'mod_interactembed',
                        'filearea' => 'content',
                        'itemid' => $itemid,
                    ], $file);
                }
            }

            $entryfile = entrypoint_locator::locate($context, $itemid, $entrypoint);
        } catch (\Throwable $exception) {
            self::delete_revision($context, $itemid);
            throw $exception;
        }

        $fs->delete_area_files($context->id, 'mod_interactembed', 'packagestage', $itemid);
        return $entryfile;
    }

    /**
     * Imports an already unpacked, immutable Creator snapshot.
     *
     * @param context_module $context Activity context.
     * @param \stored_file[] $sourcefiles Source files.
     * @param string $entrypoint Declared entry point.
     * @param int $itemid Inactive revision item id.
     * @param string $expectedhash Creator API v1 canonical hash.
     * @return \stored_file Located entry point.
     */
    public static function import_stored_files(
        context_module $context,
        array $sourcefiles,
        string $entrypoint,
        int $itemid,
        string $expectedhash
    ): \stored_file {
        $entrypoint = entrypoint_locator::normalise_path($entrypoint);
        $fs = get_file_storage();
        self::delete_revision($context, $itemid);
        try {
            $paths = archive_validator::validate_files($sourcefiles);
            if (!$paths) {
                throw new \moodle_exception('packagefilesrequired', 'mod_interactembed');
            }
            if (!package_hash::verify($sourcefiles, $expectedhash)) {
                throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
            }
            foreach ($sourcefiles as $file) {
                if (!$file instanceof \stored_file || $file->is_directory()) {
                    continue;
                }
                $fs->create_file_from_storedfile([
                    'contextid' => $context->id,
                    'component' => 'mod_interactembed',
                    'filearea' => 'content',
                    'itemid' => $itemid,
                    'filepath' => $file->get_filepath(),
                    'filename' => $file->get_filename(),
                ], $file);
            }
            $targetfiles = $fs->get_area_files(
                $context->id,
                'mod_interactembed',
                'content',
                $itemid,
                'filepath, filename',
                false
            );
            archive_validator::validate_files($targetfiles);
            if (!package_hash::verify($targetfiles, $expectedhash)) {
                throw new \moodle_exception('packageinvalidarchive', 'mod_interactembed');
            }
            self::validate_entrypoint_paths($paths, $entrypoint);
            return entrypoint_locator::locate($context, $itemid, $entrypoint);
        } catch (\Throwable $exception) {
            self::delete_revision($context, $itemid);
            throw $exception;
        }
    }

    /**
     * Deletes temporary and final files for one inactive revision.
     *
     * @param context_module $context Activity context.
     * @param int $itemid Package item id.
     */
    public static function delete_revision(context_module $context, int $itemid): void {
        $fs = get_file_storage();
        $fs->delete_area_files($context->id, 'mod_interactembed', 'packagestage', $itemid);
        $fs->delete_area_files($context->id, 'mod_interactembed', 'content', $itemid);
    }

    /**
     * Returns the sole ZIP or null for an unpacked collection.
     *
     * @param \stored_file[] $files Files.
     * @return \stored_file|null
     */
    private static function archive_from_collection(array $files): ?\stored_file {
        $archives = [];
        foreach ($files as $file) {
            if (strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) === 'zip') {
                $archives[] = $file;
            }
        }
        if (count($archives) > 1 || (count($archives) === 1 && count($files) !== 1)) {
            throw new \moodle_exception('packageonlyonearchive', 'mod_interactembed');
        }
        return count($archives) === 1 ? reset($archives) : null;
    }

    /**
     * Creates a relative path for one stored file.
     *
     * @param \stored_file $file File.
     * @return string
     */
    private static function stored_file_path(\stored_file $file): string {
        $directory = trim($file->get_filepath(), '/');
        return ($directory === '' ? '' : $directory . '/') . $file->get_filename();
    }

    /**
     * Ensures the exact or fallback entry point exists in a path list.
     *
     * @param string[] $paths Package paths.
     * @param string $entrypoint Normalised entry point.
     */
    private static function validate_entrypoint_paths(array $paths, string $entrypoint): void {
        if (in_array($entrypoint, $paths, true)) {
            return;
        }
        $indexes = array_filter($paths, static function (string $path): bool {
            return strtolower(basename($path)) === 'index.html';
        });
        if (count($indexes) !== 1) {
            throw new \moodle_exception('packageentrymissing', 'mod_interactembed');
        }
    }
}
