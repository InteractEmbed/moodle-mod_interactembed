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

namespace mod_interactembed\external;

defined('MOODLE_INTERNAL') || die();

// Moodle 4.0 and 4.1 predate the namespaced external API.
if (!class_exists(\core_external\external_api::class)) {
    require_once($CFG->libdir . '/externallib.php');
    class_alias(\external_api::class, \core_external\external_api::class);
    class_alias(\external_function_parameters::class, \core_external\external_function_parameters::class);
    class_alias(\external_single_structure::class, \core_external\external_single_structure::class);
    class_alias(\external_value::class, \core_external\external_value::class);
}

/**
 * Receives the existing player progress protocol through Moodle AJAX.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class record_progress extends \core_external\external_api {
    /**
     * Defines validated input parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function execute_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters([
            'cmid' => new \core_external\external_value(PARAM_INT, 'Course module id'),
            'nonce' => new \core_external\external_value(PARAM_ALPHANUM, 'Per-display progress nonce'),
            'status' => new \core_external\external_value(PARAM_ALPHA, 'inprogress or completed'),
            'score' => new \core_external\external_value(PARAM_FLOAT, 'Optional raw score', VALUE_DEFAULT, null, NULL_ALLOWED),
            'scoremax' => new \core_external\external_value(
                PARAM_FLOAT,
                'Optional maximum score',
                VALUE_DEFAULT,
                null,
                NULL_ALLOWED
            ),
        ]);
    }

    /**
     * Records progress for the current authorised learner.
     *
     * @param int $cmid Course module id.
     * @param string $nonce Per-display progress nonce.
     * @param string $status Progress status.
     * @param float|null $score Raw score.
     * @param float|null $scoremax Maximum score.
     * @return array Stored progress status.
     */
    public static function execute(
        int $cmid,
        string $nonce,
        string $status,
        ?float $score = null,
        ?float $scoremax = null
    ): array {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . '/mod/interactembed/lib.php');

        $params = self::validate_parameters(self::execute_parameters(), [
            'cmid' => $cmid,
            'nonce' => $nonce,
            'status' => $status,
            'score' => $score,
            'scoremax' => $scoremax,
        ]);
        [$course, $cm] = get_course_and_cm_from_cmid($params['cmid'], 'interactembed');
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_course_login($course, false, $cm);
        require_capability('mod/interactembed:submitprogress', $context);
        if (!\mod_interactembed\local\progress_nonce::validate($cm->id, $params['nonce'])) {
            throw new \moodle_exception('invalidprogressnonce', 'mod_interactembed');
        }
        $instance = $DB->get_record('interactembed', ['id' => $cm->instance], '*', MUST_EXIST);
        $progress = \mod_interactembed\local\progress_manager::record(
            $instance,
            $cm,
            (int) $USER->id,
            $params['status'],
            $params['score'] === null ? null : (float) $params['score'],
            $params['scoremax'] === null ? null : (float) $params['scoremax']
        );
        return ['success' => true, 'status' => $progress->status];
    }

    /**
     * Defines the service response.
     *
     * @return \core_external\external_single_structure
     */
    public static function execute_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure([
            'success' => new \core_external\external_value(PARAM_BOOL, 'Whether progress was recorded'),
            'status' => new \core_external\external_value(PARAM_ALPHA, 'Stored progress status'),
        ]);
    }
}
