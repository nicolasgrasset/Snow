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

// Start Snow engine
$snow_context = new snow_core_context( $config );

// Initialize theme
include( $snow_context->getSiteDir() . "/init.php" );


// Invalid request
if( $snow_context->requestIsInvalid() )
	$snow_context->invalidRequest();
	
// Full document request (header + footer)
elseif( $snow_context->requestIsPage() && $snow_context->getConfig( 'ob_gzhandler', 'true' ) == 'true' )
{
	ob_start();
	include( $snow_context->readResponse() );
	$page = ob_get_contents();
	ob_end_clean();
	
	ob_start('ob_gzhandler');
	include( $snow_context->readHeader() );
	echo $page;
	include( $snow_context->readFooter() );
	
}

// API request
else
	include( $snow_context->readResponse() );

	
	