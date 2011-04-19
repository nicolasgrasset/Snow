<?php
/**
 * Interfaces
 * 
 * Common interfaces for Snow
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


interface isnow_db
{
	public function connect();
	public function query( $query, $resultmode = 0 );
	public function multi_query( $query );
	
}

interface isnow_db_result
{
	public function fetch();
	public function fetchAll();
	
}


interface isnow_user
{
	public function auth( $id, $key );
}

interface isnow_user_auth
{
	public function auth( $id, $key );
	public function isAuth();
}



interface isnow_logger
{
	public function log( $message, $level = 3 );
}



interface isnow_i18n
{
	public function gettext( $text );
}



interface isnow_filestore
{
	public function getUrl( $file );
	public function read( $file );
	public function save( $src_file, $dst_file, $contentType = "image/jpeg", $maxage = 31536000 );
	public function remove( $file );
}


interface isnow_smartdir
{
	public function init();
}


interface isnow_admin_user
{
	public function isAdmin();
	public function memberOf( $group );
	public function isConnected();
}