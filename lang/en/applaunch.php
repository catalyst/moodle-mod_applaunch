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
 * English lang strings.
 *
 * @package    mod_applaunch
 * @author     Andrew Madden <andrewmadden@catalyst-au.net>
 * @copyright  2021 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = "App Launcher";
$string['modulename'] = "App Launcher";
$string['modulenameplural'] = "App Launchers";
$string['pluginadministration'] = 'Manage App Launcher';

$string['applaunch:addinstance'] = 'Add instance';
$string['applaunch:manageapptypes'] = 'Manage app types';
$string['applaunch:view'] = 'View';

$string['error:launchdirectaccess'] = 'Direct access to this page is not allowed. Return to your activity to access this page.';
$string['error:sitecompletionnotenabled'] = 'Completion is not enabled for site.';
$string['error:activitycompletionnotenabled'] = 'Completion is not enabled for activity.';
$string['error:externalcompletionnotenabled'] = 'External completion by app is not enabled for activity.';
$string['error:usernotexists'] = 'User does not exist.';
$string['error:cmnotexists'] = 'Course module does not exist.';
$string['error:invalidcompletionstate'] = 'Completion state is not valid.';

$string['event:applaunched'] = 'App launched';
$string['event:apptypecreated'] = 'App type created';
$string['event:apptypedeleted'] = 'App type deleted';
$string['event:apptypedisabled'] = 'App type disabled';
$string['event:apptypeenabled'] = 'App type enabled';
$string['event:apptypeupdated'] = 'App type updated';

$string['external:success'] = 'Success';
$string['external:activityslug'] = 'Activity URL slug';

$string['form:applaunch:apptype'] = 'App type';
$string['form:applaunch:defaultapptype'] = 'Choose an app type';
$string['form:applaunch:description'] = 'Description';
$string['form:applaunch:instancename'] = 'Activity name';
$string['form:applaunch:urlslug'] = 'URL slug';
$string['form:applaunch:urlslug_help'] = 'This string will be appended to the launch url.';
$string['form:applaunch:completionexternal'] = 'External app completion';
$string['form:applaunch:completionexternal_help'] = 'Allow the external application to manage completion for users.';

$string['form:app_type:description'] = 'Description';
$string['form:app_type:enabled'] = 'Enabled';
$string['form:app_type:header'] = 'App type';
$string['form:app_type:icon'] = 'Icon URL';
$string['form:app_type:icon_help'] = 'If set, the url will be used for the icon of this app type in the activity chooser.';
$string['form:app_type:name'] = 'Name';
$string['form:app_type:url'] = 'Launch URL';
$string['form:app_type:url_help'] = 'This URL will be used to attempt to open the application.';

$string['launch:description'] = 'The application should open automatically. If not, click on the link below.';

$string['setting:manage_app_types'] = "Manage app types";
$string['setting:addapptype'] = "Add app type";
$string['setting:newapptype'] = 'New app type';
$string['setting:editapptype'] = 'Edit app type';
$string['setting:cantdelete'] = 'Deleting the app type is not allowed. It may be in use by existing activities.';

$string['table:name'] = 'Name';
$string['table:description'] = 'Description';
$string['table:enabled'] = 'Enabled';
$string['table:used'] = 'Used';
$string['table:apptype'] = 'App Type';

$string['view:description'] = 'Click the launch button to open the app.';
$string['view:launch'] = 'Launch';
