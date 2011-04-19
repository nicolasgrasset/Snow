<?php
/**
 * Admin user
 * 
 * Example definiton of admin user to be used in combination with
 * smart directories.
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


class snow_admin_user 
	implements isnow_admin_user 
{
	
	private $user = null;
	
	public function isAdmin() 
	{
		return $this->isConnected();
	}
	
	public function memberOf( $group ) 
	{
		return false;
	}
	
	public function isConnected() 
	{
		if( isset($_COOKIE['admintest']) )
			return true;
		else
			return false;	
	}
	
	
}