<?php

#
# Prestashop cli tool
#
# Load Ps-cli code and start execution
#

/*====================================
|
|	 Load ps-cli functions
|
\=====================================*/

require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_utils.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_arguments.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_configure.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_interface.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_plugins.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_command.php');

/*
 * Load 3d party librairies
 */
//php-cli-tools
require_once(PS_CLI_ROOT . '/lib/php-cli-tools/load-php-cli-tools.php');

//garden-cli argument parser
require_once(PS_CLI_ROOT . '/lib/garden-cli/Args.php');
require_once(PS_CLI_ROOT . '/lib/garden-cli/Cli.php');
require_once(PS_CLI_ROOT . '/lib/garden-cli/Table.php');

$conf = PS_CLI_CONFIGURE::getConfigurationInstance();
$conf->preload_configure();

$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

$arguments->parse_arguments();

//load ps core
PS_CLI_UTILS::ps_cli_load_ps_core();

$conf->postload_configure();

$interface = PS_CLI_INTERFACE::getInterface();

// todo: create a runner and export this code in it; we'll need to create an interface instance before
//find what to run
try {
	$arguments->runArgCommand();
}
catch (Exception $e) {
	$interface->add_exception($e);
}
catch (PrestaShopException $e) {
	$interface->add_exception($e);
}
catch (PrestaShopDatabaseException $e) {
	$interface->add_exception($e);
}
catch (PrestaShopModuleException $e) {
	$interface->add_exception($e);
}
catch (PrestaShopPaymentException $e) {
	$interface->add_exception($e);
}

$interface->display();

// add plugin hook there

$interface->exit_program();

?>
