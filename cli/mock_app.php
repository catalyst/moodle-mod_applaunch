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
 * CLI script to mock an app.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/adminlib.php');

$help = "Command line tool to mock an app requesting a ws token and completing an activity.

Options:
    -h --help                   Print this help.
    -u --url                       Launch URL
    -t --token                     Temp token to obtain WS token.

Examples:

    # php mock_app.php --token=launchtoken
        Executes test mocking an external app getting web service token and triggering completion of course module.

    # php mock_app.php --url=testapp://test.com?token=12345
        Executes test mocking an external app getting web service token and triggering completion of course module.
";

list($options, $unrecognised) = cli_get_params(
    [
        'help' => false,
        'url' => false,
        'token' => false,
    ],
    [
        'h' => 'help',
        'u' => 'url',
        't' => 'token',
    ]
);

if ($unrecognised) {
    $unrecognised = implode(PHP_EOL.'  ', $unrecognised);
    cli_error(get_string('cliunknowoption', 'core_admin', $unrecognised));
}

if ($options['help']) {
    cli_writeln($help);
    exit(0);
}

if (empty($options['token']) && empty($options['url'])) {
    cli_writeln("You must provide a valid token or launch url. See help:\n");
    cli_writeln($help);
    exit(1);
}

if (empty($options['token']) && !empty($options['url'])) {
    // Try and extract the token from launch url.
    $url = new moodle_url($options['url']);
    $options['token'] = $url->get_param('token');
}

// 1. Get WS token.
cli_writeln('Getting ws token...');
$tokenendpoint = new moodle_url('/mod/applaunch/token.php', ['token' => $options['token']]);
$response = download_file_content($tokenendpoint->out(false));
cli_writeln('Web service token generated: ' . $response);

// 2. Mark course module as complete.
cli_writeln('Setting the activity as completed...');
$completionendpoint = new moodle_url('/webservice/rest/server.php');
$responsedata = json_decode($response, true);
$postdata = [
    'wstoken' => $responsedata['wstoken'],
    'wsfunction' => 'mod_applaunch_complete_activity',
    'moodlewsrestformat' => 'json',
    'activityslug' => $responsedata['activityslug'],
];

$result = download_file_content($completionendpoint->out(false), [], $postdata);
cli_writeln('Activity completed: ' . $result);

cli_writeln('Mock app test completed');
