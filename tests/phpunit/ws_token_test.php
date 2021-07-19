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
 * Test the ws_token class.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_applaunch\ws_token;

defined('MOODLE_INTERNAL') || die();

class mod_applaunch_external_ws_token_testcase extends advanced_testcase {

    /**
     * Run before every test.
     */
    protected function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test that a user key value is generated.
     */
    public function test_generate_user_key() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $value = ws_token::generate_user_key($applaunch->cmid, $user->id);
        $this->assertNotEmpty($value);
    }

    /**
     * Test that a user key value is not generated with bad cmid.
     */
    public function test_generate_user_key_with_invalid_cmid() {
        $user = $this->getDataGenerator()->create_user();
        $this->expectException(dml_exception::class);
        ws_token::generate_user_key('test', $user->id);
    }

    /**
     * Test that a user key value is not generated with bad userid.
     */
    public function test_generate_user_key_with_invalid_userid() {
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $this->expectException(dml_exception::class);
        ws_token::generate_user_key($applaunch->cmid, 'test');
    }

    /**
     * Test getting user key data from a value.
     */
    public function test_get_user_key() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $value = ws_token::generate_user_key($applaunch->cmid, $user->id);
        $userkey = ws_token::get_user_key($value);
        $this->assertInstanceOf(\mod_applaunch\user_key::class, $userkey);
        $this->assertEquals('mod_applaunch', $userkey->get_script());
        $this->assertEquals($user->id, $userkey->get_userid());
        $this->assertEquals($applaunch->cmid, $userkey->get_instance());
        $this->assertNull($userkey->get_iprestriction());
        $this->assertEquals($value, $userkey->get_value());
    }

    /**
     * Test not getting user key data from a bad value.
     */
    public function test_get_user_key_not_exists() {
        $this->expectException(dml_exception::class);
        $userkey = ws_token::get_user_key('test');
    }

    /**
     * Test user key is valid for external service.
     */
    public function test_is_user_key_valid() {
        $userkey = new \mod_applaunch\user_key((object) [
                'id' => 123,
                'script' => 'mod_applaunch',
                'value' => 'abc123',
                'userid' => 123,
                'instance' => 123,
                'iprestriction' => null,
                'validuntil' => time() + 3600, // Valid for an hour.
                'timecreated' => time(),
            ]);
        $valid = ws_token::is_user_key_valid($userkey);
        $this->assertTrue($valid);
    }

    /**
     * Test user key is not valid for external service due to incorrect script.
     */
    public function test_is_user_key_valid_with_incorrect_script() {
        $userkey = new \mod_applaunch\user_key((object) [
            'id' => 123,
            'script' => 'test',
            'value' => 'abc123',
            'userid' => 123,
            'instance' => 123,
            'iprestriction' => null,
            'validuntil' => time() + 3600, // Valid for an hour.
            'timecreated' => time(),
        ]);
        $valid = ws_token::is_user_key_valid($userkey);
        $this->assertFalse($valid);
    }

    /**
     * Test user key is not valid for external service due to it expiring.
     */
    public function test_is_user_key_valid_with_expired_timeuntil() {
        $userkey = new \mod_applaunch\user_key((object) [
            'id' => 123,
            'script' => 'mod_applaunch',
            'value' => 'abc123',
            'userid' => 123,
            'instance' => 123,
            'iprestriction' => null,
            'validuntil' => time() - 3600, // Expired.
            'timecreated' => time(),
        ]);
        $valid = ws_token::is_user_key_valid($userkey);
        $this->assertFalse($valid);
    }

    /**
     * Test user key is not valid for external service due to no expiry being set.
     */
    public function test_is_user_key_valid_with_empty_timeuntil() {
        $userkey = new \mod_applaunch\user_key((object) [
            'id' => 123,
            'script' => 'mod_applaunch',
            'value' => 'abc123',
            'userid' => 123,
            'instance' => 123,
            'iprestriction' => null,
            'validuntil' => 0,
            'timecreated' => time(),
        ]);
        $valid = ws_token::is_user_key_valid($userkey);
        $this->assertFalse($valid);
    }

    /**
     * Test deleting a user key.
     */
    public function test_delete_user_key() {
        global $DB;
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $value = ws_token::generate_user_key($applaunch->cmid, $user->id);
        $key = $DB->get_record('user_private_key', ['value' => $value]);
        $this->assertNotFalse($key);
        ws_token::delete_user_key(ws_token::get_user_key($value));
        $key = $DB->get_record('user_private_key', ['value' => $value]);
        $this->assertFalse($key);
    }

    /**
     * Test generating an external token using details from user key.
     */
    public function test_generate_ws_token() {
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $applaunch = $this->getDataGenerator()->create_module('applaunch', [
            'course' => $course->id,
            'completion' => COMPLETION_TRACKING_AUTOMATIC,
            'completionview' => COMPLETION_VIEW_NOT_REQUIRED,
        ]);
        $value = ws_token::generate_user_key($applaunch->cmid, $user->id);
        $userkey = ws_token::get_user_key($value);
        $wstoken = ws_token::generate_ws_token($userkey);
        $this->assertNotEmpty($wstoken);
    }
}
