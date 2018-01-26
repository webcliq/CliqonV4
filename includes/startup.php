<?php
// If PHP7, you may declare
// declare(strict_types = 1);

// Load Debug
require_once $basedir.'framework/logging/tracy.php';
T::enable(T::DEVELOPMENT, $basedir."log/");  // PRODUCTION or DEVELOPMENT
T::$logSeverity = E_NOTICE | E_WARNING;

// Miscellaneous functions
// Also includes subdirectories on which autoload should function
require_once $basedir.'includes/functions.php';

// Framework
loadFile('framework/Registry.php');
$clq = Registry::singleton();

// Set System variables here
$clq->set('rootpath', $rootpath);
$clq->set('basedir', $basedir);
$clq->set('rootdir', "/");

// Get Site Config(s) 
$config = $clq->resolve('Config');
$cfg = $config->cfgReadFile('config/config.cfg');
$clq->set('cfg', $cfg); global $clq;
date_default_timezone_set ($cfg['site']['timezone']);

// Database - Redbean R::static or PDO - ODBC with DSN 
loadFile('includes/database.php');

// Main Framework handlers
$clq->resolve('Cookie');
$clq->resolve('Framework');
$clq->resolve('Cliq');
$clq->resolve('Html');
$clq->resolve('Form');
$clq->resolve('Log');
// Setup Session with Security
$clq->resolve('Session');
session_start();
if(isset($_SESSION['HTTP_USER_AGENT'])) {
	if($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {
		session_regenerate_id();
		$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
	}
} else {
	$_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
}

$clq->resolve('Image');
$clq->resolve('Website');

// API Auth handling
$clq->resolve('Auth');

// Routing and Controllers
$routes = $config->cfgReadFile('config/routes.cfg', false);
$clq->resolve('Router');

// L::cLog('Startup Completed - Now Routing');
// Fired for 404 errors; must be defined before Router::serve() call
RouterHook::add("404",  function() {
	echo "Page not found";
});

Router::serve($routes);
// Startup completed