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
 * Displays the group-aware learner progress report.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');

$id = required_param('id', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($id, 'interactembed');
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/interactembed:viewreports', $context);
$instance = $DB->get_record('interactembed', ['id' => $cm->instance], '*', MUST_EXIST);

$groupmode = groups_get_activity_groupmode($cm);
$currentgroup = groups_get_activity_group($cm, true);
$users = get_enrolled_users(
    $context,
    'mod/interactembed:submitprogress',
    $currentgroup,
    'u.id,u.firstname,u.lastname'
);
$progress = $DB->get_records(
    'interactembed_progress',
    ['interactembedid' => $instance->id],
    '',
    'userid,status,score,scoremax,lastinteraction,timecompleted'
);

$PAGE->set_url('/mod/interactembed/report.php', ['id' => $cm->id]);
$PAGE->set_title(get_string('reporttitle', 'mod_interactembed'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reporttitle', 'mod_interactembed'));
if ($groupmode) {
    groups_print_activity_menu($cm, $PAGE->url);
}
$table = new html_table();
$table->head = [get_string('participant'), get_string('status'), get_string('score', 'grades'),
    get_string('lastinteraction', 'mod_interactembed')];
foreach ($users as $user) {
    $record = $progress[$user->id] ?? null;
    $score = $record && $record->score !== null && $record->scoremax !== null
        ? format_float($record->score, 2) . ' / ' . format_float($record->scoremax, 2) : '—';
    $table->data[] = [fullname($user), $record ? get_string('progressstatus:' . $record->status, 'mod_interactembed') :
        get_string('progressstatus:notstarted', 'mod_interactembed'), $score,
        $record ? userdate($record->lastinteraction) : '—'];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
