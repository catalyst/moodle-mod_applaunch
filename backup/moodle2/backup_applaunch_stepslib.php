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
 * Define backup steps.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_applaunch\applaunch;

defined('MOODLE_INTERNAL') || die();

class backup_applaunch_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define the structure of the backup file.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {

        $applaunch = new backup_nested_element('applaunch', ['id'], [
            'name',
            'description',
            'urlslug',
            'apptypeid',
            'completionexternal',
            'usermodified',
            'timecreated',
            'timemodified',
        ]);

        $apptype = new backup_nested_element('apptype', ['id'], [
            'name',
            'description',
            'url',
            'enabled',
            'usermodified',
            'timecreated',
            'timemodified',
        ]);

        $completions = new backup_nested_element('completions');
        $completion = new backup_nested_element('completion', ['id'], [
            'userid',
            'cmid',
            'state',
            'usermodified',
            'timecreated',
            'timemodified',
        ]);

        $applaunch->add_child($apptype);
        $applaunch->add_child($completions);
        $completions->add_child($completion);

        // Define sources.
        $applaunchinstance = new applaunch($this->task->get_activityid());
        $applaunch->set_source_array([$applaunchinstance->to_record()]);

        $apptypeinstance = new \mod_applaunch\app_type($applaunchinstance->get('apptypeid'));
        $apptype->set_source_array([$apptypeinstance->to_record()]);

        // If we are including user completion info then save the completion data.
        if ($this->get_setting_value('userscompletion')) {
            $completion->set_source_table('mod_applaunch_completion', ['cmid' => backup::VAR_MODID]);
        }

        // Annotate appropriate data.
        $completion->annotate_ids('user', 'userid');

        return $this->prepare_activity_structure($applaunch);
    }
}
