<?php
/**
 * Local disk filestore
 * 
 * Default file store placing all files under the /fs folder directly
 * in the root of the application
 * 
 * All filestores are using the same interfaces (isnow_filestore) and can
 * be interchanged
 * 
 * (Built for Unix/Linux systems)
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


class snow_filestore
	implements isnow_filestore
{

	private $dir = null;
	
	
	function __construct( $dir = "null" )
	{
		$this->dir = trim($dir, "/");
	}
	
	private function cleanPath( $path )
	{
		$newpath = preg_replace('/\w+\/\.\.\//', '', $path);
		if( $newpath == $path )
			return $path;
		else
			return $this->cleanPath( $newpath );
	}
	
	
	public function getRoot()
	{
		return $this->cleanPath( Snow::app()->getBaseDir() . "/fs/" . $this->dir) . "/";
		
	}
	
	public function getUrl( $file )
	{
		return Snow::app()->getBaseWeb(). "/fs/" . $this->dir . "/" . $file;
		
	}
	
	
	public function move_uploaded_file( $sourcefile, $targetfile )
	{
		$targetfile = $this->getFilename( $targetfile );
		return move_uploaded_file($sourcefile, $targetfile);
	}
	
	public function save( $src_file, $dst_file, $contentType = "image/jpeg", $maxage = 31536000 )
	{
		$targetfile = $this->getFilename( $dst_file );
		if( !$this->createDir( $targetfile ) )
			return false;
		return copy( $src_file, $targetfile );
	}
	
	public function createDir( $file )
	{
		if( strrpos( $file, "/" ) === false)
			return false;
			
		$dir = substr( $file, 0, strrpos( $file, "/" ) );
		
		if( file_exists($dir) )
			return true;
			
		$ret = mkdir( $dir, 0700, true);
			
		if( !$ret )
			Snow::app()->log( "Could not create $dir", 3 );
		
		return $ret;
		
	}
	
	public function move( $sourcefile, $targetfile )
	{
		$targetfile = $this->getFilename( $targetfile );
		return move($sourcefile, $targetfile);
	}
	
	public function remove( $targetfile )
	{
		$targetfile = $this->getFilename( $targetfile );
		return unlink( $targetfile );
	}
	
	public function read( $file )
	{
		if( file_exists($this->getFilename( $file )) )
			return file_get_contents( $this->getFilename( $file ) );
		else
			return false;
	}
	
	public function getFilename( $targetfile )
	{
		return $this->getRoot() . $targetfile;
	}
	

	function imageCopyResample($source, $dest, $desired_width, $desired_height = null )
	{   
		// Get new dimensions
		list($width, $height, $type) = getimagesize($source);
		
		if( $type != IMAGETYPE_JPEG)
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
		$image = imagecreatefromjpeg($source);
		imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
	   
		$x = ($new_width - $desired_width) / 2;
		$y = ($new_height - $desired_height) / 2;
	   
		imagecopyresampled($image_f, $image_p, 0, 0, $x, $y, $desired_width, $desired_height, $desired_width, $desired_height );
	   
		imagejpeg($image_f, $this->getFilename( $dest ), 95);
	  
		return chmod( $this->getFilename( $dest ), 0755 );
	}
	
	
	
	
}

