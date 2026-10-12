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
 * Moodle callbacks for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Declares the Moodle features supported by the activity.
 *
 * @param string $feature Feature constant.
 * @return bool|string|null
 */
function interactembed_supports(string $feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_MOD_PURPOSE:
            if (defined('MOD_PURPOSE_INTERACTIVECONTENT')) {
                return MOD_PURPOSE_INTERACTIVECONTENT;
            }
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Creates an InteractEmbed activity instance.
 *
 * @param stdClass $data Submitted activity data.
 * @param mod_interactembed_mod_form|null $mform Activity form.
 * @return int New instance id.
 */
function interactembed_add_instance(stdClass $data, ?mod_interactembed_mod_form $mform = null): int {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->packageitemid = 0;
    $data->revision = 0;
    interactembed_normalise_source_data($data);
    $draftitemid = (int) ($data->packagefiles ?? 0);
    $packageorigin = (string) ($data->packageorigin ?? 'upload');
    $creatorpublicationid = (int) ($data->creatorpublicationid ?? 0);
    $coverdraftitemid = (int) ($data->coverfiles ?? 0);
    interactembed_validate_cover_draft($coverdraftitemid, (string) ($data->coveralt ?? ''));
    unset($data->packagefiles);
    unset($data->packageorigin);
    unset($data->creatorpublicationid);
    unset($data->coverfiles);
    interactembed_normalise_presentation_data($data);
    $instanceid = $DB->insert_record('interactembed', $data);
    $data->id = $instanceid;
    $package = null;

    try {
        if ($data->sourcetype === 'package') {
            $package = interactembed_import_package(
                $data,
                $draftitemid,
                0,
                $packageorigin,
                $creatorpublicationid
            );
            $data->packageitemid = $package['itemid'];
            $data->revision = $package['revision'];
        }
        $DB->update_record('interactembed', $data);
        interactembed_save_cover(context_module::instance($data->coursemodule), $coverdraftitemid);
        interactembed_grade_item_update($data);
    } catch (Throwable $exception) {
        $context = context_module::instance($data->coursemodule);
        if ($package) {
            \mod_interactembed\local\package\package_importer::delete_revision($context, $package['itemid']);
        }
        get_file_storage()->delete_area_files($context->id, 'mod_interactembed', 'cover');
        $DB->delete_records('interactembed', ['id' => $instanceid]);
        throw $exception;
    }
    return $instanceid;
}

/**
 * Updates an InteractEmbed activity instance.
 *
 * @param stdClass $data Submitted activity data.
 * @param mod_interactembed_mod_form|null $mform Activity form.
 * @return bool
 */
function interactembed_update_instance(stdClass $data, ?mod_interactembed_mod_form $mform = null): bool {
    global $DB;

    $data->id = $data->instance;
    $data->timemodified = time();
    $oldrecord = $DB->get_record('interactembed', ['id' => $data->id], '*', MUST_EXIST);
    if (!isset($data->sourcetype)) {
        $data->sourcetype = $oldrecord->sourcetype;
    }
    interactembed_normalise_source_data($data);
    $draftitemid = (int) ($data->packagefiles ?? 0);
    $packageorigin = (string) ($data->packageorigin ?? 'upload');
    $creatorpublicationid = (int) ($data->creatorpublicationid ?? 0);
    $coverdraftitemid = (int) ($data->coverfiles ?? 0);
    interactembed_validate_cover_draft($coverdraftitemid, (string) ($data->coveralt ?? ''));
    unset($data->packagefiles);
    unset($data->packageorigin);
    unset($data->creatorpublicationid);
    unset($data->coverfiles);
    interactembed_normalise_presentation_data($data);
    $package = null;
    if ($data->sourcetype === 'package') {
        $package = interactembed_import_package(
            $data,
            $draftitemid,
            (int) $oldrecord->revision,
            $packageorigin,
            $creatorpublicationid
        );
        $data->packageitemid = $package['itemid'];
        $data->revision = $package['revision'];
    } else {
        $data->packageitemid = 0;
        $data->revision = 0;
    }

    try {
        $result = $DB->update_record('interactembed', $data);
        if (!$result) {
            throw new moodle_exception('cannotupdate', 'error');
        }
        interactembed_save_cover(context_module::instance($data->coursemodule), $coverdraftitemid);
        interactembed_grade_item_update($data);
    } catch (Throwable $exception) {
        if ($package) {
            $context = context_module::instance($data->coursemodule);
            \mod_interactembed\local\package\package_importer::delete_revision($context, $package['itemid']);
        }
        throw $exception;
    }

    if (
        (int) $oldrecord->packageitemid > 0 &&
            (int) $oldrecord->packageitemid !== (int) $data->packageitemid
    ) {
        $context = context_module::instance($data->coursemodule);
        \mod_interactembed\local\package\package_importer::delete_revision(
            $context,
            (int) $oldrecord->packageitemid
        );
    }
    return true;
}

/**
 * Restricts source types to values implemented by this release.
 *
 * @param string $sourcetype Submitted source type.
 * @return string
 */
function interactembed_normalise_sourcetype(string $sourcetype): string {
    return in_array($sourcetype, ['url', 'package', 'guide'], true) ? $sourcetype : 'package';
}

/**
 * Normalises source fields and repeats URL validation on the server-side callback.
 *
 * @param stdClass $data Submitted instance data.
 */
function interactembed_normalise_source_data(stdClass $data): void {
    $data->sourcetype = interactembed_normalise_sourcetype((string) ($data->sourcetype ?? 'package'));
    if (!\mod_interactembed\local\site_policy::allows_source($data->sourcetype)) {
        throw new moodle_exception('sourcenotallowed', 'mod_interactembed');
    }
    if ($data->sourcetype !== 'url') {
        $data->sourceurl = '';
        return;
    }

    $data->sourceurl = trim((string) ($data->sourceurl ?? ''));
    try {
        \mod_interactembed\local\embed\embed_builder::build($data->sourceurl);
    } catch (InvalidArgumentException $exception) {
        throw new moodle_exception('sourceurlinvalid', 'mod_interactembed');
    }
}

/**
 * Normalises presentation and security-sensitive fields.
 *
 * @param stdClass $data Submitted instance data.
 */
function interactembed_normalise_presentation_data(stdClass $data): void {
    $ratios = ['16x9', '4x3', '1x1', '3x4', '9x16', 'custom'];
    $data->aspectratio = in_array($data->aspectratio ?? '', $ratios, true) ? $data->aspectratio : '16x9';
    $height = (int) ($data->customheight ?? 600);
    if ($data->aspectratio === 'custom' && ($height < 200 || $height > 4000)) {
        throw new moodle_exception('customheighterror', 'mod_interactembed');
    }
    $data->customheight = max(200, min(4000, $height));
    $data->displaymode = \mod_interactembed\local\configuration_guard::display_mode(
        (string) ($data->displaymode ?? 'inline')
    );
    $data->loadonrequest = empty($data->loadonrequest) ? 0 : 1;
    $data->showfullscreen = isset($data->showfullscreen) ? (empty($data->showfullscreen) ? 0 : 1) : 1;
    $data->lazyload = isset($data->lazyload) ? (empty($data->lazyload) ? 0 : 1) : 1;
    $data->coveralt = trim((string) ($data->coveralt ?? ''));
    $data->sandboxprofile = \mod_interactembed\local\configuration_guard::sandbox_profile(
        (string) ($data->sandboxprofile ?? 'standard'),
        has_capability('mod/interactembed:trustpackage', context_module::instance($data->coursemodule))
    );
    $data->showembedcode = \mod_interactembed\local\site_policy::allows_embed_code() &&
        !empty($data->showembedcode) ? 1 : 0;
}

/**
 * Requires alternative text whenever a submitted draft contains a cover image.
 *
 * @param int $draftitemid Cover draft item id.
 * @param string $coveralt Submitted alternative text.
 */
function interactembed_validate_cover_draft(int $draftitemid, string $coveralt): void {
    if ($draftitemid <= 0) {
        return;
    }
    $info = file_get_draft_area_info($draftitemid);
    if (!empty($info['filecount']) && trim($coveralt) === '') {
        throw new moodle_exception('coveraltrequired', 'mod_interactembed');
    }
}

/**
 * Replaces the optional cover through Moodle's Files API.
 *
 * @param context_module $context Activity context.
 * @param int $draftitemid Cover draft item id.
 */
function interactembed_save_cover(context_module $context, int $draftitemid): void {
    if ($draftitemid <= 0) {
        return;
    }
    file_save_draft_area_files(
        $draftitemid,
        $context->id,
        'mod_interactembed',
        'cover',
        0,
        [
            'subdirs' => false,
            'maxfiles' => 1,
            'maxbytes' => 5 * 1024 * 1024,
            'accepted_types' => ['image'],
        ]
    );
}

/**
 * Serves an activity cover image after normal Moodle access checks.
 *
 * @param stdClass $course Course record.
 * @param stdClass $cm Course-module record.
 * @param context $context File context.
 * @param string $filearea File area.
 * @param array $args File arguments.
 * @param bool $forcedownload Whether a download was requested.
 * @param array $options File serving options.
 */
function mod_interactembed_pluginfile(
    $course,
    $cm,
    $context,
    $filearea,
    $args,
    $forcedownload,
    array $options = []
): void {
    if (
        !$context instanceof context_module || $filearea !== 'cover' || (int) array_shift($args) !== 0 ||
            empty($args)
    ) {
        send_file_not_found();
    }
    require_login($course, true, $cm);
    require_capability('mod/interactembed:view', $context);
    $filename = array_pop($args);
    $filepath = empty($args) ? '/' : '/' . implode('/', $args) . '/';
    $file = get_file_storage()->get_file(
        $context->id,
        'mod_interactembed',
        'cover',
        0,
        $filepath,
        $filename
    );
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }
    header('X-Content-Type-Options: nosniff');
    send_stored_file($file, 0, 0, $forcedownload, $options);
}

/**
 * Deletes an InteractEmbed activity instance.
 *
 * @param int $id Instance id.
 * @return bool
 */
function interactembed_delete_instance(int $id): bool {
    global $DB;

    if (!$instance = $DB->get_record('interactembed', ['id' => $id])) {
        return false;
    }
    $cm = get_coursemodule_from_instance('interactembed', $id, $instance->course, false, IGNORE_MISSING);
    if ($cm) {
        $context = context_module::instance($cm->id);
        get_file_storage()->delete_area_files($context->id, 'mod_interactembed');
    }
    $DB->delete_records('interactembed_progress', ['interactembedid' => $id]);
    $DB->delete_records('interactembed', ['id' => $id]);
    interactembed_grade_item_delete($instance);
    return true;
}

/**
 * Creates or updates this activity's gradebook item.
 *
 * @param stdClass $instance Activity instance.
 * @param mixed $grades Grade data or the reset marker.
 * @return int Grade update status.
 */
function interactembed_grade_item_update(stdClass $instance, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    $params = [
        'itemname' => $instance->name,
        'idnumber' => $instance->cmidnumber ?? '',
        'gradetype' => empty($instance->grade) ? GRADE_TYPE_NONE : GRADE_TYPE_VALUE,
        'grademin' => 0,
        'grademax' => max(0, (float) ($instance->grade ?? 0)),
    ];
    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }
    return grade_update(
        'mod/interactembed',
        $instance->course,
        'mod',
        'interactembed',
        $instance->id,
        0,
        $grades,
        $params
    );
}

/**
 * Removes this activity's gradebook item.
 *
 * @param stdClass $instance Activity instance.
 * @return int Grade update status.
 */
function interactembed_grade_item_delete(stdClass $instance): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update(
        'mod/interactembed',
        $instance->course,
        'mod',
        'interactembed',
        $instance->id,
        0,
        null,
        ['deleted' => 1]
    );
}

/**
 * Pushes stored learner grades into the gradebook.
 *
 * @param stdClass $instance Activity instance.
 * @param int $userid Optional user identifier.
 */
function interactembed_update_grades(stdClass $instance, int $userid = 0): void {
    global $DB;
    if (empty($instance->grade)) {
        interactembed_grade_item_update($instance);
        return;
    }
    $conditions = ['interactembedid' => $instance->id];
    if ($userid) {
        $conditions['userid'] = $userid;
    }
    $grades = [];
    foreach ($DB->get_records('interactembed_progress', $conditions) as $progress) {
        if ($progress->score === null || empty($progress->scoremax)) {
            continue;
        }
        $grades[$progress->userid] = (object) [
            'userid' => $progress->userid,
            'rawgrade' => min(
                (float) $instance->grade,
                max(0, ((float) $progress->score / (float) $progress->scoremax) * (float) $instance->grade)
            ),
            'dategraded' => $progress->lastinteraction,
        ];
    }
    interactembed_grade_item_update($instance, $grades);
}

/**
 * Describes the enabled custom completion rule.
 *
 * @param cm_info $cm Course module information.
 * @return string[] Active rule descriptions.
 */
function mod_interactembed_get_completion_active_rule_descriptions($cm): array {
    if (
        ($cm->completion ?? 0) != COMPLETION_TRACKING_AUTOMATIC ||
            empty($cm->customdata['customcompletionrules']['completioncontent'])
    ) {
        return [];
    }
    return [get_string('completiondetail:content', 'mod_interactembed')];
}

/**
 * Adds the learner report to activity navigation for authorised users.
 *
 * @param settings_navigation $settingsnav Settings navigation tree.
 * @param navigation_node $node Activity navigation node.
 */
function interactembed_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $node): void {
    global $PAGE;
    if (has_capability('mod/interactembed:viewreports', $PAGE->context)) {
        $node->add(
            get_string('reporttitle', 'mod_interactembed'),
            new moodle_url('/mod/interactembed/report.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'interactembedreport'
        );
    }
}

/**
 * Adds InteractEmbed learner data to the course reset form.
 *
 * @param MoodleQuickForm $mform Reset form.
 */
function interactembed_reset_course_form_definition(&$mform): void {
    $mform->addElement('header', 'interactembedheader', get_string('modulenameplural', 'mod_interactembed'));
    $mform->addElement('advcheckbox', 'reset_interactembed', get_string('resetprogress', 'mod_interactembed'));
}

/**
 * Supplies the course reset default.
 *
 * @param stdClass $course Course record.
 * @return array Reset defaults.
 */
function interactembed_reset_course_form_defaults(stdClass $course): array {
    return ['reset_interactembed' => 1];
}

/**
 * Deletes learner progress and grades during a course reset.
 *
 * @param stdClass $data Course reset data.
 * @return array Reset status entries.
 */
function interactembed_reset_userdata(stdClass $data): array {
    global $DB;
    if (empty($data->reset_interactembed)) {
        return [];
    }
    $sql = "SELECT p.id
              FROM {interactembed_progress} p
              JOIN {interactembed} i ON i.id = p.interactembedid
             WHERE i.course = :courseid";
    $progressids = $DB->get_fieldset_sql($sql, ['courseid' => $data->courseid]);
    if ($progressids) {
        $DB->delete_records_list('interactembed_progress', 'id', $progressids);
    }
    if (empty($data->reset_gradebook_grades)) {
        foreach ($DB->get_records('interactembed', ['course' => $data->courseid]) as $instance) {
            interactembed_grade_item_update($instance, 'reset');
        }
    }
    return [[
        'component' => get_string('modulenameplural', 'mod_interactembed'),
        'item' => get_string('resetprogress', 'mod_interactembed'),
        'error' => false,
    ]];
}

/**
 * Imports a draft package into a new inactive revision.
 *
 * @param stdClass $data Activity data containing coursemodule and entrypoint.
 * @param int $draftitemid Draft item id.
 * @param int $oldrevision Previous revision number.
 * @param string $origin Package origin: upload or library.
 * @param int $creatorpublicationid InteractEmbed Library publication id.
 * @return array{itemid:int,revision:int}
 */
function interactembed_import_package(
    stdClass $data,
    int $draftitemid,
    int $oldrevision,
    string $origin = 'upload',
    int $creatorpublicationid = 0
): array {
    if (!in_array($origin, ['upload', 'library'], true)) {
        throw new invalid_parameter_exception('Unknown package origin.');
    }
    $context = context_module::instance($data->coursemodule);
    $revision = \mod_interactembed\local\configuration_guard::next_revision($oldrevision);
    $itemid = $revision;
    if ($origin === 'library') {
        $snapshot = \mod_interactembed\local\creator_connector::snapshot(
            context_course::instance($data->course),
            $creatorpublicationid
        );
        $data->entrypoint = $snapshot->entrypoint;
        \mod_interactembed\local\package\package_importer::import_stored_files(
            $context,
            $snapshot->files,
            $snapshot->entrypoint,
            $itemid,
            $snapshot->packagehash
        );
    } else {
        \mod_interactembed\local\package\package_importer::import_draft(
            $context,
            $draftitemid,
            (string) $data->entrypoint,
            $itemid
        );
    }
    return ['itemid' => $itemid, 'revision' => $revision];
}

/**
 * Loads the iframe resizer on destination pages on Moodle 4.0 to 4.3.
 *
 * Moodle 4.4 and later use the equivalent output hook in db/hooks.php.
 *
 * @return string Empty footer markup.
 */
function mod_interactembed_before_footer(): string {
    global $PAGE;
    $PAGE->requires->js_call_amd('mod_interactembed/embed_resizer', 'init');
    return '';
}
