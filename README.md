# moodle-mod_applaunch
![GitHub Workflow Status (branch)](https://img.shields.io/github/workflow/status/catalyst/moodle-mod_applaunch/ci/main?label=ci)

A moodle activity plugin to manage launching an external applications.

The administrator can define multiple different types of applications and these will show
in the activity chooser with their own icons and names, similar to the way LTI plugin.


Branches
--------

| Moodle verion     | Branch           | PHP  |
| ----------------- | ---------------- | ---- |
| Moodle 3.9+       | MOODLE_39_STABLE | 7.1+ |
| Totara up to 11   | MOODLE_39_STABLE | 7.1+ |


Setup
--------

The `webservice/{name}:use` capability for all users that are using this plugin. As a catch all method, you can enable this capability for the *Authenticated user* role. If you require less access to this capability, you could create a new role based on the student archetype with this capability enabled. New users could then be enrolled in the course containing the applaunch activity with new role.

To create or modify a role, go to `Site administration` -> `Users` -> `Define roles`.

If using the rest protocol, the capability would be called `webservice/rest:user`.

You will also need to enable and configure web services, including the protocols required such as REST. https://docs.moodle.org/en/Using_web_services

Configuring an application schema
--------------------------------

1) Lets assume your desktop app has a schema of 'foobar://'
2) Navigate to Admin > Plugins > Activities > App Launcher > Manage app types
3) Create or edit an app type. Under 'URL', add the schema with any part of the url that would be used by any course. E.g. 'foobar://activity.run'
4) Go to a course, and create or edit an applauncher activity with the same app type. Under 'URL slug', and a string to be appended to the url that is specific for the course. This is options. E.g. '/course/123


The url scheme interface
------------------------

When a learner clicks on a link to open your app it will pass through two query params:

```
foobar://test.com?token=xxxx&baseuri=https%3A%2F%2Fmoodle.example.edu,
```

1) token - This is a single use token which the app must exchange in order to gain a normal Moodle Webservice token
2) baseuri - This is the wwwroot of the Moodle instance that launched the app, which MUST be used for all communication back to Moodle. Note that the value will be URL escaped

Exchange single use token for WS key
------------------------------------

To exchange the single use token make a GET request like this:

```
[baseuri]mod/applaunch/token.php?token=xxxxx
```

This will return a json document

```json
{
    "wstoken": "xxxxx",
    "errors": "",
    "baseurl": "https://moodle.example.edu",
    "activityslug": "/mod/applaunch/view.php?id=1234"
}
```

Mark as complete using Moodle webservice
----------------------------------------

Look at the Moodle docs regarding the protocol that is being used for a more detailed outline of how to make a web service request to Moodle. An example for the REST protocol will be provided below. The key bits of information you need are:

* wstoken: [provided in json from token.php]
* wsfunction: mod_applaunch_complete_activity
* activityslug: [provided in json from token.php]

Rest example:
Must use the POST protocol.

```sh
curl -d "wstoken=value1&wsfunction=mod_applaunch_complete_activity&moodlewsrestformat=json&activityslug=value2" -X POST https://www.moodle.com/webservice/rest/server.php
```
Calling this endpoint with the external function `archivecompletion` will mark the activity as complete for the user who clicked the launch button.

On a success you will receieve (if JSON is selected for return format)

```json
{"success": "true"}
```

If there is a failure, the format of the response will be:

```json
{
    "exception": "dml_missing_record_exception",
    "errorcode": "invalidrecordunknown",
    "message": "An error message",
    "debuginfo": "Extra debug info"
}
```

A reference implementation
--------------------------

A reference implementation has been bundled as a standalone php CLI script which implements the single use token
exchange and the callback to the Moodle webservice to mark the activity as complete.

This can be configured

### Ubuntu / Linux

To setup a new custom url schema TBA

This guide can be followed to set up a specific app or script to be run, when a custom schema is detected.

https://unix.stackexchange.com/questions/497146/create-a-custom-url-protocol-handler


### Windows

TBA


Support
-------

If you have issues please log them in github here

https://github.com/catalyst/moodle-mod_applaunch/issues

Please note our time is limited, so if you need urgent support or want to
sponsor a new feature then please contact Catalyst IT Australia:

https://www.catalyst-au.net/contact-us


Credits
-------

This plugin was developed by Catalyst IT Australia:

https://www.catalyst-au.net/

<img alt="Catalyst IT" src="https://cdn.rawgit.com/CatalystIT-AU/moodle-auth_saml2/master/pix/catalyst-logo.svg" width="400">

Activity Icon: Icons made by [Freepik](https://www.freepik.com "Freepik") from [www.flaticon.com](https://www.flaticon.com/ "Flaticon")
