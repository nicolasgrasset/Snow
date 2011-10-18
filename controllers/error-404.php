<?php 
/**
 * Error controller
 * 
 * All 404 errors will load this file.
 * 
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author		  nicolas.grasset@gmail.com
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 */


// Simply load a view
$this->render( "error", array("errorCode"=>404, "errorMessage"=> "Could not find content for your query" ), "web", 404 );

