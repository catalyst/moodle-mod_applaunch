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

defined('MOODLE_INTERNAL') || die();

class applaunch extends \core\persistent {

    const TABLE = 'mod_applaunch';

    protected static function define_properties() {
        return [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'description' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'urlslug' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'apptypeid' => [
                'type' => PARAM_INT,
            ],
        ];
    }

    protected function before_create() {
        $this->before_save();
    }

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

    public function get_url(): string {
        $apptype = new app_type($this->get('apptypeid'));
        return $apptype->get('url') . $this->get('urlslug');
    }

    /**
     * The mod form data includes standard fields that can't be passed to persistent object. Filter out these extra fields.
     *
     * // TODO: As we expect all fields from form, do we handle missing fields or just let it break?
     *
     * @param \stdClass $formdata
     */
    public static function process_mod_form_data(\stdClass $formdata): \stdClass {
        $defaultprops = ['id', 'usermodified', 'timecreated', 'timemodified'];
        $filtereddata = new \stdClass();
        $properties = self::properties_definition();
        foreach ($properties as $key => $property) {
            if (!in_array($key, $defaultprops)) {
                $filtereddata->$key = $formdata->$key;
            }
        }
        return $filtereddata;
    }
}
