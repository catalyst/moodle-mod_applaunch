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
 * Main function file. Mostly contains callbacks.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function applaunch_add_instance($applaunch) {
    $applaunch = \mod_applaunch\applaunch::process_mod_form_data($applaunch);
    $applaunchinstance = new mod_applaunch\applaunch(0, $applaunch);
    $applaunchinstance->save();
    return $applaunchinstance->get('id');
}

function applaunch_update_instance($applaunch) {
    $applaunch = \mod_applaunch\applaunch::process_mod_form_data($applaunch);
    $applaunchinstance = new mod_applaunch\applaunch($applaunch->id, $applaunch);
    $applaunchinstance->save();
    return true; // If instance is not able to be updated, an exception will be thrown.
}

function applaunch_delete_instance($id) {
    $applaunchinstance = new mod_applaunch\applaunch($id);
    return $applaunchinstance->delete();
}

function applaunch_get_coursemodule_info($cm): cached_cm_info {
    $applaunchinstance = new mod_applaunch\applaunch($cm->instance);
    $apptype = new \mod_applaunch\app_type($applaunchinstance->get('apptypeid'));

    // Create cm cache object.
    $cminfo = new cached_cm_info();
    $cminfo->name = $applaunchinstance->get('name');
    $cminfo->description = $applaunchinstance->get('description');
    $cminfo->urlslug = $applaunchinstance->get('urlslug');
    $cminfo->apptype = $apptype->to_record(); // Return the actual app type data, instead of only id.

    return $cminfo;
}

function mod_applaunch_get_shortcuts($defaultitem) {
    // TODO: Implement get shortcuts.
}
