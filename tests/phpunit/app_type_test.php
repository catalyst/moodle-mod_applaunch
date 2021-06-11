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
 * Test the app_type persistent clase.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_applaunch_app_type_testcase extends advanced_testcase {

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * @dataProvider instance_data_provider
     */
    public function test_create_instance($data) {
        global $DB;
        $instance = new \mod_applaunch\app_type(0, $data);
        $instance->save();
        $records = $DB->get_records('mod_applaunch_app_types');
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertEquals($data->name, $record->name);
        $this->assertEquals($data->url, $record->url);
    }

    public function test_input_are_trimmed() {
        global $DB;
        $instance = new \mod_applaunch\app_type(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'url' => 'fake://test.com   ',
            'icon' => '  https://icon.com ',
            'enabled' => 1
        ]);
        $instance->save();
        $records = $DB->get_records('mod_applaunch_app_types');
        $this->assertCount(1, $records);
        $record = reset($records);
        $this->assertEquals('fake://test.com', $record->url);
        $this->assertEquals('https://icon.com', $record->icon);
    }

    public function test_can_delete() {
        $instance = new \mod_applaunch\app_type(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'url' => 'fake://test.com',
            'icon' => 'https://icon.com',
            'enabled' => 1
        ]);
        $instance->save();
        $this->assertTrue($instance->can_delete());
    }

    public function test_cannot_delete() {
        $instance = new \mod_applaunch\app_type(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'url' => 'fake://test.com',
            'icon' => 'https://icon.com',
            'enabled' => 1
        ]);
        $instance->save();
        // Set up activity relying on app type.
        $applaunch = new \mod_applaunch\applaunch(0, (object) ['name' => 'Test Activity', 'apptypeid' => $instance->get('id')]);
        $applaunch->save();
        $this->assertFalse($instance->can_delete());
    }

    public function test_get_default_icon_html() {
        global $OUTPUT;
        $instance = new \mod_applaunch\app_type(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'url' => 'fake://test.com',
            'enabled' => 1
        ]);
        $expected = $OUTPUT->pix_icon('icon', $instance->get('name') . ' icon', 'applaunch');
        $this->assertEquals($expected, $instance->get_icon_html());
    }

    public function test_get_icon_html() {
        $iconurl = 'https://icon.com';
        $expected = '<img src="' . $iconurl . '" alt="Test App icon" />';
        $instance = new \mod_applaunch\app_type(0, (object) [
            'name' => 'Test App',
            'description' => 'Test description',
            'url' => 'fake://test.com',
            'icon' => $iconurl,
            'enabled' => 1
        ]);
        $this->assertEquals($expected, $instance->get_icon_html());
    }

    public function instance_data_provider(): array {
        return [
            'All data' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'url' => 'fake://test.com',
                    'icon' => 'https://icon.com', 'enabled' => 1]],
            'All data with no description' => [(object) ['name' => 'Test App', 'url' => 'fake://test.com', 'icon' => 'https://icon.com', 'enabled' => 1]],
            'All data with no icon' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'url' => 'fake://test.com', 'enabled' => 1]],
            'All data with no enabled' => [(object) ['name' => 'Test App', 'description' => 'Test description', 'url' => 'fake://test.com',
                    'icon' => 'https://icon.com']],
        ];
    }
}
