<?php
/**
 * Configuration file
 *
 * Holds the current deployment's configuration
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 RIVER (www.river.se)
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */


define("SNOW_ENV",  "live");

// Site to load
$this->define(	'site', 'default');

// Localization
$this->define(	'local.url', 	false );								// Activate or deactivate local in path (ex en/ sv/)
$this->define(	'local.paths', 	array('en'=>'en_US','sv'=>'sv_SE') );	// Locales to activate in path
$this->define(	'local.default', 'en_US' );								// Default locale
$this->define(	'local.domain', 'default' );							// Domain (translation file)

// Non-pages directories
$this->define(	'directories', 	array('api','rss') );



// Logging config
$this->define(	'log.file', "/var/log/snow.log" ); 				// Config filename (null means web server error log)
$this->define(	'log.email', 	false );						// Send email for error level 5?
$this->define(	'log.emailto', 	"" ); 							// Email address for error level 5?
$this->define(	'log.level', 1 );								// General log level
$this->define(	'log.*', 1 );									// Log level outside of plugins
$this->define(	'log.snow_mysqli', 4 );							// Log level for a specific plugin (mysqli)



