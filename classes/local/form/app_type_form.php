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
 * Define the form to manage app types.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch\local\form;

use core\form\persistent;

defined('MOODLE_INTERNAL') || die();

class app_type_form extends persistent {

    protected static $persistentclass = 'mod_applaunch\\app_type';

    /**
     * Define the form elements
     */
    protected function definition() {

        $mform    =& $this->_form;

        $mform->addElement('header', 'setup', get_string('form:app_type:header', 'applaunch'));

        $mform->addElement('text', 'name', get_string('form:app_type:name', 'applaunch'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('textarea', 'description', get_string('form:app_type:description', 'applaunch'), array('rows' => 4, 'cols' => 60));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('text', 'url', get_string('form:app_type:url', 'applaunch'), array('size' => '64'));
        $mform->setType('url', PARAM_RAW);
        $mform->addHelpButton('url', 'form:app_type:url', 'applaunch');
        $mform->addRule('url', null, 'required', null, 'client');

        $mform->addElement('checkbox', 'enabled', get_string('form:app_type:enabled', 'applaunch'));
        $mform->setDefault('checkbox', 1);

        $this->add_action_buttons();
    }
}
