<?php
/**
 * Logger
 * 
 * Default logger for Snow based on PHP error_log().
 * Writes to log files.
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

class snow_core_logger
	implements isnow_logger
{
		
	/**
	 * 
	 * 	LEVEL 1: DEBUG
	 *  LEVEL 2: INFO
	 *  LEVEL 3: WARNING
	 *  LEVEL 4: ERROR
	 *  LEVEL 5: CRITICAL
	 * 
	 * 
	 */
	
	
	
	public function log( $message, $level = 3 )
	{
		$stack = debug_backtrace();
		$stack = $this->readPluginName($stack);
		$stackName = "{" . $stack . "}";
			
		$packageLevel = Snow::app()->getConfig( 'log.' . $stack );
		$globalLevel = Snow::app()->getConfig( 'log.level', 1 );
		
		
		if( ( !is_null($packageLevel) && $level < $packageLevel )		// package log level is set, and is higher than this event
			|| (is_null($packageLevel) && $level < $globalLevel) )		// package log level is not set, and global level is higher than this event
			return;
			
		
		$level = $this->adjustLevel( $level );
		
		$date = date( "c" );
		if( !isset(Snow::app()->v['cli']) )
			$user_ip = $_SERVER['REMOTE_ADDR'];
		else
			$user_ip = "php_cli";
		
		$message = explode("\n", $message);
		foreach( $message as $n=>$line )
		{
			$this->write( "$date [LVL$level] ($user_ip) $stackName #:$n $line", $level );
		}
		
	}
	
	
	
	private function write( $line, $level )
	{
		if( !is_null( Snow::app()->getConfig( 'log.file' ) ) )
			error_log( $line . "\n", 3, Snow::app()->getConfig( 'log.file' ) );
		else
			error_log( $line, 0 );
			
		if( $level == 5
			&& Snow::app()->getConfig( 'log.email', false )
			&& strlen(Snow::app()->getConfig( 'log.emailto', "" )) > 5 )
			error_log( $line, 1, Snow::app()->getConfig( 'log.emailto' ) );
			
	}
	
	
	private function readPluginName( $stack )
	{
		if( !isset($stack[2]['class']) )
			return "*";
		
		$i = 2;
		while( isset($stack[$i]['class']) && substr($stack[$i]['class'],0, 5) != "snow_" )
		{
			$i++;
		}
		
		if( !isset($stack[$i]['class']) || substr($stack[$i]['class'],0, 5) != "snow_" )
			return "*";
			
		$class = explode("_", $stack[$i]['class']);
		return $class[0] . "_" . $class[1];
		
	} 
	

	private function adjustLevel( $level )
	{
		if( !is_numeric($level) )
			return 2;
			
		$level = 0 + $level;
		
		if( !is_int($level) )
			return 2;
		
		if( $level < 1 )
			return 1;
		
		if( $level < 1 )
			return 1;
		
		if( $level > 5 )
			return 5;
			
		return $level;
	}
	
}