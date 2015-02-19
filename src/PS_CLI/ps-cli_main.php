<?php

/*
 *
 * PS-Cli main file
 *
 */

//configure ps-cli
//$psConfig = getConfigurationInstance();

// do not run as root (unless --allow-root is given)
PS_CLI_UTILS::check_user_root();

//load ps core
PS_CLI_UTILS::ps_cli_load_ps_core();

//find what to run
$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();
$arguments->runArgCommand();

// return here ?

?>
