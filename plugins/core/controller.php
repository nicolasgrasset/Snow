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
	
	public function __construct( $name )
	{
		$this->name = $name;
		
		// Run default action
		$this->action();
		
	}
	
	protected function action()
	{
		// Load the controller file, or render it if still running legacy
		if( Snow::app()->getConfig("controllers.enabled", false) )
			include( Snow::app()->getControllerFileName( $this->name ) );
		else
			$this->render( $this->name );
		
	}
	
	
	private function render( $name, Array $data = null, Array $header = null )
	{
		$controllersEnabled = Snow::app()->getConfig("controllers.enabled", false);
		
		// Get view
		ob_start();
		Snow::app()->loadView( $name, $data, null, $controllersEnabled );
		$page = ob_get_contents();
		ob_end_clean();
		
		// Output with gz compression
		if( Snow::app()->getConfig( 'ob_gzhandler', 'true' ) == 'true' )
			ob_start('ob_gzhandler');
		
		$this->loadHeader( $controllersEnabled );
		echo $page;
		$this->loadFooter( $controllersEnabled );
	}
	
	
	private function renderPartial( $name, Array $data = null )
	{
		// Output with gz compression
		if( Snow::app()->getConfig( 'ob_gzhandler', 'true' ) == 'true' )
			ob_start('ob_gzhandler');
		
		Snow::app()->loadView( $name, $data );
		
	}
	
	
	protected function loadHeader( $controllersEnabled = true )
	{
		if( $controllersEnabled )
			Snow::app()->loadView( "layout/header" );
		else
			include( Snow::app()->readHeader() );
	}
	
	
	protected function loadFooter( $controllersEnabled = true )
	{
		if( $controllersEnabled )
			Snow::app()->loadView( "layout/footer" );
		else
			include( Snow::app()->readFooter() );
	}
	
	
	public function isAjaxRequest() {
	
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == "xmlhttprequest") ? true : false;
	
	}
}

