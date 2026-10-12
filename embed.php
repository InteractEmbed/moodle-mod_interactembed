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
 * Displays the same-site embedded activity player.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($id, 'interactembed');
require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/interactembed:view', $context);
$instance = $DB->get_record('interactembed', ['id' => $cm->instance], '*', MUST_EXIST);

$PAGE->set_url('/mod/interactembed/embed.php', ['id' => $cm->id]);
$PAGE->set_context($context);
$PAGE->set_pagelayout('embedded');
$PAGE->set_pagetype('mod-interactembed-embedded');
$PAGE->set_title(format_string($instance->name));
$PAGE->set_heading($PAGE->title);
$PAGE->set_cacheable(false);
header("Content-Security-Policy: frame-ancestors 'self'", false);

$player = \mod_interactembed\local\content_factory::from_instance($instance, $context, false);
$messagenonce = \mod_interactembed\local\progress_nonce::issue((int) $cm->id);
$tracking = [
    'cmid' => (int) $cm->id,
    'expectedorigin' => $player->get_expected_origin(),
    'allowopaqueorigin' => in_array($instance->sourcetype, ['package', 'guide'], true),
    'messagenonce' => $messagenonce,
];
$PAGE->requires->js_call_amd('mod_interactembed/player', 'init', [$player->get_dom_id(), $tracking]);
$PAGE->requires->js_call_amd('mod_interactembed/embed_child', 'init', [(int) $cm->id]);

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('mod_interactembed/content', $player->export_for_template($OUTPUT));
echo $OUTPUT->footer();
