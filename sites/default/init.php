<?php
/**
 * Site init file
 * 
 * Contains all the script instructions that should be executed for each
 * request on the site. Typically database and session initialization
 * 
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 RIVER (www.river.se)
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 */


// Example of Database initialization
$snow_context->v['db'] = new snow_mysqli( 
	$snow_context->getConfig( 'mysqldb_host' ), 
	$snow_context->getConfig( 'mysqldb_user' ), 
	$snow_context->getConfig( 'mysqldb_pass' ), 
	$snow_context->getConfig( 'mysqldb_name' )  
	);

	
// Example of smart directory setup for requests against /admin
$adminDir = new snow_admin();
$adminDir->setUser( new snow_admin_user() );
$snow_context->bindSmartDir( "admin", $adminDir );