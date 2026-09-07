<?php
// This file is part of Moodle - https://moodle.org/
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

/**
 * Receives validated learner progress from the activity player.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');

$cmid = required_param('cmid', PARAM_INT);
$nonce = required_param('nonce', PARAM_ALPHANUM);
$status = required_param('status', PARAM_ALPHA);
$scoreparam = optional_param('score', null, PARAM_FLOAT);
$scoremaxparam = optional_param('scoremax', null, PARAM_FLOAT);

require_sesskey();
[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'interactembed');
require_course_login($course, false, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/interactembed:submitprogress', $context);
if (!\mod_interactembed\local\progress_nonce::validate($cmid, $nonce)) {
    throw new moodle_exception('invalidprogressnonce', 'mod_interactembed');
}
$instance = $DB->get_record('interactembed', ['id' => $cm->instance], '*', MUST_EXIST);
$score = $scoreparam === null ? null : (float) $scoreparam;
$scoremax = $scoremaxparam === null ? null : (float) $scoremaxparam;
$progress = \mod_interactembed\local\progress_manager::record(
    $instance,
    $cm,
    (int) $USER->id,
    $status,
    $score,
    $scoremax
);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'status' => $progress->status]);
