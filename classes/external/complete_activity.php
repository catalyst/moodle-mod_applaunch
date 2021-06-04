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
 * External function to set an activity to be complete if completion is enabled.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch\external;

use mod_applaunch\applaunch;
use mod_applaunch\completion;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');

class complete_activity extends \external_api {

    /**
     * External function parameters.
     *
     * @return \external_function_parameters
     */
    public static function execute_parameters(): \external_function_parameters {
        return new \external_function_parameters([
            'activityslug' => new \external_value(PARAM_RAW, get_string('external:activityslug', 'applaunch'),
                    VALUE_REQUIRED, null, NULL_NOT_ALLOWED),
        ]);
    }

    /**
     * Execute the external function.
     *
     * @param $slug
     * @return array
     */
    public static function execute($slug): array {
        global $USER;
        $result = ['success' => false];

        ['activityslug' => $slug] = self::validate_parameters(self::execute_parameters(), ['activityslug' => $slug]);

        // Get activity and course data from activity slug.
        $url = new \moodle_url($slug);
        $cmid = $url->get_param('id');
        list($course, $cm) = get_course_and_cm_from_cmid($cmid);
        $applaunch = new applaunch($cm->instance);
        $completioninfo = new \completion_info($course);

        self::validate_context(\context_module::instance($cmid));

        // Check that completion is enabled for the site.
        if (!\completion_info::is_enabled_for_site()) {
            throw new \moodle_exception('error:sitecompletionnotenabled', 'applaunch');
        }

        // Check that completion is enabled for the course.
        if ($completioninfo->is_enabled($cm) === COMPLETION_TRACKING_NONE) {
            throw new \moodle_exception('error:activitycompletionnotenabled', 'applaunch');
        }

        // Check external completion is enabled.
        if (!$applaunch->is_external_completion_enabled()) {
            throw new \moodle_exception('error:externalcompletionnotenabled', 'applaunch');
        }

        // Get completion info for course and set activity to complete.
        $completion = completion::get_by_userid_and_cmid($USER->id, $cm->id);
        $completion->set('state', COMPLETION_COMPLETE);
        $completion->save();
        $completioninfo->update_state($cm, COMPLETION_COMPLETE); // Trigger update in core completion.

        // Check that the state was updated successfully.
        $cmcompletiondata = $completioninfo->get_data($cm);
        if ($cmcompletiondata->completionstate == COMPLETION_COMPLETE) {
            $result['success'] = true;
        }

        return $result;
    }

    /**
     * External function return arguments.
     *
     * @return \external_single_structure
     */
    public static function execute_returns() {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL, get_string('external:success', 'applaunch'),
                    VALUE_REQUIRED, false, NULL_NOT_ALLOWED),
        ]);
    }
}

