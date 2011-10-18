<?php
/**
 * Language translation files support
 * 
 * Default support for translation using Unix gettext
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
 *
 * 
 * HOW TO?
 * 
 * -> Every time you want to discover new strings:
 * find ./ -iname "*.php" -exec xgettext -C -j -o ./locales/default.pot --keyword=__ {} \;
 * 
 * 
 * -> The first time, generate new .po files for each language
 * msginit -l en -o ./locales/en_US/LC_MESSAGES/default.po -i ./locales/default.pot
 * 
 * -> The first time, generate new .po files for each language
 * msgmerge -U ./locales/en_US/LC_MESSAGES/default.po ./locales/default.pot
 * 
 * -> Edit .po files in other directories and then run the following command
 * msgfmt default.po -o default.mo
 * 
 * 
 */



class snow_i18n
	implements isnow_i18n
{
	
	public function __construct()
	{
		// SetLocale
		$newlocale = setlocale(LC_MESSAGES, Snow::app()->getLocale(), Snow::app()->getLocale() . "." . Snow::app()->getConfig('local.codeset','UTF-8') );
		
		// ENV variable for OSX/Windows
		putenv("LANG=$newlocale");
		
		// Log actual result
		if( $newlocale != Snow::app()->getLocale() && $newlocale != Snow::app()->getLocale() . "." . Snow::app()->getConfig('local.codeset','UTF-8') )
			Snow::app()->log( "Could not set locale to " . Snow::app()->getLocale() . " but to " . $newlocale, 3);
		else
			Snow::app()->log( "Locale LC_MESSAGES now set to " . $newlocale, 1);
			
		$domains = Snow::app()->getConfig('local.domain','default');
		if( !is_array($domains) )
			$domains = array($domains);
			
		// Point to snow language directory
		$locales_dir = realpath( Snow::app()->getBaseDir() . Snow::app()->getConfig('local.dir','/locales') );
		foreach( $domains as $onedomain )
		{
			$bindtextdomain_set = bindtextdomain( $onedomain, $locales_dir );
			$bindtextdomain_codeset_set =bind_textdomain_codeset( $onedomain, Snow::app()->getConfig('local.codeset','UTF-8')); 
		}
		if( $bindtextdomain_set != $locales_dir )
			Snow::app()->log( "Locale domain could not be set to $locales_dir: " . $bindtextdomain_set, 1 );
		
		
		// Set domain
		$domain = Snow::app()->getConfig('local.domain','default');
		foreach( $domains as $onedomain )
		{
			$textdomain_set = textdomain( $onedomain );
			if( $textdomain_set != $onedomain )
				Snow::app()->log( "Text domain could not be set to $domain: " . $textdomain_set, 1 );
		}
		
		
	}
	
	public function gettext( $text )
	{
		return _( $text );
	}
	
	
	
}