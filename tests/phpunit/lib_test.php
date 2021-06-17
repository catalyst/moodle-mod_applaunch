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

use mod_applaunch\app_type;
use mod_applaunch\applaunch;

defined('MOODLE_INTERNAL') || die();

class mod_applaunch_lib_testcase extends advanced_testcase {

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
            'enabled' => 1
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
}
