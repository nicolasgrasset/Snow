<?php
/**
 * SQL DB test page
 * 
 * Simple test assuming the DB is setup under Snow::app()->v['db'],
 * most often done under sites/YOUR_SITE/init.php
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

$res = Snow::app()->v['db']->query( "SHOW TABLES" );

if( $res instanceof isnow_db_result )
	echo "MySQLi is working. # of tables: " . $res->num_rows;
else
	echo "MySQLi is NOT working";
