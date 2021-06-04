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
 * Implement the custom completion API for plugin.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch\completion;

use mod_applaunch\applaunch;
use mod_applaunch\completion;

defined('MOODLE_INTERNAL') || die();

if (!class_exists('\core_completion\activity_custom_completion')) {
    // New API does not exist in this site, so do nothing.
    return;
}

class custom_completion extends \core_completion\activity_custom_completion {

    const CUSTOM_COMPLETION_EXTERNAL = 'completionexternal';

    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {

        $this->validate_rule($rule);

        switch ($rule) {
            case self::CUSTOM_COMPLETION_EXTERNAL:
                $status = $this->calculate_external_completion_state();
                break;
            default:
                $status = false;
        }

        return $status ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [self::CUSTOM_COMPLETION_EXTERNAL];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [self::CUSTOM_COMPLETION_EXTERNAL => get_string('form:applaunch:completionexternal', 'applaunch')];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            self::CUSTOM_COMPLETION_EXTERNAL,
            'completionview',
            'completiontimespent',
        ];
    }

    /**
     * Check whether user has completed activity via external app.
     *
     * @return bool
     * @throws \coding_exception
     */
    private function calculate_external_completion_state(): bool {
        $applaunch = new applaunch($this->cm->instance);

        // Check if custom completion is enabled for activity.
        if (empty($applaunch->get('completionexternal'))) {
            return false;
        }

        // Check completion for user.
        $completion = completion::get_by_userid_and_cmid($this->userid, $this->cm->id);
        return !empty($completion->get('state'));
    }
}
