<?php
/**
 * File library
 * 
 * (this has been improved in other projects. should be updated!)
 * 
 * The file library makes use of a file store and a SQL database
 * to store files (mostly images) in multiple formats to be associated
 * with objects within the application
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

/*
 

CREATE TABLE  filelibrary (
`UIDP` INT( 12 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`formats` VARCHAR( 256 ) NOT NULL ,
`mime` VARCHAR( 50 ) NOT NULL ,
`extension` VARCHAR( 10 ) NOT NULL ,
`lastupdated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = MYISAM ;



 */



class snow_filelibrary
{
	
	private $fs = null;
	private $db = null;
	private $dbt = null;
	private $tmpdir = null;
	
	private $secret = "snow";
	
	function __construct( &$filestore, &$database, $sqltable, $tmpdir = "/tmp", $secret = "snow" )
	{
		
		if( !($filestore instanceof isnow_filestore) )
			throw new Exception( "Filestore specified is not implementing isnow_filestore" );
			
		if( !($database instanceof isnow_db) )
			throw new Exception( "Database specified is not implementing isnow_db" );
		
		$this->fs =& $filestore;
		$this->db =& $database;
		$this->dbt = $sqltable;
		$this->tmpdir = $tmpdir;
		
		$this->secret = $secret;
	
	}
	

	
	private function getFileName( $id, $fname = "original.jpg" )
	{
		$id = 0+$id;
		if( !is_int($id) )
			return null;
			
		$dir = md5( $this->secret . $id );
		return substr( $dir, 0, 3) . "/" . $dir . "/" . $id . "/" . $fname;
		
	}
	
	public function getUrl( $id, $fname = "original.jpg" )
	{	
		return $this->fs->getUrl( $this->getFileName($id, $fname) );
	}
	
	
	public function save( $src_file, $isphoto = true, $mimetype = "image/jpeg", $formats = null, $id = null )
	{
		global $snow_context;
		
		$filesToCopy = array();
		$formatstr = "original";
		$extension = (($pos = strrpos($src_file, ".")) === FALSE) ? "" : substr($src_file, $pos);
		
		
		// Check if file exists
		if( !file_exists( $src_file ) )
		{
			$snow_context->log( "Could not open file $src_file", 3);
			return false;
		}
		
		// Check if file is a photo when it should be
		$testImage = getimagesize($src_file);
		if( $isphoto && $testImage === false )
		{
			$snow_context->log( "File $src_file is not an image", 2);
			return false;
		}
		
		// Not an image
		else
		{
			$tmpfile = "original" . $extension;
			$filesToCopy[ $tmpfile ] = $src_file;
		}
		
		// Generate extra formats
		if( $isphoto && is_array($formats) )
		{
			list($width, $height, $type, $attr) = $testImage;
			$mimetype = image_type_to_mime_type($type);
			$extension = image_type_to_extension($type);
			$extension = $extension == ".jpeg" ? ".jpg" : $extension;
			
			$tmpname = $this->tmpdir . "/tmpsnow_" . time() . rand(0,999) . md5(rand(0,999).time()) . "_";
			
			// Original format
			$tmpfile = "original" . $extension;
			if( !copy( $src_file, $tmpname . $tmpfile ) )
			{
				$snow_context->log( "Could not copy $src_file to {$tmpname}{$tmpfile}", 3);
				return false;
			}	
			$snow_context->log( "Original file should be at " . $tmpname . $tmpfile, 1);
			$filesToCopy[ $tmpfile ] = $tmpname . $tmpfile;
			
			
			// New formats
			foreach( $formats as $one )
			{
				$width = $one[0];
				$height = is_null($one[1]) ? "auto" : $one[1];
				$tmpfile = $width . "x" . $height . $extension;
				if( !$this->imageCopyResample($src_file, $tmpname . $tmpfile, $one[0], $one[1] ) )
				{
					$snow_context->log( "Could not resample image $src_file to $width x $height", 3);
					foreach( $filesToCopy as $one )
					{
						if( $one != $src_file)
							@unlink( $one );
					}
					return false;
				}
				$filesToCopy[ $tmpfile ] = $tmpname . $tmpfile;
				$formatstr .= "," . $width . "x" . $height;
			}
		}
		
		// Store in DB
		if( is_null($id) )
			$sql = "INSERT INTO {$this->dbt} (formats,mime,extension) VALUES ('$formatstr', '$mimetype', '$extension')";
		else
			$sql = "UPDATE {$this->dbt} SET formats='$formatstr', mime='$mimetype', extension='$extension' WHERE UIDP=$id";
		
		// Run SQL
		if( !$this->db->query( $sql ) )
		{
			$snow_context->log( "Could not update DB table {$this->dbt}", 3);
			foreach( $filesToCopy as $one )
			{
				if( $one != $src_file)
					@unlink( $one );
			}
			return false;
		}
		
		// Check ID
		$id = is_null($id) ? $this->db->insert_id : $id;
		
		
		// Save files
		foreach( $filesToCopy as $fname=>$path )
		{
			$this->getFileName($id, $fname );
			if( $this->fs->save( $path, $this->getFileName($id, $fname ), $mimetype ) > 0 )
				$snow_context->log( "File $fname saved in filestore", 1);
			else
				$snow_context->log( "Could not save $fname in filestore @ " . $this->getFileName($id, $fname ), 3);
			@unlink( $one );
		}
		
		
		foreach( $filesToCopy as $one )
		{
			if( $one != $src_file)
				@unlink( $one );
		}
	
		
		return $id;
		
	}
	

	private function imageCopyResample($source, $dest, $desired_width, $desired_height = null )
	{   
		// Get new dimensions
		list($width, $height, $type) = getimagesize($source);
		
		if( $type != IMAGETYPE_JPEG && $type != IMAGETYPE_GIF && $type != IMAGETYPE_PNG)
			return false;
		
		$desired_width = $desired_width > $width ? $width : $desired_width;
		$desired_height = is_null($desired_height) ? $height * ($desired_width / $width) : $desired_height;
		$desired_height = $desired_height > $height ? $height : $desired_height;
		
		
		if($desired_width/$desired_height > $width/$height) 
		{
			$new_width = $desired_width;
			$new_height = $height * ($desired_width / $width);
		}
	    else
	    {
	        $new_width = $width * ($desired_height / $height);
	        $new_height = $desired_height;
		}
	    
	  
		// Resize
		$image_p = imagecreatetruecolor($new_width, $new_height);
		$image_f = imagecreatetruecolor($desired_width, $desired_height);
		
		switch( $type )
		{
			case IMAGETYPE_JPEG:	$image = imagecreatefromjpeg($source); break;
			case IMAGETYPE_GIF:		$image = imagecreatefromgif($source); break;
			case IMAGETYPE_PNG:		$image = imagecreatefrompng($source); break;
			default: return false;
		}
		
		
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	   
		$x = ($new_width - $desired_width) / 2;
		$y = ($new_height - $desired_height) / 2;
	   
		imagecopyresampled($image_f, $image_p, 0, 0, $x, $y, $desired_width, $desired_height, $desired_width, $desired_height );
	   
		
	
		switch( $type )
		{
			case IMAGETYPE_JPEG:	imagejpeg($image_f, $dest, 95); break;
			case IMAGETYPE_GIF:		imagegif($image_f, $dest); break;
			case IMAGETYPE_PNG:		imagepng($image_f, $dest, 1); break;
			default: return false;
		}
		
		return true;
	}
	
	
	
	
	
	
	
}