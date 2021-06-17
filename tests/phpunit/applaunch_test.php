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
 * Test the applaunch persistent class.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_applaunch\app_type;
use mod_applaunch\applaunch;

defined('MOODLE_INTERNAL') || die();

class mod_applaunch_applaunch_testcase extends advanced_testcase {

    /** @var app_type $apptype App type. */
    protected $apptype;

    /** @var stdClass $course Moodle course. */
    protected $course;

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
        $this->course = $this->getDataGenerator()->create_course();
        $this->apptype = new app_type(0, (object) ['name' => 'Test App', 'description' => 'Test description',
            'url' => 'fake://test.com', 'icon' => 'https://icon.com', 'enabled' => 1]);
        $this->apptype->save();
    }

    /**
     * This method runs after every test.
     */
    protected function tearDown(): void {
        $this->course = null;
        $this->apptype = null;
    }

    /**
     * Test creating applaunch instance with various data.
     *
     * @dataProvider instance_data_provider
     */
    public function test_create_instance($data) {
        global $DB;
        $data->course = $this->course->id;
        $data->apptypeid = $this->apptype->get('id');
        $instance = new applaunch(0, $data);
        $instance->save();
        $records = $DB->get_records('applaunch');
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertEquals($data->name, $record->name);
        $this->assertEquals($data->apptypeid, $record->apptypeid);
    }

    /**
     * Test applaunch instance creation fails with various data.
     *
     * @dataProvider bad_instance_data_provider
     */
    public function test_cannot_create_instance($data) {
        // Update course and apptypeid with calculated value if the params are set with empty value.
        if (isset($data->course) && empty($data->course)) {
            $data->course = $this->course->id;
        }
        if (isset($data->apptypeid) && empty($data->apptypeid)) {
            $data->apptypeid = $this->apptype->get('id');
        }
        $this->expectException(\core\invalid_persistent_exception::class);
        $instance = new applaunch(0, $data);
        $instance->save();
    }

    /**
     * Test inputs are trimmed.
     */
    public function test_input_are_trimmed() {
        $instance = new applaunch(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'course' => $this->course->id,
            'urlslug' => '  test ',
            'apptypeid' => $this->apptype->get('id'),
            'completionexternal' => 1]);
        $instance->save();
        $this->assertEquals('test', $instance->get('urlslug'));
    }

    /**
     * Test the launch url is generated.
     */
    public function test_get_url() {
        $instance = new applaunch(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'course' => $this->course->id,
            'urlslug' => '?test=1',
            'apptypeid' => $this->apptype->get('id'),
            'completionexternal' => 1]);
        $instance->save();
        $expected = 'fake://test.com?test=1&token=123';
        $url = $instance->get_url('123');
        $this->assertEquals($expected, $url);
    }

    public function test_process_mod_form_data() {

    }

    /**
     * Test getting the related cm.
     */
    public function test_get_cm() {
        $applaunch = $this->getDataGenerator()->create_module('applaunch', ['course' => $this->course->id]);
        $instance = new applaunch($applaunch->id);
        $cm = $instance->get_cm();
        $modinfo = get_fast_modinfo($this->course);
        foreach ($modinfo->get_cms() as $modcm) {
            if ($modcm->modname == 'applaunch' && $modcm->instance == $applaunch->id) {
                $expected = $modcm;
                break;
            }
        }
        $this->assertEquals($expected->id, $cm->id);
        $this->assertEquals($expected->course, $cm->course);
        $this->assertEquals($expected->module, $cm->module);
        $this->assertEquals($expected->added, $cm->added);
    }

    /**
     * Test getting the related cm if none has been created.
     */
    public function test_get_cm_not_found() {
        $instance = new applaunch(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'course' => $this->course->id,
            'urlslug' => '?test=1',
            'apptypeid' => $this->apptype->get('id'),
            'completionexternal' => 1]);
        $instance->save();
        $this->expectException(dml_exception::class);
        $instance->get_cm();
    }

    /**
     * Test if external completion for this activity is enabled.
     */
    public function test_is_external_completion_enabled() {
        $applaunch = new applaunch(0, (object) ['name' => 'Test App', 'description' => 'Test description',
            'course' => $this->course->id, 'urlslug' => 'test', 'apptypeid' => $this->apptype->get('id'), 'completionexternal' => 1]);
        $applaunch->save();
        $this->assertTrue($applaunch->is_external_completion_enabled());
    }

    /**
     * Test if external completion for this activity is disabled.
     */
    public function test_is_external_completion_disabled() {
        $applaunch = new applaunch(0, (object) ['name' => 'Test App', 'description' => 'Test description',
            'course' => $this->course->id, 'urlslug' => 'test', 'apptypeid' => $this->apptype->get('id'), 'completionexternal' => 0]);
        $applaunch->save();
        $this->assertFalse($applaunch->is_external_completion_enabled());
    }

    /**
     * Provide data to create valid applaunch instances.
     *
     * @return object[][]
     */
    public function instance_data_provider(): array {
        return [
            'All data' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'course' => 0,
                'urlslug' => 'test', 'apptypeid' => 0, 'completionexternal' => 1]],
            'All data with no description' => [(object) ['name' => 'Test App', 'course' => 0,
                'urlslug' => 'test', 'apptypeid' => 0, 'completionexternal' => 1]],
            'All data with no urlslug' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'course' => 0,
                'apptypeid' => 0, 'completionexternal' => 1]],
            'All data with no completionexternal' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'course' => 0,
                'urlslug' => 'test', 'apptypeid' => 0]],
        ];
    }

    /**
     * Provide bad data to create valid applaunch instances.
     *
     * @return object[][]
     */
    public function bad_instance_data_provider(): array {
        return [
            'All data with no course' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'urlslug' => 'test',
                'apptypeid' => 0, 'completionexternal' => 1]],
            'All data with no name' => [(object) ['description' => 'Test description', 'course' => 0,
                'urlslug' => 'test', 'apptypeid' => 0, 'completionexternal' => 1]],
            'All data with no apptypeid' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'course' => 0,
                'urlslug' => 'test', 'completionexternal' => 1]],
            'All data with apptypeid that doesnt exist' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'course' => 0,
                'urlslug' => 'test', 'apptypeid' => 12, 'completionexternal' => 1]],
        ];
    }
}
