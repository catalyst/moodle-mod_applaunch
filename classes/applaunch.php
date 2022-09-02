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
 * Represent an instance of the activity.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

class applaunch extends \core\persistent {

    const TABLE = 'applaunch';

    const MODULE_NAME = 'applaunch';

    protected static function define_properties() {
        return [
            'name' => ['type' => PARAM_TEXT],
            'description' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'course' => ['type' => PARAM_INT],
            'urlslug' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'apptypeid' => ['type' => PARAM_INT],
            'completionexternal' => [
                'type' => PARAM_BOOL,
                'default' => true,
            ],
        ];
    }

    /**
     * Validate the apptypeid.
     *
     * @return bool|\lang_string
     * @throws \coding_exception
     */
    protected function validate_apptypeid() {
        // Check that the app type exists and is enabled.
        $apptype = app_type::get_record(['id' => $this->get('apptypeid'), 'enabled' => 1]);
        if (empty($apptype)) {
            return new \lang_string('error:apptypenotexists', 'applaunch');
        }
        return true;
    }

    /**
     * Called before instance is created.
     */
    protected function before_create() {
        $this->before_save();
    }

    /**
     * Called before instance is updated.
     */
    protected function before_update() {
        $this->before_save();
    }

    /**
     * Execute before a save. Must be called in before_create and before_update hooks.
     */
    protected function before_save() {
        // Trim the urlslug.
        $urlslug = trim($this->get('urlslug'));
        $this->set('urlslug', $urlslug);
    }

    /**
     * Get the launch url.
     *
     * @param string $token User private key value to use a temporary token.
     * @return string
     */
    public function get_url(string $token): string {
        global $CFG;
        $apptype = new app_type($this->get('apptypeid'));
        $urlstring = $apptype->get('url') . $this->get('urlslug');
        $url  = new \moodle_url($urlstring);
        $url->params([
            'token' => $token,
            'baseuri' => $CFG->wwwroot,
        ]);
        return $url->out(false);
    }

    /**
     * The mod form data includes standard fields that can't be passed to persistent object. Filter out these extra fields.
     *
     * @param \stdClass $formdata
     */
    public static function process_mod_form_data(\stdClass $formdata): \stdClass {
        $defaultprops = ['id', 'usermodified', 'timecreated', 'timemodified'];
        $filtereddata = new \stdClass();
        $properties = self::properties_definition();
        foreach ($properties as $key => $property) {
            if (!in_array($key, $defaultprops) && isset($formdata->$key)) {
                $filtereddata->$key = $formdata->$key;
            }
        }
        // Add instance if provided to identify applaunch instance.
        if (!empty($formdata->instance)) {
            $filtereddata->id = $formdata->instance;
        }
        return $filtereddata;
    }

    /**
     * Get the cm object corresponding to this instance.
     *
     * @return \stdClass
     */
    public function get_cm(): \stdClass {
        global $DB;
        return $DB->get_record_sql("
                SELECT cm.*
                  FROM {course_modules} cm
                  JOIN {modules} m on cm.module = m.id
                 WHERE cm.instance = :id
                   AND m.name = :modulename
                ",
                ['id' => $this->get('id'), 'modulename' => 'applaunch'], MUST_EXIST);
    }

    /**
     * Get the url for the icon for this instance's app type.
     *
     * @return null|\moodle_url
     */
    public function get_icon_url(): ?\moodle_url {
        $apptype = app_type::get_record(['id' => $this->get('apptypeid')]);
        if ($apptype === false) {
            return null; // This should not be possible when creating activity in the UI.
        }
        return $apptype->get_icon_url();
    }

    /**
     * Check if external completion is enabled.
     *
     * @return bool True if enabled.
     */
    public function is_external_completion_enabled(): bool {
        return !empty($this->get('completionexternal'));
    }

    /**
     * Get an instance of applaunch from a cmid.
     *
     * @param string $cmid Course module id.
     * @return self Instance of applaunch.
     * @throws \dml_exception
     */
    public static function get_by_cmid(string $cmid): self {
        list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'applaunch');
        return new applaunch($cm->instance);
    }
}
