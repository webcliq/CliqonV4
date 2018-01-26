<?php
/**
Database handling
; type = 'mysql'
; server = 'localhost'
; dbname = ''
; username = ''
; password = ''
; port = '3306'
; charset = 'utf8'
*/
try {
	global $cfg; 
	$dbcfg = $cfg['database'];
	// This gets the DB Handler loaded
	loadFile('framework/core/Rb.php');
	switch($dbcfg['type']){
	    
	    case"mysql":
	    case"pgsql":
	        R::setup($dbcfg['type'].':host='.$dbcfg['server'].';dbname='.$dbcfg['dbname'],$dbcfg['username'],$dbcfg['password']);
	        R::useWriterCache(true); 
	    break;

	    case"sqlite":
	        R::setup('sqlite:'.$basedir.$dbcfg['file']);
	    break;

	    case "firebird":
	        $dsn = ['firebird:host='.$dbcfg['server'].';dbname='.$dbcfg['dbname'],$dbcfg['username'],$dbcfg['password']];
	        $clq->set('dsn', $dsn);
	    break;	    
	    case "odbc":
	        $dsn = 'odbc:'.$dbcfg['dbname'];
	        $clq->set('dsn', $dsn);
	    break;	    
	}

} catch (Exception $e) {
	Debugger::log('PDO Error: '.$e->getMessage());
};