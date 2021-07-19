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
 * Represent an external application.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

defined('MOODLE_INTERNAL') || die();

class app_type extends \core\persistent {

    /** @var string TABLE Moodle DB table storing instances of the class. */
    const TABLE = 'mod_applaunch_app_types';

    /**
     * Define the properties of the persistent object.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'name' => [
                'type' => PARAM_TEXT,
            ],
            'description' => [
                'type' => PARAM_TEXT,
                'default' => '',
            ],
            'url' => [
                'type' => PARAM_RAW, // PARAM_URL doesn't allow custom schemas.
            ],
            'icon' => [
                'type' => PARAM_TEXT, // File name.
                'null' => NULL_ALLOWED,
                'default' => null,
            ],
            'enabled' => [
                'type' => PARAM_INT,
                'default' => 1,
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
        // Trim the url and icon.
        $url = trim($this->get('url'));
        $this->set('url', $url);

        $icon = trim($this->get('icon'));
        $this->set('icon', $icon);
    }

    /**
     * Check if the app_type can be deleted. Prevent app types being deleted if they are used by any activities.
     *
     * @return bool True if can delete.
     */
    public function can_delete(): bool {
        // Check if the app_type is currently in use by any activities.
        $applaunchinstances = applaunch::get_records(['apptypeid' => $this->get('id')]);
        if (empty($applaunchinstances)) {
            return true;
        }
        return false;
    }

    /**
     * Get the HTML for the icon.
     *
     * @return string HTML
     */
    public function get_icon_html(): string {
        global $OUTPUT;
        $default = $OUTPUT->pix_icon('icon', $this->get('name') . ' icon', 'applaunch');

        if (empty($this->get('icon'))) {
            return $default;
        }

        return \html_writer::empty_tag('img', array('src' => $this->get('icon'), 'alt' => $this->get('name') . ' icon'));
    }

    /**
     * Get the url for the icon for this app type.
     *
     * @return null|\moodle_url Returns null if no custom URL found.
     */
    public function get_icon_url(): ?\moodle_url {

        if (empty($this->get('icon'))) {
            return null;
        }

        // Try and return URL as a moodle_url. If not possible, return null with a debugging message.
        try {
            return new \moodle_url($this->get('icon'));
        } catch (\moodle_exception $e) {
            debugging($e->getMessage(), DEBUG_DEVELOPER, $e->getTrace());
            return null;
        }
    }
}
