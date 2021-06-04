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
 * Handle processing a user private key to obtain a ws token.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_applaunch;

use mod_applaunch\user_key;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->libdir . '/externallib.php');

class ws_token {

    /** @var string Unique identifier to identify user key as generated for plugin. */
    const USER_KEY_SCRIPT = 'mod_applaunch';

    /**
     * Generate a user private key based on plugin and activity.
     *
     * @param string $cmid
     * @param string $userid
     * @return string User key 'value'.
     */
    public static function generate_user_key(string $cmid, string $userid): string {
        // Check that the course module ID is valid using context.
        \context_module::instance($cmid);
        // Check user id is valid.
        \core_user::get_user($userid, 'id', MUST_EXIST);
        $validuntil = time() + 60; // Seconds. Valid for 1 minute.
        return get_user_key(self::USER_KEY_SCRIPT, $userid, $cmid, null, $validuntil);
    }

    /**
     * Get a user_key object based on a unique user_key value.
     *
     * @param string $value
     * @return user_key
     */
    public static function get_user_key(string $value): user_key {
        global $DB;
        $key = $DB->get_record('user_private_key', ['value' => $value], '*', MUST_EXIST);
        return new user_key($key);
    }

    /**
     * Check if the user key found is valid.
     *
     * @param user_key $key
     * @return bool
     */
    public static function is_user_key_valid(user_key $key): bool {
        // Check the user_private_key was generated for this plugin.
        if (empty($key->get_script()) || $key->get_script() !== self::USER_KEY_SCRIPT) {
            return false;
        }

        // If no time limit set or time limit has expired, the token is invalid.
        if (empty($key->get_validuntil()) || $key->get_validuntil() < time()) {
            return false;
        }
        return true;
    }

    /**
     * Delete the user key.
     *
     * @param user_key $key
     */
    public static function delete_user_key(user_key $key) {
        delete_user_key($key->get_script(), $key->get_userid());
    }

    /**
     * Generate a ws token in the context determined by the user private key for the plugin's default web service.
     *
     * @param user_key $key
     * @return string
     */
    public static function generate_ws_token(user_key $key, $validuntil = 0): string {
        global $DB;
        // Create token for course module that launched the application.
        $context = \context_module::instance($key->get_instance());
        $service = $DB->get_record('external_services', array('shortname' => 'mod_applaunch_service'));
        return external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $key->get_userid(), $context, $validuntil);
    }
}
