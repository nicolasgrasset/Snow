<?php
/**
 * Admin
 * 
 * Example usage of Smart Directories for an admin
 * interface availble under a sub-directory.
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


class snow_admin implements isnow_smartdir 
{
	
	private $user = null;
	
	// Get/Set admin user
	public function & getUser() { return $this->user; }
	public function setUser( &$user ) 
	{ 
		if( $user instanceof  isnow_admin_user )
		{
			$this->user = $user;
			return true;
		}
		return false; 
	}
	
	
	public function init()
	{
		if( !is_null($this->user) && !$this->user->isAdmin() && Snow::app()->getContent() != "login")
		{
			// TODO: Keep current request in session for auto redirection after login
			setcookie("adminreq", $_SERVER['REQUEST_URI'], time()+120, "/");
			header("Location: " . Snow::app()->getBaseWeb() . "/" . Snow::app()->getInc() . "/login" ); 
		}
		
	}
	
	
}