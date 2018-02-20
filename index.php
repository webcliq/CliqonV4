<?php
// Sets Root Directory and calls Startup
if( isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ) {
	$protocol = "https://";
} else {
	$protocol = "http://";
};

$rootpath = $protocol.$_SERVER['SERVER_NAME']."/";
$basedir = $_SERVER['DOCUMENT_ROOT']."/";

// We will use this to ensure scripts are not called from outside of the framework
define("CLIQON", true);

if(file_exists($basedir.'notinstalled')) {
	require_once 'install/includes/install.php'; 
} else {
	require_once 'includes/startup.php'; //
}
