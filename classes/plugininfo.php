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
 * Define the activity plugin info. If not defined here, it will be handled by the activity level class.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

class plugininfo extends \core\plugininfo\mod {

    const SETTINGS_CATEGORY = 'modapplaunchsettings';

    /**
     * Returns the URL of the plugin settings screen
     *
     * Null value means that the plugin either does not have the settings screen
     * or its location is not available via this library.
     *
     * @return null|\moodle_url
     */
    public function get_settings_url() {
        global $CFG;
        require_once($CFG->libdir . '/adminlib.php');

        $settings = admin_get_root()->locate(self::SETTINGS_CATEGORY);
        if ($settings && $settings instanceof \admin_category) {
            return new \moodle_url('/admin/category.php', ['category' => self::SETTINGS_CATEGORY]);
        } else {
            return parent::get_settings_url();
        }
    }
}
