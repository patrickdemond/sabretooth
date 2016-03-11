<?php
/**
 * settings.ini.php
 * 
 * Defines initialization settings for sabretooth.
 * DO NOT edit this file, to override these settings use settings.local.ini.php instead.
 * Any changes in the local ini file will override the settings found here.
 */

global $SETTINGS;

// tagged version
$SETTINGS['general']['application_name'] = 'sabretooth';
$SETTINGS['general']['instance_name'] = $SETTINGS['general']['application_name'];
$SETTINGS['general']['version'] = '2.0.0';
$SETTINGS['general']['build'] = '56e260f';

// always leave as false when running as production server
$SETTINGS['general']['development_mode'] = false;

// the location of sabretooth internal path
$SETTINGS['path']['APPLICATION'] = '/usr/local/lib/sabretooth';
