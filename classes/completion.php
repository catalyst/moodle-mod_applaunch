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
 * Track completion of activity based on user and activity.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

class completion extends \core\persistent {

    const TABLE = 'mod_applaunch_completion';

    protected static function define_properties() {
        return [
            'userid' => ['type' => PARAM_INT],
            'cmid' => ['type' => PARAM_INT],
            'state' => [
                'type' => PARAM_INT,
                'default' => COMPLETION_INCOMPLETE,
            ],
        ];
    }

    /**
     * Validate that the user exists.
     *
     * @param $value
     * @return bool|\lang_string
     */
    protected function validate_userid($value) {
        $user = \core_user::get_user($value);
        if ($user === false) {
            return new \lang_string('error:usernotexists', 'applaunch');
        }
        return true;
    }

    /**
     * Validate that the course module exists.
     *
     * @param $value
     * @return bool|\lang_string
     */
    protected function validate_cmid($value) {
        global $DB;
        $cm = $DB->get_record('course_modules', ['id' => $value]);
        if ($cm === false) {
            return new \lang_string('error:cmnotexists', 'applaunch');
        }
        return true;
    }

    /**
     * Validate that it is a valid completion state.
     *
     * @param $value
     * @return bool|\lang_string
     */
    protected function validate_state($value) {
        $validstates = [
            COMPLETION_INCOMPLETE,
            COMPLETION_COMPLETE,
            COMPLETION_COMPLETE_PASS,
            COMPLETION_COMPLETE_FAIL,
        ];
        if (!in_array($value, $validstates)) {
            return new \lang_string('error:invalidcompletionstate', 'applaunch');
        }
        return true;
    }

    /**
     * Get user object related to completion.
     *
     * @return \stdClass
     */
    public function get_user(): \stdClass {
        return \core_user::get_user($this->get('userid'));
    }

    /**
     * Get course module object related to completion.
     *
     * @return \stdClass
     */
    public function get_cm(): \stdClass {
        global $DB;
        return $DB->get_record('course_modules', ['id' => $this->get('cmid')]);
    }

    /**
     * Get the applaunch instance related to completion.
     *
     * @return applaunch
     */
    public function get_applaunch(): applaunch {
        global $DB;
        $cm = $DB->get_record('course_modules', ['id' => $this->get('cmid')]);
        return new applaunch($cm->instance);
    }

    /**
     * Get instance based on userid and cmid. If it doesn't exist, create it.
     *
     * @param string $userid
     * @param string $cmid
     * @return \core\persistent|false|completion
     */
    public static function get_by_userid_and_cmid(string $userid, string $cmid) {
        $completion = self::get_record(['userid' => $userid, 'cmid' => $cmid]);
        // Create the instance.
        if ($completion === false) {
            $completion = new completion(0, (object) ['userid' => $userid, 'cmid' => $cmid]);
            $completion->save();
        }
        return $completion;
    }
}
