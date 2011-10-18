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
Snow::app()->v['db'] = new snow_mysqli( 
	Snow::app()->getConfig( 'mysqldb_host' ), 
	Snow::app()->getConfig( 'mysqldb_user' ), 
	Snow::app()->getConfig( 'mysqldb_pass' ), 
	Snow::app()->getConfig( 'mysqldb_name' )  
	);

	
// Example of smart directory setup for requests against /admin
$adminDir = new snow_admin();
$adminDir->setUser( new snow_admin_user() );
Snow::app()->bindSmartDir( "admin", $adminDir );