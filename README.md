# moodle-mod_applaunch

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

Configring an application schema
--------------------------------

1) Lets assume your desktop app has a schema of 'foobar://'
2) Navigate to Admin > blah
3) 3) Add record


The url scheme interface
------------------------

When a learning clicks on a link to open your app it will pass through two query params:

```
foobar://test.com?token=xxxx&baseuri=https://moodle.example.edu,
```

1) token - This is a single use token which the app must exchange in order to gain a normal Moodle Webservice token
2) baseuri - This is the wwwroot of the Moodle instance that launched the app, which MUST be used for all communication back to Moodle

Exchange single use token for WS key
------------------------------------

To exchange the singke use token make a get request like this:

```
[baseuri]]mod/applaunc/token.php?token=xxxxx
```

This will return a json document 

```json
    wstoken => "xxxxx",
    errors: ""
    baseurl: "https://moodle.example.edu"
    activityslug": "/mod/applaunch/view.php?id=1234"
}
```

Mark as complete using Moodle webservice
----------------------------------------

A reference implementation
--------------------------

A reference implementation has been bundled as a standalone php CLI script which implements the single use token
exchange and the callback to the Moodle webservice to mark the activity as complete.

This can we configured

### Ubuntu / Linux

To setup a new custom url schema TBA


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
