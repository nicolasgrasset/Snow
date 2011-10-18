<?php
/**
 * Login page+logic of the admin (example case)
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

if( Snow::app()->getContent(2) == "auth" )
{
	setcookie("admintest", true, time()+120, "/");
	$q = isset($_COOKIE['adminreq']) ? $_COOKIE['adminreq'] : "";
	header("Location: " . Snow::app()->getBaseWeb() . $q);
}
else
	echo "<a href='". Snow::app()->getBaseWeb() . "/" . Snow::app()->getInc() ."/login/auth'>Please login</a>";

