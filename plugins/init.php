<?php
/**
 * Snow core initializer
 * 
 * Contains generic logic for plugins:
 *  -> autoloader to load all snow_* classes in plugins without includes
 *  -> __() function definition to rely on Snow context for translations
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

// Load interfaces
require_once( dirname(__FILE__) . '/core/interfaces.php');

// Load Snow
require_once( dirname(__FILE__) . '/snow.php');

// Define autoloader
function snow_autoLoader($className){
	$className = substr($className, 0, 5) == 'snow_' ? substr($className, 5) : $className;
	if( strstr($className, "_") === false )
		$path = $className . "/" . $className;
	else
    	$path = str_ireplace('_', '/', $className);
    if( include_once( dirname(__FILE__) . '/' . $path.'.php') )
        return;
	
}

// Register autoloader
spl_autoload_register('snow_autoLoader');


function __( $str ) {
	
	$str = Snow::app()->getT()->gettext( $str );
	
	for( $i = func_num_args()-1 ; $i ; --$i )
	{
        $s = func_get_arg( $i );
        $str = str_replace( '%'.$i, $s, $str );
    }
	
    return $str;
    
}



?>