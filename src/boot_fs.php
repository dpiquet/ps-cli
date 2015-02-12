<?php

// From the wp-cli project https://github.com/wp-cli/wp-cli

// Load ps-cli from the command line

if ( 'cli' !== PHP_SAPI) {
	echo "Only CLI access !\n";
	exit(-1);
}

define( 'PS_CLI_ROOT', dirname( __DIR__ ) );

include WP_CLI_ROOT . '/php/ps-cli.php';

?>
