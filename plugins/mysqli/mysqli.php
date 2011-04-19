<?php
/**
 * Snow MySQLi
 * 
 * MySQLi object wrapper to abstract sql databases access 
 * and be able to rely on isnow_db interface
 * 
 * Note that the main modification is that calling the constructor will not
 * open a connection with the MySQL server
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


class snow_mysqli extends mysqli
	implements isnow_db
{
	private $db_host;
	private $db_user;
	private $db_pass;
	private $db_name;
	
	var $attempts;
	var $insertid;
	
	var $connection;
	var $query;
	var $result;
	var $results;
	var $rows;
	
	var $err;
	
	
	function __construct( $host, $user, $pass, $db = "snow" )
	{
		parent::init();
		
		
		$this->db_host = $host;
	  	$this->db_user = $user;
	  	$this->db_pass = $pass;
		$this->db_name = $db;
		
		$this->attempts = 7;
		
		$this->connected = false;
	}
	
	function __destruct()
	{
		if( $this->connected )
			parent::close();
	}
	
	public function connect()
	{
		while( $this->connected == false && $this->attempts-- > 0 ) {
	        $this->connected = @parent::real_connect($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
		}
        
		if( !$this->connected )
			throw new Exception( "Could not connect to {{$this->db_host}}: {$this->error}"  );
		
        return $this->connected;
	}
	
	
	public function query( $query, $resultmode = 0 )
	{
		if( !$this->connect() )
			return false;
			
		if( $this->real_query( $query ) ) 
			return new snow_mysqli_result($this);
		else 
			return false;
		
	}
	
	
	public function multi_query( $query )
	{
		if( !$this->connect() )
			return false;
			
		return parent::real_multi_query( $query );
	}
	
	
};

