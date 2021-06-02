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
 * Generate a web service token if a valid user private key is provided.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_applaunch\ws_token;

// As we need this page to be accessed externally, we can't use a require_login. This prevents the code checker complaining.
// @codingStandardsIgnoreStart
require_once(__DIR__ . '/../../config.php');
// @codingStandardsIgnoreEnd

$token = required_param('token', PARAM_TEXT); // Temporary user private key token.

$output = [
    'wstoken' => '',
    'errors' => '',
    'baseurl' => (new moodle_url('/'))->out(),
    'activityslug' => '',
];

try {
    // Check if the token is valid.
    $userkey = ws_token::get_user_key($token);
    if (!ws_token::is_user_key_valid($userkey)) {
        $output['errors'] .= get_string('error:invalidtoken', 'applaunch') . "\n";
    }

    // Create user token and populate data.
    $validuntil = time() + 86400; // Seconds. One day. // TODO: Make this configurable?
    $output['wstoken'] = ws_token::generate_ws_token($userkey, $validuntil);
    $output['activityslug'] = '/mod/applaunch/view.php?id=' . $userkey->get_instance();

    // Token is single use, so clean it up.
    ws_token::delete_user_key($userkey);
} catch (moodle_exception $e) {
    $output['errors'] .= $e->getMessage() . "\n";
}

// Send back wstoken.
echo json_encode($output);
