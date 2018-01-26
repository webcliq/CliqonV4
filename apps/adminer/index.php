<?php
$basedir = $_SERVER['DOCUMENT_ROOT'];

function adminer_object() {
    // required to run any plugin
    include_once "plugins/plugin.php";
    
    // autoloader
    foreach (glob("plugins/*.php") as $filename) {
        include_once "$filename";
    }
    
    $plugins = array(
        // specify enabled plugins here
        new AdminerEditTextarea,
        new AdminerTablesFilter,
        new AdminerDumpZip,
        new AdminerJsonColumn,
        new AdminerLoginSqlite,
        new AdminerFrames
    );
       
    return new AdminerPlugin($plugins);
}

include "adminer.php";