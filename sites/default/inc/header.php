<?php
/**
 * Website default header
 * 
 * Every pages (not special controllers) will, by default, load this document AFTER
 * execution (which allows them to set headers) and output the content.
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
 
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Snow - <?php echo Snow::app()->getPageTitle(); ?></title>
</head>
<body>
