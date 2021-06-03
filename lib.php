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

use mod_applaunch\completion;

defined('MOODLE_INTERNAL') || die();

/**
 * Create an applaunch instance.
 *
 * @param $applaunch
 * @return int Instance id.
 */
function applaunch_add_instance($applaunch) {
    $applaunch = \mod_applaunch\applaunch::process_mod_form_data($applaunch);
    $applaunchinstance = new mod_applaunch\applaunch(0, $applaunch);
    $applaunchinstance->save();
    return $applaunchinstance->get('id');
}

/**
 * Update an applaunch instance.
 *
 * @param $applaunch
 * @return bool True on success.
 */
function applaunch_update_instance($applaunch) {
    $applaunch = \mod_applaunch\applaunch::process_mod_form_data($applaunch);
    $applaunchinstance = new mod_applaunch\applaunch($applaunch->id, $applaunch);
    $applaunchinstance->save();
    return true; // If instance is not able to be updated, an exception will be thrown.
}

/**
 * Delete an applaunch instance.
 *
 * @param string $id ID of applaunch instance.
 * @return bool True on success.
 */
function applaunch_delete_instance($id) {
    $applaunchinstance = new mod_applaunch\applaunch($id);
    return $applaunchinstance->delete();
}

/**
 * Indicates API features that the activity supports.
 *
 * @param string $feature
 * @return mixed True if yes (some features may use other values)
 */
function applaunch_supports($feature) {
    switch($feature) {
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GROUPINGS:
        case FEATURE_GROUPS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_MOD_INTRO:
        case FEATURE_PLAGIARISM:
        case FEATURE_RATE:
        case FEATURE_SHOW_DESCRIPTION:
            return false;

        default:
            return null;
    }
}

/**
 * Custom load for course module info.
 *
 * @param $cm
 * @return cached_cm_info
= */
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

/**
 * Obtains the automatic completion state for this module based on any conditions
 * in settings.
 *
 * @param object $course Course
 * @param object $cm Course-module
 * @param int $userid User ID
 * @param bool $type Type of comparison (or/and; can be used as return value if no conditions)
 * @return bool True if completed, false if not, $type if conditions not set.
 */
function applaunch_get_completion_state($course, $cm, $userid, $type) {
    $applaunch = new \mod_applaunch\applaunch($cm->instance);

    // Check if custom completion is enabled for activity.
    if (empty($applaunch->get('completionexternal'))) {
        return $type;
    }

    // Check completion for user.
    $completion = completion::get_by_userid_and_cmid($userid, $cm->id);
    return !empty($completion->get('state'));
}
