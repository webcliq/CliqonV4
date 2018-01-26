<?php
/**
* Cliqon Lite Error Handling
* 
* @author Mark Richards, Webcliq
* @version 4.0.1
*/
// Delete existing log file if it exists
if(file_exists($rootpath.'log/php_error.log')) {
	// unlink($rootpath.'log/php_error.log');	
};

// Set reporting level
ini_set('error_reporting', E_ALL & ~(E_NOTICE | E_STRICT | E_WARNING)); 	// Suitable for PHP 5.4 which includes STRICT
error_reporting(E_ALL & ~(E_NOTICE | E_STRICT | E_WARNING));
ini_set('log_errors', 1);												// enable or disable php error logging (use 'On' or 'Off')
ini_set('display_errors', 1); 											// enable or disable public display of errors (use 'On' or 'Off')
	
ini_set("error_log", $sitepath."log/php_error.log"); 					// path to server-writable log file

// Write to error_log with error_log('msg', lvl, 'filepath - again')