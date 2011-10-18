<?php
/**
 * HTTP requests handler
 *
 * Loads the core of snow for web server requests
 * Requires proper .htaccess setup on Apache
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        nicolas.grasset@gmail.com
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */


// Initialize Snow
$maindir = isset($maindir) ? $maindir : "./";
require_once( $maindir . "plugins/init.php" );

// Load config
$config = new snow_core_config();

// Start Snow engine ($snow_context set for backward compatibility)
$snow_context = Snow::app( $config );

// Initialize theme
include( Snow::app()->getSiteDir() . "/init.php" );

// Handle HTTP Request
Snow::app()->routeHttpRequest();


	
	