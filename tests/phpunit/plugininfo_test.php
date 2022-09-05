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
 * Test the plugininfo class.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

class plugininfo_test extends \advanced_testcase {

    /**
     * This method runs before every test.
     */
    public function setUp(): void {
        $this->resetAfterTest();
    }

    /**
     * Test get_settings_url returns a link to the category page.
     */
    public function test_category_settings_url_is_returned() {
        $this->setAdminUser();
        $plugininfo = new \mod_applaunch\plugininfo();
        $url = $plugininfo->get_settings_url();
        $expected = new \moodle_url('/admin/category.php', ['category' => 'modapplaunchsettings']);
        $this->assertEquals($expected->out(), $url->out());
    }
}
