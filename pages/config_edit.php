<?php
// authenticate
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
// Read results
$f_threshold = gpc_get_int( 'export_threshold', DEVELOPER );


// update results
plugin_config_set( 'export_access_level_threshold', $f_threshold );



// redirect
print_successful_redirect( plugin_page( 'config',TRUE ) );