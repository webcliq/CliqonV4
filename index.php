<?php
// Sets Root Directory and calls Startup
$rootpath = "http://".$_SERVER['SERVER_NAME']."/"; 
$basedir = $_SERVER['DOCUMENT_ROOT']."/";

// We will use this to ensure scripts are not called from outside of the framework
define("CLIQ", true);

if(file_exists($basedir.'notinstalled')) {
	require_once 'includes/install.php'; 
} else {
	require_once 'includes/startup.php'; //
}
