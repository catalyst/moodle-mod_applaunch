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
 * Admin settings for plugin.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('modsettings', new admin_category('modapplaunchsettings', new lang_string('pluginname', 'applaunch')));
$ADMIN->add('modapplaunchsettings', $settings);
$settings = null; // Tell core we have managed the settings pages ourselves.

if (has_capability('mod/applaunch:manageapptypes', context_system::instance())) {
    $ADMIN->add('modapplaunchsettings',
        new admin_externalpage(
            'mod_applaunch/app_type',
            get_string('setting:manage_app_types', 'applaunch'),
            new moodle_url('/mod/applaunch/app_type.php', ['action' => 'view']),
            'mod/applaunch:manageapptypes'
        )
    );
}
