<?php
/**
 * Snow application starter
 * 
 * This file is used to clean up the app and rely on Snow::app()-> across the code
 * No more global variable
 * 
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        nicolas.grasset@gmail.com
 * @package       snow
 * @since         Snow v 0.9.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class Snow
{
	
	private static $context;
	
	public static function app( snow_core_config &$config = null, $analyze_request = true )
	{
		if (!isset(self::$context)) {
			self::$context = new snow_core_context( $config, $analyze_request );
		}
		return self::$context;
	}
	
}

