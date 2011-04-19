<?php
/**
 * Snow MySQL
 * 
 * MySQL object wrapper to abstract sql databases access 
 * and be able to rely on isnow_db interface
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


class snow_mysql
	implements isnow_db
{
	
	private $connection = false;
	
	function __construct( $mysqldb_name, $mysqldb_user, $mysqldb_pass, $mysqldb_host = "localhost" )
	{
		$this->connection = mysql_connect($mysqldb_host,$mysqldb_user,$mysqldb_pass, true, 128);
		
		if( $this->connection )
			$db = mysql_select_db($mysqldb_name, $this->connection);
			
	}
	
	function __destruct()
	{
		mysql_close( $this->connection );
			
	}
	
	public function query( $sql )
	{
		if( !$this->connection )
			return new snow_mysql_result(false);
			
		$res = mysql_query( $sql, $this->connection);
		
		if( strcasecmp(substr($sql, 0, 6), "SELECT") == 0 )
			return new snow_mysql_result( $res );
		else
			return $res;
	}
	
}