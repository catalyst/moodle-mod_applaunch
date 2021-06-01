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
 * Display list of applaunch instances in course.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/applaunch/classes/local/table/applaunch_list.php');

$id = required_param('id', PARAM_INT); // Course ID.

$PAGE->set_url(new moodle_url('/mod/applaunch/index.php', ['id' => $id]));

// Ensure that the course specified is valid.
if (!$course = $DB->get_record('course', array('id' => $id))) {
    throw new moodle_exception('invalidcourseid');
}

require_course_login($course);

$modinfo = get_fast_modinfo($course);
$applaunchinstances = [];
foreach ($modinfo->get_instances_of('applaunch') as $instanceid => $cm) {
    if (!$cm->uservisible) {
        continue;
    }
    $applaunchinstances[] = new \mod_applaunch\applaunch($instanceid);
}

$table = new \mod_applaunch\local\table\applaunch_list();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'applaunch'));
$table->display($applaunchinstances);
echo $OUTPUT->footer();

