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
 * Define backup task.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/applaunch/backup/moodle2/backup_applaunch_stepslib.php');

class backup_applaunch_activity_task extends backup_activity_task {

    /**
     * Define particular settings this activity can have.
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define particular steps this activity can have.
     */
    protected function define_my_steps() {
        $this->add_step(new backup_applaunch_activity_structure_step('applaunch_structure', 'applaunch.xml'));
    }

    /**
     * Code the transformations to perform in the activity in order to get transportable (encoded) links.
     *
     * @param string $content
     * @return mixed|string
     */
    public static function encode_content_links($content) {
        if (!self::has_scripts_in_content($content, 'mod/applaunch', ['index.php', 'view.php'])) {
            // No scripts present in the content, simply continue.
            return $content;
        }

        if (empty($task)) {
            // No task has been provided, lets just encode everything, must be some old school backup code.
            $content = self::encode_content_link_basic_id($content, "/mod/applaunch/index.php?id=", 'APPLAUNCHINDEX');
            $content = self::encode_content_link_basic_id($content, "/mod/applaunch/view.php?id=", 'APPLAUNCHVIEWBYID');
        } else {
            // OK we have a valid task, we can translate just those links belonging to content that is being backed up.
            $content = self::encode_content_link_basic_id($content, "/mod/applaunch/index.php?id=",
                    'APPLAUNCHINDEX', $task->get_courseid());
            foreach ($task->get_tasks_of_type_in_plan('backup_applaunch_activity_task') as $task) {
                $content = self::encode_content_link_basic_id($content, "/mod/applaunch/view.php?id=",
                        'APPLAUNCHVIEWBYID', $task->get_moduleid());
            }
        }

        return $content;
    }
}
