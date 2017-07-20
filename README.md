# Akismet plugin for PHPList3

## Description

This plugin checks new subscribers against the Akismet API using the signup comment_type to try and reduce the volume of spam signups.


## Installation ##

### Dependencies ###

The plugin needs the openssl PHP module to be enabled

### Install through the PHPList admin
Install on the Plugins page (menu Config > Plugins) using the package URL `https://github.com/Automattic/phplist-akismet/archive/master.zip`.

### Install manually
Download the plugin zip file from <https://github.com/Automattic/phplist-akismet/archive/master.zip>

Expand the zip file, then copy the plugin file to the plugins directory to your PHPList plugins directory.

### Settings

On the Settings page you need to specify:

* The Akismet API key
* The message to be displayed to the subscriber when the subscription attempt is rejected