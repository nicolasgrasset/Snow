<?php
/**
 * Snow MySQL Result
 * 
 * MySQL result object wrapper to abstract sql databases access 
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



class snow_mysql_result
	implements isnow_db_result
{
	
	private $res = null;
	
	public $num_rows = 0;
	
	function __construct( &$result )
	{
		$this->res =& $result;
		
		if( $this->res )
			$this->num_rows = mysql_num_rows( $this->res );
		
	}
	
	
	public function fetch()
	{
		return mysql_fetch_assoc( $this->res );
	}

}