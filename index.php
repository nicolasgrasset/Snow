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
 * @copyright     Copyright 2011 RIVER (www.river.se)
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


// Invalid request
if( Snow::app()->requestIsInvalid() )
	Snow::app()->invalidRequest();
	
// Full document request (header + footer)
elseif( Snow::app()->requestIsPage() && Snow::app()->getConfig( 'ob_gzhandler', 'true' ) == 'true' )
{
	ob_start();
	include( Snow::app()->readResponse() );
	$page = ob_get_contents();
	ob_end_clean();
	
	ob_start('ob_gzhandler');
	include( Snow::app()->readHeader() );
	echo $page;
	include( Snow::app()->readFooter() );
	
}

// API request
else
	include( Snow::app()->readResponse() );

	
	