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

namespace mod_interactembed\local;

use context_course;

/**
 * Optional, runtime-safe bridge to the InteractEmbed Library.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class creator_connector {
    /** Creator API class, deliberately kept as a string. */
    private const API_CLASS = '\\local_interactembedcreator\\publication_api';

    /**
     * Returns whether API v1 may be used in this course.
     *
     * @param context_course $context Course context.
     * @return bool
     */
    public static function available(context_course $context): bool {
        return site_policy::allows_source('package')
            && class_exists(self::API_CLASS)
            && defined(self::API_CLASS . '::API_VERSION')
            && constant(self::API_CLASS . '::API_VERSION') === 1
            && method_exists(self::API_CLASS, 'list_for_context')
            && method_exists(self::API_CLASS, 'get_snapshot')
            && has_capability('local/interactembedcreator:use', $context);
    }

    /**
     * Lists publications without creating a static dependency.
     *
     * @param context_course $context Course context.
     * @return array
     */
    public static function options(context_course $context): array {
        if (!self::available($context)) {
            return [];
        }
        try {
            $publications = call_user_func([self::API_CLASS, 'list_for_context'], $context);
        } catch (\Throwable $exception) {
            debugging($exception->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
        if (!$publications) {
            return [];
        }
        $options = [0 => get_string('creatorselectpublication', 'mod_interactembed')];
        foreach ($publications as $publication) {
            if (!isset($publication->id, $publication->name, $publication->publicationno)) {
                continue;
            }
            $options[(int) $publication->id] = format_string($publication->name, true, ['context' => $context]) .
                ' — ' . get_string('creatorpublicationnumber', 'mod_interactembed', (int) $publication->publicationno);
        }
        return count($options) > 1 ? $options : [];
    }

    /**
     * Returns the exact contextualized publication snapshot.
     *
     * @param context_course $context Course context.
     * @param int $publicationid Publication id.
     * @return \stdClass
     */
    public static function snapshot(context_course $context, int $publicationid): \stdClass {
        if ($publicationid <= 0 || !self::available($context)) {
            throw new \moodle_exception('creatorunavailable', 'mod_interactembed');
        }
        $snapshot = call_user_func([self::API_CLASS, 'get_snapshot'], $publicationid, $context);
        if (
            !$snapshot instanceof \stdClass || empty($snapshot->id) ||
            !isset($snapshot->entrypoint, $snapshot->packagehash) ||
            !is_string($snapshot->entrypoint) || !is_string($snapshot->packagehash) ||
            !isset($snapshot->files) || !is_array($snapshot->files) || !$snapshot->files ||
            !preg_match('/^[a-f0-9]{64}$/', $snapshot->packagehash)
        ) {
            throw new \moodle_exception('creatorpublicationinvalid', 'mod_interactembed');
        }
        return $snapshot;
    }
}
