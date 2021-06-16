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
 * Upgrade steps.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade this activity.
 * @param int $oldversion The old version of the plugin
 * @return bool
 */
function xmldb_applaunch_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021061000) {

        // Add an 'icon' field to the 'mod_applaunch_app_types' table.
        $table = new xmldb_table('mod_applaunch_app_types');
        $field = new xmldb_field('icon', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'url');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Applaunch savepoint reached.
        upgrade_mod_savepoint(true, 2021061000, 'applaunch');
    }

    if ($oldversion < 2021061500) {

        // Add an 'course' field to the 'applaunch' table.
        $table = new xmldb_table('applaunch');
        $field = new xmldb_field('course', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'id');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Set the course field for existing applaunch instances.
        $applaunchinstances = \mod_applaunch\applaunch::get_records();
        foreach ($applaunchinstances as $applaunch) {
            $cm = $applaunch->get_cm();
            $applaunch->set('course', $cm->course);
            $applaunch->save();
        }

        // Applaunch savepoint reached.
        upgrade_mod_savepoint(true, 2021061500, 'applaunch');
    }

    return true;
}
