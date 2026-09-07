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
 * Displays one InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($id, 'interactembed');
$instance = $DB->get_record('interactembed', ['id' => $cm->instance], '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/interactembed:view', $context);

$PAGE->set_url('/mod/interactembed/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

$completion = new completion_info($course);
$completion->set_module_viewed($cm);
$event = \mod_interactembed\event\course_module_viewed::create([
    'objectid' => $instance->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('interactembed', $instance);
$event->trigger();
$showembedtools = !empty($instance->showembedcode) &&
    \mod_interactembed\local\site_policy::allows_embed_code() &&
    has_capability('moodle/course:manageactivities', $context);
$player = \mod_interactembed\local\content_factory::from_instance($instance, $context, $showembedtools);
$messagenonce = \mod_interactembed\local\progress_nonce::issue((int) $cm->id);
$PAGE->requires->js_call_amd('mod_interactembed/player', 'init', [$player->get_dom_id(), [
    'cmid' => (int) $cm->id,
    'sesskey' => sesskey(),
    'endpoint' => (new moodle_url('/mod/interactembed/progress.php'))->out(false),
    'expectedorigin' => $player->get_expected_origin(),
    'allowopaqueorigin' => in_array($instance->sourcetype, ['package', 'guide'], true),
    'messagenonce' => $messagenonce,
]]);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($instance->name));
if (trim((string)$instance->intro) !== '') {
    echo $OUTPUT->box(format_module_intro('interactembed', $instance, $cm->id), 'generalbox mod_introbox');
}
echo $OUTPUT->render_from_template('mod_interactembed/content', $player->export_for_template($OUTPUT));
echo $OUTPUT->footer();
