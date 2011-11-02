<?php
/**
 * Snow controller
 * 
 * Represent a controller
 * 
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        nicolas.grasset@gmail.com
 * @package       snow
 * @since         Snow v 0.9.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class snow_core_controller
{
	
	private $name;
	private $extension = null;
	private $theme = "web";
	
	private static $messages = array(
	
	// [Informational 1xx]
	
	100=>'100 Continue',
	
	101=>'101 Switching Protocols',
	
	// [Successful 2xx]
	
	200=>'200 OK',
	
	201=>'201 Created',
	
	202=>'202 Accepted',
	
	203=>'203 Non-Authoritative Information',
	
	204=>'204 No Content',
	
	205=>'205 Reset Content',
	
	206=>'206 Partial Content',
	
	// [Redirection 3xx]
	
	300=>'300 Multiple Choices',
	
	301=>'301 Moved Permanently',
	
	302=>'302 Found',
	
	303=>'303 See Other',
	
	304=>'304 Not Modified',
	
	305=>'305 Use Proxy',
	
	306=>'306 (Unused)',
	
	307=>'307 Temporary Redirect',
	
	// [Client Error 4xx]
	
	400=>'400 Bad Request',
	
	401=>'401 Unauthorized',
	
	402=>'402 Payment Required',
	
	403=>'403 Forbidden',
	
	404=>'404 Not Found',
	
	405=>'405 Method Not Allowed',
	
	406=>'406 Not Acceptable',
	
	407=>'407 Proxy Authentication Required',
	
	408=>'408 Request Timeout',
	
	409=>'409 Conflict',
	
	410=>'410 Gone',
	
	411=>'411 Length Required',
	
	412=>'412 Precondition Failed',
	
	413=>'413 Request Entity Too Large',
	
	414=>'414 Request-URI Too Long',
	
	415=>'415 Unsupported Media Type',
	
	416=>'416 Requested Range Not Satisfiable',
	
	417=>'417 Expectation Failed',
	
	// [Server Error 5xx]
	
	500=>'500 Internal Server Error',
	
	501=>'501 Not Implemented',
	
	502=>'502 Bad Gateway',
	
	503=>'503 Service Unavailable',
	
	504=>'504 Gateway Timeout',
	
	505=>'505 HTTP Version Not Supported');
	
	public function __construct( $name, $extension = null )
	{
		$this->name = $name;
		$this->extension = $extension;
		$this->theme = Snow::app()->getConfig("views.theme","web");
		
		// Run default action
		$this->action();
		
	}
	
	protected function action()
	{
		// Load the controller file, or render it if still running legacy
		if( Snow::app()->getConfig("controllers.enabled", false) )
			include( Snow::app()->getControllerFileName( $this->name ) );
		elseif( Snow::app()->requestIsPage() )
			$this->render( $this->name );
		else
			$this->renderPartial( $this->name );
	}
	
	protected function setTheme( $theme )
	{
		$this->theme = $theme;
	}
	
	
	private function render( $name, Array $data = null, $http_response_code = 200, Array $headers = null )
	{
		// Backward compatibility
		$controllersEnabled = Snow::app()->getConfig("controllers.enabled", false);
		
		// HTTP Response code
		if( $http_response_code != 200 )
			$this->httpResponseCode( $http_response_code );
		
		// Headers
		if( !is_null($headers) )
			$this->renderHeader( $headers );
		
		// Get view
		ob_start();
		$this->loadView( $name, $data, $this->theme, $controllersEnabled );
		$page = ob_get_contents();
		ob_end_clean();
		
		// Output with gz compression
		if( Snow::app()->getConfig( 'ob_gzhandler', 'true' ) == 'true' )
			ob_start('ob_gzhandler');
		
		$this->loadHeader( $controllersEnabled );
		echo $page;
		$this->loadFooter( $controllersEnabled );
	}
	
	
	private function renderPartial( $name, Array $data = null, $returnString = false )
	{
		// Backward compatibility
		$controllersEnabled = Snow::app()->getConfig("controllers.enabled", false);
		
		// User output buffer to return as string
		if( $returnString )
			ob_start();
		
		$load = $this->loadView( $name, $data, $this->theme, $controllersEnabled );
		
		if( $returnString )
		{
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		else
			return $load;
		
	}
	
	protected function loadView( $viewName, $data = null, $theme = "web", $controllersEnabled = true )
	{
		
		// legacy
		if( !$controllersEnabled )
			$snow_context = Snow::app();
		
		if( $controllersEnabled )
			$file = Snow::app()->getBaseDir() . "/". Snow::app()->getConfig("views.dir","views") ."/{$theme}/{$viewName}.php";
		else
			$file = Snow::app()->getControllerFileName( $viewName );
		
		if( $controllersEnabled && is_array($data) )
			extract($data);
		
		if( file_exists($file) )
			include( $file );
		else		
			return false;
	}
	
	private function httpResponseCode( $http_response_code = 200 )
	{
		if( function_exists("http_response_code") )
			http_response_code( $http_response_code );
		else
		{
			$message = array_key_exists($http_response_code, self::$messages) ? self::$messages[$http_response_code] : self::$messages[500];
			header($_SERVER["SERVER_PROTOCOL"].$message);
		}
	}
	
	private function renderHeader( Array $headers, $replace = true )
	{
		foreach( $headers as $name=>$value )
		{
			header( "$name: $value", $replace );
		}
	}
	
	protected function loadHeader( $controllersEnabled = true )
	{
		if( $controllersEnabled )
			$this->loadView( "layout/header", null, $this->theme );
		else
		{
			$snow_context = Snow::app();
			include( Snow::app()->readHeader() );
		}
	}
	
	
	protected function loadFooter( $controllersEnabled = true )
	{
		if( $controllersEnabled )
			$this->loadView( "layout/footer", null, $this->theme );
		else
		{
			$snow_context = Snow::app();
			include( Snow::app()->readFooter() );
		}
			
		
	}
	
	
	public function isAjaxRequest() {
	
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") ? true : false;
	
	}
	
	public function isMobile() {
		return Snow::app()->isMobile();
	}
	
	private function getBaseWeb() {
		return Snow::app()->getBaseWeb();
	}
	
	private function getSiteUrl() {
		return Snow::app()->getSiteUrl();
	}
}

