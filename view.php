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
 * Default page for activity. This script is used to launch the app.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/sessionlib.php');

global $CFG, $DB, $OUTPUT, $PAGE;

$cmid = required_param('id', PARAM_INT);    // Course Module ID

list ($course, $cm) = get_course_and_cm_from_cmid($cmid, 'applaunch');
$appinstance = new \mod_applaunch\applaunch($cm->instance);

require_login($course, false);

$PAGE->set_url(new moodle_url('/mod/applaunch/view.php', ['id' => $cmid]));

$html = '';
$html .= $OUTPUT->header();
$html .= $OUTPUT->render_from_template('mod_applaunch/view', [
    'modname' => $appinstance->get('name'),
    'url' => (new moodle_url('/mod/applaunch/launch.php'))->out(),
    'urlparams' => [
        [
            'name' => 'id',
            'value' => $cmid,
        ],
        [
            'name' => 'sesskey',
            'value' => sesskey(),
        ],
    ]
]);
$html .= $OUTPUT->footer();
echo $html;
