<?php
/**
 * Snow core
 *
 * The context holds all core features to run a web application or site.
 * This object know where the application is (URL + local path), and gathers
 * pointers to all the generic components
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2011 RIVER (www.river.se)
 * @package       snow
 * @since         Snow v 0.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class snow_core_context
{
	
	private $basedir = null;
	private $baseweb = null;
	
	private $locale = null;
	
	private $request;

	private $content = null;
	
	private $documentTitle = "";
	
	private $documentHeader = array();
	
	private $config = null;
	
	private $logger = null;
	
	private $translator = null;
	
	private $smartdirs = array();
	
	public $v = array();


	function __construct( &$config = null, $analyze_request = true )
	{
		/*
		 *	Parse query
		 */
		if( $config instanceof snow_core_config )
			$this->config = $config;
			
			
		$this->basedir = str_replace('\\','/',dirname(__FILE__)) . "/../..";
		$this->baseweb = $this->getConfig( "baseurl", "http://" . @$_SERVER['SERVER_NAME'] );
			
		if( $analyze_request )
			$this->parseUrl( $this->getConfig( 'default_inc', 'index') );
		
	}

	function __destruct() {
		if( !is_null($this->config) )
			unset($this->config);
   }

	function getBaseDir() {
		return $this->basedir;
   }

	function getBaseWeb() {
		return $this->baseweb;
   }
	
	
	
	/**
	 * Get/Set page title
	 */
	public function getPageTitle() { return $this->documentTitle; }
	public function setPageTitle( $title ) { $this->documentTitle = $title; }
	
	
	/**
	 * Get/Set page title
	 */
	public function appendPageHeader( $code ) { $this->documentHeader[] = $code; }
	public function readPageHeader() { return implode("\n", $this->documentHeader); }
	
	
	/**
	 * Get/Set main page/directory
	 */
	public function getInc()  { return $this->content[0]; }
	public function setInc( $inc )  { $this->content[0] = $inc; }
	
	
	/**
	 * Get/Bind smart directories
	 */
	public function isSmartDir( $path )  
	{ 
		return isset( $this->smartdirs[$path] ); 
	}
	public function & getBindSmartDir( $path )  
	{ 
		if( isset( $this->smartdirs[$path] ) )
			return $this->smartdirs[$path];
		else
			return null; 
	}
	public function bindSmartDir( $path, &$smartdir )  
	{ 
		if ( $smartdir instanceof isnow_smartdir )
		{
			$this->smartdirs[$path] = $smartdir;
			if( $this->getInc() == $path )
			{
				if (!isset($this->content[1]) || strlen(trim($this->content[1])) <= 0)
					$this->content[1] = $this->getConfig( 'default_inc', 'index' );	
					
				$this->smartdirs[$path]->init();
			}
			return true;
		}
		return false; 
	}
	
	
	/**
	 * Get/Set locale
	 */
	public function getLocale()  
	{ 
		if(is_null($this->locale)) 
			$this->setLocale( $this->getConfig('local.default','sv_SE') ); 
		return $this->locale; 
	}
	public function setLocale( $l )  { $this->locale = $l; }
	
	
	/**
	 * Get/Set translator
	 */
	public function & getT()  
	{
		if(is_null($this->translator)) 
		{
			$name = $this->getConfig('translator','snow_i18n');
			$this->setT( new $name );
		} 
		return $this->translator; 
	}
	public function setT( &$t )  { $this->translator = $t; }
	
	
	
	/**
	 * Read from config
	 * 
	 * 
	 * @param mixed $key: Configuation field
	 * @param mixed $default: Default value to return in case none is found
	 * @return mixed
	 */
	public function & getConfig( $key, $default = null ) 
	{ 
		if( is_null($this->config) )
			return $default;
			
		return $this->config->getKey( $key, $default );
			
	}
	
	
	
	/**
	 * Returns URI content per level 
	 * 
	 * Example of /page/content/subcontent/subsubcontent:
	 *	/band/rolling-stones/albums/sticky-fingers
	 * 
	 * @param int $level : Content level, 0=page, 1=content, 2=subcontent etc 
	 * 
	 * @return array URI elements
	 */
	public function getContent( $level = 1, $default = null ) 
	{
		if( !isset($this->content[$level]) )
			return $default;
		else
			return $this->content[$level];
	}
	
	
	
	/**
	 * Parses the URL to extract the request and parameters
	 * 
	 * @param string $default_inc : Default page to load in case request is empty (i.e. '/') 
	 * 
	 * @return array URI elements
	 */
	public function parseUrl( $default_inc = 'index' )
	{
		$this->request = $_SERVER['REQUEST_URI'];
		$site_urlpath = parse_url($this->config->getKey( 'baseurl' ), PHP_URL_PATH);
			
		if( !is_null($site_urlpath) && substr( $this->request, 0, strlen($site_urlpath) ) == $site_urlpath )
			$this->request = substr( $this->request, strlen($site_urlpath) );
			
		$path = strpos($this->request, "?") === false ? $this->request : substr($this->request, 0, strpos($this->request, "?") ); 
		$this->content = explode("/", trim($path, "/"));
		
		if( !isset($this->content[0]) || strlen(trim($this->content[0])) <= 0)
			$this->content[0] = $default_inc;			
			
		// Update path data if local path 
		$this->localPathUpdate($default_inc);
			
		
		if( !$this->requestIsPage() && (!isset($this->content[1]) || strlen(trim($this->content[1])) <= 0) )
			$this->content[1] = $default_inc;	
			
		return;
	}
	
	
	
	/**
	 * Checks whether the current request is a page or not (api, rss, etc.)
	 * 
	 * @return boolean
	 */
	public function requestIsPage()
	{
		if( in_array( $this->getInc(), $this->getConfig( 'directories', array('api','rss'))) )
			return false;
		elseif ( $this->isSmartDir( $this->getInc() ) )
			return false;
		return true;
	}
	
	
	
	/**
	 * Checks for locale definition in path
	 * 
	 * @return boolean
	 */
	public function localPathUpdate($default_inc = 'index')
	{
		$paths = $this->getConfig( 'local.paths', array());
		
		if( !$this->getConfig( 'local.url', false)
			|| !isset( $paths[$this->content[0]] ) )
			return false;
			
		$this->setLocale( $paths[$this->content[0]] );
		
		foreach( $this->content as $k=>$v )
		{
			if( $k > 0 )
				$this->content[($k-1)] = $v;
		}
		
		unset($this->content[(count($this->content)-1)]);
		
		if( !isset($this->content[0]) || strlen(trim($this->content[0])) <= 0)
			$this->content[0] = $default_inc;		
			
		return true;
	}
	
	
	
	/**
	 * Checks whether the current request is a page or not (api, rss, etc.)
	 * 
	 * @return boolean
	 */
	public function requestIsInvalid()
	{
		if( !file_exists( $this->readResponse() ) )
			return true;
		return false;
	}

	public function invalidRequest()
	{
		// TODO: EDIT THIS with default 404;
		echo "request: " . $this->request."<br/>";
		echo "siteurl: " . $this->config->getKey( 'baseurl' ) ."<br/>";
		echo "basepath: " . parse_url($this->config->getKey( 'baseurl' ), PHP_URL_PATH)."<br/>";
		echo "basedir: {$this->basedir}<br/>";
		echo "content: ".$this->readResponse()."<br/>";
		echo __("404 error. Please edit me");
	}
	
	
	
	public function readResponse()
	{
		if( $this->requestIsPage() )
			return $this->basedir . "/content/" . $this->getInc() . ".php";
		else
			return $this->basedir . "/content/" . $this->getInc() . "/" . $this->getContent(1) . ".php";
	}
	
	
	// Returns Site folder Directory (full path)
	public function getSiteDir()
	{
		return $this->basedir . "/sites/" . $this->getConfig( 'site', 'default' );
	}
	
	
	// Returns Site URL (absolute)
	public function getSiteUrl()
	{
		return $this->baseweb . "/sites/" . $this->getConfig( 'site', 'default' );
	}
	
	
	// Returns Site header filename (full path)
	public function readHeader()
	{
		return $this->getSiteDir() . "/inc/header.php";
	}
	
	
	// Returns Site footer filename (full path)
	public function readFooter()
	{
		return $this->getSiteDir() . "/inc/footer.php";
	}
	
	
	
	public function cleanupinput( $string ) 
	{
		$string = trim($string);
		
		$string = strip_tags($string);
		
		
		
		return $string;
	}
	
	
	
	public function loadTemplate( $template, $content_id = null )
	{
		global $snow_context;
		
		$file = $this->basedir . "/templates/" . $template . ".php";
		
		if( file_exists($file) )
			include( $file );
		else		
			return false;
	}
	
	
	public function setLogger( &$logger = null )
	{
		if( $logger instanceof isnow_logger  )
			$this->logger = $logger;
		else
			$this->logger = new snow_core_logger();
	}
	
	
	public function log( $message, $level = 3 )
	{
		if( is_null($this->logger) )
			$this->setLogger();
			
		$this->logger->log( $message, $level );
			
	}
	

}


?>