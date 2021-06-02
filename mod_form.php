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
 * Form that define the settings for a new instance of this activity.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_applaunch_mod_form extends moodleform_mod {

    /**
     * Define the moodle form.
     */
    protected function definition() {
        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('form:applaunch:instancename', 'applaunch'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('form:applaunch:description', 'applaunch'),
                array('rows' => 4, 'cols' => 60));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('text', 'urlslug', get_string('form:applaunch:urlslug', 'applaunch'), array('size' => '64'));
        $mform->setType('urlslug', PARAM_TEXT);
        $mform->addHelpButton('urlslug', 'form:applaunch:urlslug', 'applaunch');

        $apptypeoptions = $this->get_app_type_options();
        $mform->addElement('select', 'apptypeid', get_string('form:applaunch:apptype', 'applaunch'),
                $apptypeoptions);
        $mform->setType('apptypeid', PARAM_INT);
        $mform->addRule('apptypeid', null, 'required', null, 'client');

        $this->standard_coursemodule_elements();
        $this->apply_admin_defaults();

        $this->add_action_buttons();
    }

    /**
     * Get
     *
     * @return array
     */
    private function get_app_type_options(): array {
        $apptypeoptions = [0 => get_string('form:applaunch:defaultapptype', 'applaunch')]; // Add default.
        $apptypes = \mod_applaunch\app_type::get_records(['enabled' => 1]);
        foreach ($apptypes as $apptype) {
            $apptypeoptions[$apptype->get('id')] = $apptype->get('name');
        }
        return $apptypeoptions;
    }
}
