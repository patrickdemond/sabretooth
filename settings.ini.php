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
$SETTINGS['general']['version'] = '2.4';
$SETTINGS['general']['build'] = 'e850d69';

// the location of sabretooth internal path
$SETTINGS['path']['APPLICATION'] = str_replace( '/settings.ini.php', '', __FILE__ );

// add modules used by the application
$SETTINGS['module']['interview'] = true;
$SETTINGS['module']['recording'] = true;
$SETTINGS['module']['script'] = true;
$SETTINGS['module']['voip'] = true;
