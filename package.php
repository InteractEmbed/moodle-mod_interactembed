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

/**
 * Serves active HTML5 package files through a short-lived signed path.
 *
 * This endpoint deliberately does not use the Moodle session. Relative assets can therefore
 * load concurrently inside the sandboxed iframe without locking the user's session.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');

$relativepath = get_file_argument();
if (!$relativepath || substr($relativepath, 0, 1) !== '/') {
    send_file_not_found();
}

$parts = explode('/', ltrim($relativepath, '/'));
if (count($parts) < 3) {
    send_file_not_found();
}

$contextpart = (string) array_shift($parts);
$contextid = (int) $contextpart;
$accesskey = (string) array_shift($parts);
$filename = rawurldecode((string) array_pop($parts));
if ($contextid <= 0 || $contextpart !== (string) $contextid || $filename === '') {
    send_file_not_found();
}

$context = context::instance_by_id($contextid, IGNORE_MISSING);
if (!$context instanceof context_module) {
    send_file_not_found();
}

$cm = get_coursemodule_from_id('interactembed', $context->instanceid, 0, false, IGNORE_MISSING);
if (!$cm) {
    send_file_not_found();
}

$instance = $DB->get_record('interactembed', ['id' => $cm->instance], '*', IGNORE_MISSING);
if (
    !$instance || ($instance->sourcetype ?? 'package') !== 'package' ||
        (int) $instance->packageitemid <= 0 || (int) $instance->revision <= 0
) {
    send_file_not_found();
}

$requestedrevision = optional_param('rev', 0, PARAM_INT);
if (
    ($requestedrevision !== 0 && $requestedrevision !== (int) $instance->revision) ||
    !\mod_interactembed\local\package\package_access_key::validate(
        $accesskey,
        $context->id,
        (int) $instance->revision,
        (int) $instance->packageitemid
    )
) {
    send_file_not_found();
}

$decodedparts = array_map('rawurldecode', $parts);
$candidatepath = implode('/', array_merge($decodedparts, [$filename]));
try {
    \mod_interactembed\local\package\archive_validator::validate_path($candidatepath);
} catch (moodle_exception $exception) {
    send_file_not_found();
}

$filepath = empty($decodedparts) ? '/' : '/' . implode('/', $decodedparts) . '/';
$file = get_file_storage()->get_file(
    $context->id,
    'mod_interactembed',
    'content',
    (int) $instance->packageitemid,
    $filepath,
    $filename
);
if (!$file || $file->is_directory()) {
    send_file_not_found();
}

foreach (
    \mod_interactembed\local\package\package_response_policy::headers(
        $filename,
        (string) ($instance->sandboxprofile ?? 'standard')
    ) as $headername => $headervalue
) {
    header($headername . ': ' . $headervalue);
}

$requestmethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($requestmethod === 'OPTIONS') {
    http_response_code(204);
    exit;
}
if (!in_array($requestmethod, ['GET', 'HEAD'], true)) {
    header('Allow: GET, HEAD, OPTIONS');
    http_response_code(405);
    exit;
}

send_stored_file($file, 0, 0, false);
