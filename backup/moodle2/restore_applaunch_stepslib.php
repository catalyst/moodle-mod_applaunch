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
 * Define steps to restore plugin activity.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\persistent;
use mod_applaunch\app_type;

defined('MOODLE_INTERNAL') || die();

class restore_applaunch_activity_structure_step extends restore_activity_structure_step {

    /** @var string[] Default fields generated internally by persistent objects. */
    const PERSISTENT_DEFAULT_FIELDS = ['id', 'usermodified', 'timecreated', 'timemodified'];

    /**
     * Define the data structure to be restored.
     *
     * @return mixed
     * @throws base_step_exception
     */
    protected function define_structure() {
        $paths = [];
        // To know if we are including user completion info.
        $userscompletion = $this->get_setting_value('userscompletion');

        $applaunch = new restore_path_element('applaunch', '/activity/applaunch');
        $paths[] = $applaunch;
        $paths[] = new restore_path_element('apptype', '/activity/applaunch/apptype');
        if ($userscompletion) {
            $paths[] = new restore_path_element('completion', '/activity/applaunch/completions/completion');
        }

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process the main activity instance.
     *
     * @param $data
     * @throws coding_exception
     */
    protected function process_applaunch($data) {
        $data = (object) $data;
        $data->apptypeid = 0; // Set the app type id to empty. It will be updated once the apptype is processed.
        $data = $this->filter_default_persistent_fields($data);
        $newinstance = new \mod_applaunch\applaunch(0, $data);
        $newinstance->save();
        $this->apply_activity_instance($newinstance->get('id'));
    }

    /**
     * Process the app type.
     *
     * @param $data
     * @throws coding_exception
     */
    protected function process_apptype($data) {
        $data = (object) $data;
        $apptype = $this->get_existing_app_type($data);
        if (empty($apptype)) {
            // No existing type found, so create a new one.
            $data = $this->filter_default_persistent_fields($data);
            $apptype = new app_type(0, $data);
            $apptype->save();
        }
        // Update applaunch instance.
        $applaunch = new \mod_applaunch\applaunch($this->get_new_parentid('applaunch'));
        $applaunch->set('apptypeid', $apptype->get('id'));
        $applaunch->save();
    }

    /**
     * Process the user completion.
     *
     * @param $data
     */
    protected function process_completion($data) {
        $data = (object) $data;

        // Get the new user id and cmid.
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->cmid = $this->task->get_moduleid();

        // Create new completion record.
        $data = $this->filter_default_persistent_fields($data);
        $newcompletion = new \mod_applaunch\completion(0, $data);
        $newcompletion->save();
    }

    /**
     * Get the existing app type if it exists.
     *
     * @param $data
     * @return persistent|null
     * @throws restore_step_exception
     */
    protected function get_existing_app_type($data): ?persistent {
        // Check if the app type is already mapped.
        if ($apptypeid = $this->get_mappingid('apptype', $data->id)) {
            return new app_type($apptypeid);
        }

        // If same site, try and find app type by id.
        $apptype = app_type::get_record(['id' => $data->id]);
        if ($this->task->is_samesite() && $apptype !== false) {
            // If found, map it.
            $this->set_mapping('apptype', $data->id, $data->id);
            return $apptype;
        }

        // Try and find an existing app type matching name, description and url. If found, we assume it's the same app type.
        $apptype = app_type::get_record([
            'name' => $data->name,
            'description' => $data->description,
            'url' => $data->url,
        ]);
        if ($apptype !== false) {
            return $apptype;
        }

        return null;
    }

    /**
     * Remove the persistent object fields that are not expected when instantiating a new instance.
     *
     * @param stdClass $data
     * @return stdClass
     */
    protected function filter_default_persistent_fields(stdClass $data): stdClass {
        foreach ($data as $key => $value) {
            if (in_array($key, self::PERSISTENT_DEFAULT_FIELDS)) {
                unset($data->$key);
            }
        }
        return $data;
    }
}
