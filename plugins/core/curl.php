<?php
/**
 * cUrl
 * 
 * Wrapper for cURL HTTP requests
 * 
 * (this object is a work in progress inspired based on WordPress source under GPL License)
 * 
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

class snow_core_curl
{
	
	
	
	public function get( $url, $args = array() )
	{
		$defaults = array('method' => 'GET');
		$r = array_merge( $args, $defaults );
		$response = $this->request($url, $r);
		
		if( $response !== false )
			return $response;
		else
			return false;
		
	}
	
	
	public function post( $url, $param, $args = array() )
	{
		$args['body'] = $param;
		$args['timeout'] = 300;
		$defaults = array('method' => 'POST');
		$r = array_merge( $args, $defaults );
		$response = $this->request($url, $r);
		
		if( $response !== false )
			return $response;
		else
			return false;
		
	}
	
	
	
	/**
	 * 
	 * Source code modified from WordPress
	 * 
	 * Send a HTTP request to a URI using cURL extension.
	 *
	 * @access public
	 * @since 2.7.0
	 *
	 * @param string $url
	 * @param str|array $args Optional. Override the defaults.
	 * @return array 'headers', 'body', 'cookies' and 'response' keys.
	 */
	public function request($url, $args = array()) {
		$defaults = array(
			'method' => 'GET', 'timeout' => 5,
			'redirection' => 5, 'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(), 'body' => null, 'cookies' => array(),
			'user-agent' => 'snow'
		);

		$r = array_merge( $defaults, $args );
		

		if ( isset($r['headers']['User-Agent']) ) {
			$r['user-agent'] = $r['headers']['User-Agent'];
			unset($r['headers']['User-Agent']);
		} else if( isset($r['headers']['user-agent']) ) {
			$r['user-agent'] = $r['headers']['user-agent'];
			unset($r['headers']['user-agent']);
		}

		// TODO: Support for cookies

		// cURL extension will sometimes fail when the timeout is less than 1 as it may round down
		// to 0, which gives it unlimited timeout.
		if ( $r['timeout'] > 0 && $r['timeout'] < 1 )
			$r['timeout'] = 1;

		$handle = curl_init();

		$is_local = isset($args['local']) && $args['local'];
		$ssl_verify = isset($args['sslverify']) && $args['sslverify'];

		curl_setopt( $handle, CURLOPT_URL, $url);
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYHOST, $ssl_verify );
		curl_setopt( $handle, CURLOPT_SSL_VERIFYPEER, $ssl_verify );
		curl_setopt( $handle, CURLOPT_USERAGENT, $r['user-agent'] );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, $r['timeout'] );
		curl_setopt( $handle, CURLOPT_TIMEOUT, $r['timeout'] );
		curl_setopt( $handle, CURLOPT_MAXREDIRS, $r['redirection'] );

		switch ( $r['method'] ) {
			case 'HEAD':
				curl_setopt( $handle, CURLOPT_NOBODY, true );
				break;
			case 'POST':
				curl_setopt( $handle, CURLOPT_POST, true );
				@curl_setopt( $handle, CURLOPT_POSTFIELDS, $r['body'] );
				break;
		}

		if ( true === $r['blocking'] )
			curl_setopt( $handle, CURLOPT_HEADER, true );
		else
			curl_setopt( $handle, CURLOPT_HEADER, false );

		// The option doesn't work with safe mode or when open_basedir is set.
		if ( !ini_get('safe_mode') && !ini_get('open_basedir') )
			curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );

		if ( !empty( $r['headers'] ) ) {
			// cURL expects full header strings in each element
			$headers = array();
			foreach ( $r['headers'] as $name => $value ) {
				$headers[] = "{$name}: $value";
			}
			curl_setopt( $handle, CURLOPT_HTTPHEADER, $headers );
		}

		if ( $r['httpversion'] == '1.0' )
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0 );
		else
			curl_setopt( $handle, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );

		// We don't need to return the body, so don't. Just execute request and return.
		if ( ! $r['blocking'] ) {
			curl_exec( $handle );
			curl_close( $handle );
			return array( 'headers' => array(), 'body' => '', 'response' => array('code' => false, 'message' => false), 'cookies' => array() );
		}

		$theResponse = curl_exec( $handle );

		if ( !empty($theResponse) ) {
			$parts = explode("\r\n\r\n", $theResponse);

			$headerLength = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
			$theHeaders = trim( substr($theResponse, 0, $headerLength) );
			$theBody = substr( $theResponse, $headerLength );
			if ( false !== strrpos($theHeaders, "\r\n\r\n") ) {
				$headerParts = explode("\r\n\r\n", $theHeaders);
				$theHeaders = $headerParts[ count($headerParts) -1 ];
			}
			$theHeaders = $this->processHeaders($theHeaders);
		} else {
			if ( $curl_error = curl_error($handle) )
				throw new Exception('http_request_failed: ' . $curl_error);
			if ( in_array( curl_getinfo( $handle, CURLINFO_HTTP_CODE ), array(301, 302) ) )
				throw new Exception('http_request_failed: Too many redirects.');

			$theHeaders = array( 'headers' => array(), 'cookies' => array() );
			$theBody = '';
		}

		$response = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		
		curl_close( $handle );

		/*
		if ( true === $r['decompress'] && true === WP_Http_Encoding::should_decode($theHeaders['headers']) )
			$theBody = WP_Http_Encoding::decompress( $theBody );
		*/

		return array('headers' => $theHeaders['headers'], 'body' => $theBody, 'response' => $response, 'cookies' => $theHeaders['cookies']);
	}

	/**
	 * 
	 * 
	 * Source code modified from WordPress
	 * 
	 * 
	 * Transform header string into an array.
	 *
	 * If an array is given then it is assumed to be raw header data with numeric keys with the
	 * headers as the values. No headers must be passed that were already processed.
	 *
	 * @access public
	 * @static
	 * @since 2.7.0
	 *
	 * @param string|array $headers
	 * @return array Processed string headers. If duplicate headers are encountered,
	 * 					Then a numbered array is returned as the value of that header-key.
	 */
	function processHeaders($headers) {
		// split headers, one per array element
		if ( is_string($headers) ) {
			// tolerate line terminator: CRLF = LF (RFC 2616 19.3)
			$headers = str_replace("\r\n", "\n", $headers);
			// unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>, <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2)
			$headers = preg_replace('/\n[ \t]/', ' ', $headers);
			// create the headers array
			$headers = explode("\n", $headers);
		}

		$response = array('code' => 0, 'message' => '');

		$cookies = array();
		$newheaders = array();
		foreach ( $headers as $tempheader ) {
			if ( empty($tempheader) )
				continue;

			if ( false === strpos($tempheader, ':') ) {
				list( , $iResponseCode, $strResponseMsg) = explode(' ', $tempheader, 3);
				$response['code'] = $iResponseCode;
				$response['message'] = $strResponseMsg;
				continue;
			}

			list($key, $value) = explode(':', $tempheader, 2);

			if ( !empty( $value ) ) {
				$key = strtolower( $key );
				if ( isset( $newheaders[$key] ) ) {
					$newheaders[$key] = array( $newheaders[$key], trim( $value ) );
				} else {
					$newheaders[$key] = trim( $value );
				}
				/* TODO: Cookies support
				if ( 'set-cookie' == strtolower( $key ) )
					$cookies[] = new WP_Http_Cookie( $value );
				*/
			}
		}

		return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
	}
	
}



?>