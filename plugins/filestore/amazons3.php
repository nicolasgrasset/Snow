<?php
/**
 * 
 * Amazon S3 filestore
 * 
 * Stores, reads and edit files in an Amazon S3 bucket
 * 
 * All filestores are using the same interfaces (isnow_filestore) and can
 * be interchanged
 * 
 * (TODO: current dependency on PEAR package HTTP_Request could easily be removed)
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

// pear install HTTP_Request
require_once 'HTTP/Request.php';


class snow_filestore_amazons3
	implements isnow_filestore
{
	
	private $conf_keyId;
	private $conf_secretKey;
	private $conf_bucket;
	private $conf_url;

	
	function __construct( $conf_bucket, $conf_secretKey, $conf_keyId, $conf_url = null )
	{
		$this->conf_bucket = $conf_bucket;
		$this->conf_secretKey = $conf_secretKey;
		$this->conf_keyId = $conf_keyId;
		
		if( !is_null($conf_url) )
			$this->conf_url = $conf_url;
		else
			$this->conf_url = "http://{$conf_bucket}.s3.amazonaws.com/";
			
	}
	
	
	public function getUrl( $file )
	{
		return $this->conf_url . $file;
	}
	
	/*
	public function move_uploaded_file( $sourcefile, $targetfile )
	{
		$targetfile = $this->getFilename( $targetfile );
		return move_uploaded_file($sourcefile, $targetfile);
	}
	
	
	public function move( $sourcefile, $targetfile )
	{
		$targetfile = $this->getFilename( $targetfile );
		return move($sourcefile, $targetfile);
	}
	*/
	
	public function save( $src_file, $dst_file, $contentType = "image/jpeg", $maxage = 31536000 )
	{
		return $this->s3_put( $src_file, $dst_file, $contentType, $maxage );
	}
	
	public function save_content( $src_content, $dst_file, $contentType = "image/jpeg", $maxage = 31536000 )
	{
		return $this->s3_put_content( $src_content, $dst_file, $contentType, $maxage );
	}
	
	public function exists( $resource )
	{
		$req = new HTTP_Request( $this->conf_url . $resource, array('allowRedirects'=>true));
		$req->setMethod("GET");
		$req->sendRequest();
		return $req->getResponseCode() == 200;
	}
	
	public function remove( $resource )
	{
		$verb = "DELETE";
		
		
		$httpDate = gmdate("D, d M Y H:i:s T");
		$headers["Date"] = $httpDate;
		
		$stringToSign = "$verb\n\n$httpDate\n/$this->conf_bucket/$resource";
		$stringToSign = "$verb\n\n\n$httpDate\n/$this->conf_bucket/$resource";
		$signature = $this->hex2b64( hash_hmac  ( "sha1"  , $stringToSign  , $this->conf_secretKey ) );
		
		$req = new HTTP_Request( $this->conf_url . $resource, array('allowRedirects'=>true));
		$req->setMethod($verb);
		$req->addHeader("Date", $httpDate);
		$req->addHeader("Authorization", "AWS " . $this->conf_keyId . ":" . $signature);
		$req->sendRequest();
		return $req->getResponseCode() == 204;
	}
	
	public function read( $resource )
	{
		$req = new HTTP_Request( $this->conf_url . $resource, array('allowRedirects'=>true));
		$req->setMethod("GET");
		$req->sendRequest();
		if( $req->getResponseCode() == 200)
			return $req->getResponseBody();
		else
			return false;
	}


	private function s3_put( $src_file, $dst_file, $contentType = "image/jpeg", $maxage = 31536000 )
	{
			
		if( !file_exists($src_file))
			return -5;
		
		return $this->s3_put_content( file_get_contents($src_file), $dst_file, $contentType, $maxage );
	}


	private function s3_put_content( $src_content, $dst_file, $contentType = "image/jpeg", $maxage = 31536000 )
	{
			
		$verb = "PUT";
		
		$httpDate = gmdate("D, d M Y H:i:s T");
		$resource = $dst_file;
		$acl = "public-read";
		$cachecontrol = "max-age=" . $maxage;
		
		$stringToSign = "$verb\n\n$contentType\n$httpDate\nx-amz-acl:$acl\n/$this->conf_bucket/$resource";
		$signature = $this->hex2b64( hash_hmac  ( "sha1"  , $stringToSign  , $this->conf_secretKey ) );
		
		$headers["content-type"] = $contentType;
		$headers["Date"] = $httpDate;
		$headers["Authorization"] = "AWS " . $this->conf_keyId . ":" . $signature;
		
		$retry = 3;
		do{
			$req = new HTTP_Request( $this->conf_url . $resource, array('allowRedirects'=>true));
			$req->setMethod($verb);
			$req->addHeader("content-type", $contentType);
			$req->addHeader("Date", $httpDate);
		    $req->addHeader("x-amz-acl", $acl);
		    $req->addHeader("Cache-Control", $cachecontrol);
			$req->addHeader("Authorization", "AWS " . $this->conf_keyId . ":" . $signature);
			$req->setBody($src_content);
			$req->sendRequest();
			
			$code = $req->getResponseCode();
			if( $code > 200 && $retry > 0 )
				$retry--;
			else if( $code > 200 )
			{
				echo $req->getResponseBody();
				exit();
			}
			else
				$retry = 0;
		}while($retry > 0);
		
		return $req->getResponseCode();
	}
	
	private function hex2b64($str) {
	    $raw = '';
	    for ($i=0; $i < strlen($str); $i+=2) {
	        $raw .= chr(hexdec(substr($str, $i, 2)));
	    }
	    return base64_encode($raw);
	}
	
	
	
}