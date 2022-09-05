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
 * Test the lib functions.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

use mod_applaunch\app_type;
use mod_applaunch\applaunch;
use mod_applaunch\completion;

class lib_test extends \advanced_testcase {

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test adding instance of activity using formdata.
     */
    public function test_applaunch_add_instance() {
        $course = $this->getDataGenerator()->create_course();
        $apptype = new app_type(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'url' => 'fake://test.com',
            'enabled' => 1,
        ]);
        $apptype->save();
        $formdata = ['name' => 'Test name', 'course' => $course->id, 'apptypeid' => $apptype->get('id')];
        $id = applaunch_add_instance((object) $formdata);
        $applaunch = applaunch::get_record(['id' => $id]);
        $this->assertInstanceOf(applaunch::class, $applaunch);
        $this->assertEquals('Test name', $applaunch->get('name'));
        $this->assertEquals($course->id, $applaunch->get('course'));
        $this->assertEquals($apptype->get('id'), $applaunch->get('apptypeid'));
    }

    /**
     * Test updating instance of activity using formdata.
     */
    public function test_applaunch_update_instance() {
        $course = $this->getDataGenerator()->create_course();
        $applaunch = $this->getDataGenerator()->create_module('applaunch', ['course' => $course->id, 'name' => 'Test name']);
        $instance = new applaunch($applaunch->id);
        $this->assertEquals('Test name', $instance->get('name'));
        $formdata = ['instance' => $applaunch->id, 'name' => 'New name'];
        $this->assertTrue(applaunch_update_instance((object) $formdata));
        $instance->read();
        $this->assertEquals('New name', $instance->get('name'));
    }

    /**
     * Test deleting instance of activity.
     */
    public function test_applaunch_delete_instance() {
        $course = $this->getDataGenerator()->create_course();
        $applaunch = $this->getDataGenerator()->create_module('applaunch', ['course' => $course->id]);
        $this->assertNotFalse(applaunch::get_record(['id' => $applaunch->id]));
        applaunch_delete_instance($applaunch->id);
        $this->assertFalse(applaunch::get_record(['id' => $applaunch->id]));
    }

    /**
     * Test archive completion removes completion data for user.
     */
    public function test_applaunch_archive_completion() {
        // Set up data.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $applaunch = $this->getDataGenerator()->create_module('applaunch', ['course' => $course->id]);
        $instance = new applaunch($applaunch->id);
        $cm = $instance->get_cm();

        // Check it isn't complete.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get('state'));

        // Set completion to be complete.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $completion->set('state', COMPLETION_COMPLETE);
        $completion->save();

        // Check it's completed.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get('state'));

        // Archive completion.
        applaunch_archive_completion($user->id, $course->id);

        // Check it isn't complete.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get('state'));
    }

    /**
     * Test archive completion removes completion data for only single user.
     */
    public function test_applaunch_archive_completion_with_multiple_users() {
        // Set up data.
        $user = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->getDataGenerator()->enrol_user($user2->id, $course->id, 'student');
        $applaunch = $this->getDataGenerator()->create_module('applaunch', ['course' => $course->id]);
        $instance = new applaunch($applaunch->id);
        $cm = $instance->get_cm();

        // Check it isn't complete for both users.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get('state'));
        $completion = completion::get_by_userid_and_cmid($user2->id, $cm->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get('state'));

        // Set completion to be complete for both users.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $completion->set('state', COMPLETION_COMPLETE);
        $completion->save();
        $completion = completion::get_by_userid_and_cmid($user2->id, $cm->id);
        $completion->set('state', COMPLETION_COMPLETE);
        $completion->save();

        // Check it's completed for both users.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get('state'));
        $completion = completion::get_by_userid_and_cmid($user2->id, $cm->id);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get('state'));

        // Archive completion for first user only.
        applaunch_archive_completion($user->id, $course->id);

        // Check it isn't complete for first user only.
        $completion = completion::get_by_userid_and_cmid($user->id, $cm->id);
        $this->assertEquals(COMPLETION_INCOMPLETE, $completion->get('state'));
        $completion = completion::get_by_userid_and_cmid($user2->id, $cm->id);
        $this->assertEquals(COMPLETION_COMPLETE, $completion->get('state'));
    }
}
