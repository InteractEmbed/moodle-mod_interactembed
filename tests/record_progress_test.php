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

namespace mod_interactembed;

use mod_interactembed\external\record_progress;
use mod_interactembed\local\progress_nonce;

/**
 * Tests authorisation and validation of the progress external service.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \mod_interactembed\external\record_progress
 */
final class record_progress_test extends \advanced_testcase {
    /** @var \stdClass Test activity. */
    private $activity;
    /** @var \stdClass Enrolled learner. */
    private $learner;
    /** @var string Per-display nonce. */
    private $nonce;

    /**
     * Sets up an enrolled learner and URL activity.
     */
    protected function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/modlib.php');
        $course = $this->getDataGenerator()->create_course();
        [, , , , $data] = prepare_new_moduleinfo_data($course, 'interactembed', 0);
        $data->name = 'External progress';
        $data->intro = '';
        $data->introformat = FORMAT_HTML;
        $data->sourcetype = 'url';
        $data->sourceurl = 'https://youtu.be/dQw4w9WgXcQ';
        $data->entrypoint = 'index.html';
        $data->showdescription = 0;
        $data->visibleoncoursepage = 1;
        $data->grade = 10;
        $data->cmidnumber = '';
        $this->activity = add_moduleinfo($data, $course);
        $this->learner = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->learner->id, $course->id, 'student');
        $this->setUser($this->learner);
        $this->nonce = progress_nonce::issue($this->activity->coursemodule);
    }

    /**
     * Verifies unscored progress and scored completion for the current user.
     */
    public function test_records_progress_and_grade(): void {
        global $DB;
        $cmid = $this->activity->coursemodule;
        $result = record_progress::execute($cmid, $this->nonce, 'inprogress');
        $this->assertSame(['success' => true, 'status' => 'inprogress'], $result);
        $result = record_progress::execute($cmid, $this->nonce, 'completed', 8, 10);
        $this->assertSame($result, record_progress::clean_returnvalue(record_progress::execute_returns(), $result));
        $this->assertSame('completed', $result['status']);
        $record = $DB->get_record('interactembed_progress', ['interactembedid' => $this->activity->instance], '*', MUST_EXIST);
        $this->assertEquals($this->learner->id, $record->userid);
        $this->assertEquals(8, $record->score);
        $item = $DB->get_record('grade_items', ['itemmodule' => 'interactembed', 'iteminstance' => $this->activity->instance]);
        $this->assertEquals(8, $DB->get_field('grade_grades', 'rawgrade', ['itemid' => $item->id, 'userid' => $this->learner->id]));
        $this->assertSame('completed', record_progress::execute($cmid, $this->nonce, 'inprogress')['status']);
    }

    /**
     * Rejects a valid nonce issued for a different module.
     */
    public function test_rejects_nonce_for_another_module(): void {
        $nonce = progress_nonce::issue($this->activity->coursemodule + 100);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidprogressnonce', 'mod_interactembed'));
        record_progress::execute($this->activity->coursemodule, $nonce, 'completed');
    }

    /**
     * Rejects a learner without the submission capability.
     */
    public function test_rejects_missing_capability(): void {
        global $DB;
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $context = \context_module::instance($this->activity->coursemodule);
        assign_capability('mod/interactembed:submitprogress', CAP_PROHIBIT, $roleid, $context->id);
        $this->expectException(\required_capability_exception::class);
        record_progress::execute($this->activity->coursemodule, $this->nonce, 'completed');
    }

    /**
     * Rejects a user outside the course even with a valid session nonce.
     */
    public function test_rejects_unenrolled_user(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        $nonce = progress_nonce::issue($this->activity->coursemodule);
        $this->expectException(\moodle_exception::class);
        record_progress::execute($this->activity->coursemodule, $nonce, 'completed');
    }

    /**
     * Rejects an unsupported status without writing progress.
     */
    public function test_rejects_invalid_status(): void {
        $this->expectException(\invalid_parameter_exception::class);
        record_progress::execute($this->activity->coursemodule, $this->nonce, 'reset');
    }

    /**
     * Rejects an incomplete score pair.
     */
    public function test_rejects_unpaired_score(): void {
        $this->expectException(\invalid_parameter_exception::class);
        record_progress::execute($this->activity->coursemodule, $this->nonce, 'completed', 8);
    }

    /**
     * Rejects a score above its maximum.
     */
    public function test_rejects_out_of_range_score(): void {
        $this->expectException(\invalid_parameter_exception::class);
        record_progress::execute($this->activity->coursemodule, $this->nonce, 'completed', 11, 10);
    }

    /**
     * Rejects malformed parameter types before resolving the activity.
     */
    public function test_rejects_malformed_nonce(): void {
        $this->expectException(\invalid_parameter_exception::class);
        record_progress::execute($this->activity->coursemodule, '<script>', 'completed');
    }
}
