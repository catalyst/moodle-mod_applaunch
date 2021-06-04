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
 * Page that launches the application.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/sessionlib.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

$cmid = required_param('id', PARAM_INT); // Course Module ID.
$sesskey = required_param('sesskey', PARAM_TEXT); // Course Module ID.

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'applaunch');
$appinstance = new \mod_applaunch\applaunch($cm->instance);
$apptype = new \mod_applaunch\app_type($appinstance->get('apptypeid'));

require_login($course, false);

$PAGE->set_url(new moodle_url('/mod/applaunch/launch.php', ['id' => $cmid]));

// If the user attempted to access this page directly.
if (confirm_sesskey($sesskey)) {
    // Attempt to launch the application.
    $token = \mod_applaunch\ws_token::generate_user_key($cmid, $USER->id);
    header("Location: " . $appinstance->get_url($token));
} else {
    throw new moodle_exception('error:launchdirectaccess', 'applaunch');
}
