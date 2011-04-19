<?php
/**
 * Snow configuration
 *
 * Core configuration is the default configuration loader.
 *
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 RIVER (www.river.se)
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class snow_core_config
{
	private $keys = array();
	
	
	function __construct( $filename = "config.inc.php" )
	{
		include( dirname(__FILE__) . "/../../" . $filename );
	}
	
	
	public function define( $key, $val )
	{
		$this->keys[$key] =& $val;
	}
	
	
	public function &getKey( $key, $default = null )
	{
		if( isset($this->keys[$key]))
			return $this->keys[$key];
		
		return $default;
	}
	
	
	
}