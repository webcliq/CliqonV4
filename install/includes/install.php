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
$clq->set('protocol', $protocol); // http:// or https://
$clq->set('rootpath', $rootpath); // protocol + domain.com/
$clq->set('basedir', $basedir); // d:\wwwroot\sitedir\ or /var/www/sitedir/
$clq->set('rootdir', "/");

// Get Site Config(s) 
$config = $clq->resolve('Config');
$cfg = $config->cfgReadFile('install/config/install.cfg');
$clq->set('cfg', $cfg); global $clq;
date_default_timezone_set ($cfg['site']['timezone']);
loadFile('install/idiom/idiom_'.$cfg['site']['defaultidiom'].'.php');

// Main Framework handlers
$clq->resolve('Framework');
$clq->resolve('Cliq');
$clq->resolve('Html');
// This gets the DB Handler loaded
loadFile('framework/core/Rb.php');

session_start();

$routes = $config->cfgReadFile('install/config/routes.cfg', false);
$clq->resolve('Router');

// Fired for 404 errors; must be defined before Router::serve() call
RouterHook::add("404",  function() {
	echo "Page not found";
});

Router::serve($routes);
// Startup completed