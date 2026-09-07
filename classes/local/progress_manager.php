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
 * Learner progress persistence.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_interactembed\local;

/**
 * Persists validated learner results and synchronises Moodle completion and grades.
 */
final class progress_manager {
    /**
     * Stores a validated result and updates Moodle completion and grades.
     *
     * @param \stdClass $instance Activity record.
     * @param \cm_info $cm Course-module information.
     * @param int $userid Learner id.
     * @param string $status Progress status.
     * @param float|null $score Raw score.
     * @param float|null $scoremax Maximum raw score.
     * @return \stdClass Stored progress record.
     */
    public static function record(
        \stdClass $instance,
        \cm_info $cm,
        int $userid,
        string $status,
        ?float $score,
        ?float $scoremax
    ): \stdClass {
        global $DB;
        if (!in_array($status, ['inprogress', 'completed'], true)) {
            throw new \invalid_parameter_exception('Invalid progress status');
        }
        if (
            ($score === null) !== ($scoremax === null) ||
                ($score !== null && (!is_finite($score) || !is_finite($scoremax) ||
                $scoremax <= 0 || $score < 0 || $score > $scoremax))
        ) {
            throw new \invalid_parameter_exception('Invalid score range');
        }
        $now = time();
        $record = $DB->get_record('interactembed_progress', [
            'interactembedid' => $instance->id,
            'userid' => $userid,
        ]);
        if (!$record) {
            $record = (object) [
                'interactembedid' => $instance->id,
                'userid' => $userid,
                'firstinteraction' => $now,
            ];
        }
        // Completion is monotonic; an iframe cannot undo an achieved result.
        if (($record->status ?? '') !== 'completed') {
            $record->status = $status;
        }
        if ($status === 'completed' && empty($record->timecompleted)) {
            $record->timecompleted = $now;
        }
        $record->score = $score;
        $record->scoremax = $scoremax;
        $record->lastinteraction = $now;
        if (empty($record->id)) {
            $record->id = $DB->insert_record('interactembed_progress', $record);
        } else {
            $DB->update_record('interactembed_progress', $record);
        }

        if (!empty($instance->grade) && $score !== null) {
            $grade = (object) [
                'userid' => $userid,
                'rawgrade' => ($score / $scoremax) * (float) $instance->grade,
                'dategraded' => $now,
            ];
            interactembed_grade_item_update($instance, $grade);
        }
        if (!empty($instance->completioncontent)) {
            $completion = new \completion_info(get_course($instance->course));
            $completion->update_state(
                $cm,
                $record->status === 'completed' ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE,
                $userid
            );
        }
        \mod_interactembed\event\progress_updated::create([
            'objectid' => $record->id,
            'context' => \context_module::instance($cm->id),
            'userid' => $userid,
            'other' => ['interactembedid' => (int) $instance->id, 'status' => $record->status],
        ])->trigger();
        return $record;
    }
}
