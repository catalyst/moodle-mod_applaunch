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
 * Test the complete_activity external function.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_applaunch\external\complete_activity;

defined('MOODLE_INTERNAL') || die();

class mod_applaunch_complete_activity_testcase extends advanced_testcase {

    /**
     * Run before every test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test exception thrown if completion not enabled for site.
     */
    public function test_activity_completed_if_completion_not_set_for_site() {
        global $CFG;

        // Set completion for site.
        $CFG->enablecompletion = 0;

        // Create user, course and module.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        // Debugging call saying mod created with completion while site completion not enabled..
        $this->assertDebuggingCalledCount(1);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Trigger external function.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:sitecompletionnotenabled', 'applaunch'));
        complete_activity::execute('/mod/applaunch/view.php?id=' . $applaunch->cmid);
    }

    /**
     * Test exception thrown if completion not enabled for course.
     */
    public function test_activity_completed_if_completion_not_set_for_course() {
        global $CFG;

        // Set completion for site.
        $CFG->enablecompletion = 1;

        // Create user, course and module.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 0]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        // Debugging call saying mod with completion created without course completion.
        $this->assertDebuggingCalledCount(1);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Trigger external function.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:activitycompletionnotenabled', 'applaunch'));
        complete_activity::execute('/mod/applaunch/view.php?id=' . $applaunch->cmid);
    }

    /**
     * Test that exception thrown if completion not set for activity.
     */
    public function test_activity_completed_if_completion_not_set_for_activity() {
        global $CFG;

        // Set completion for site.
        $CFG->enablecompletion = 1;

        // Create user, course and module.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_NONE,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Trigger external function.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:activitycompletionnotenabled', 'applaunch'));
        complete_activity::execute('/mod/applaunch/view.php?id=' . $applaunch->cmid);
    }

    /**
     * Test that exception thrown if completion not set for activity.
     */
    public function test_activity_completed_if_external_completion_not_enabled_for_activity() {
        global $CFG;

        // Set completion for site.
        $CFG->enablecompletion = 1;

        // Create user, course and module.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
            'completionexternal' => 0,
        ]);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Trigger external function.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:externalcompletionnotenabled', 'applaunch'));
        complete_activity::execute('/mod/applaunch/view.php?id=' . $applaunch->cmid);
    }

    /**
     * Test that external function sets activity to be complete.
     */
    public function test_activity_completed() {
        global $CFG;

        // Set completion for site.
        $CFG->enablecompletion = 1;

        // Create user, course and module.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        // Check module is not complete.
        $completioninfo = new completion_info($course);
        $modcompletiondata = $completioninfo->get_data((object) ['id' => $applaunch->cmid], false, $user->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $modcompletiondata->completionstate);

        // Trigger external function.
        $response = complete_activity::execute('/mod/applaunch/view.php?id=' . $applaunch->cmid);
        $this->assertEquals(['success' => true], $response);

        // Check module is complete.
        $completioninfo = new completion_info($course);
        $modcompletiondata = $completioninfo->get_data((object) ['id' => $applaunch->cmid]);
        $this->assertEquals(COMPLETION_COMPLETE, $modcompletiondata->completionstate);
    }
}
