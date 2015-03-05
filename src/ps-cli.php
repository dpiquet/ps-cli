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

require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_modules.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_themes.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_core.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_db.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_utils.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_employee.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_shops.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_profile.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_cms.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_images.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_seo.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_multistore.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_import.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_ccc.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_preferences.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_order_preferences.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_product_preferences.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_customer_preferences.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_stores.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_search.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_validator.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_arguments.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_configure.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_localization.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_email.php');

/*
 * Load librairies
 */
//php-cli-tools
require_once(PS_CLI_ROOT . '/lib/php-cli-tools/load-php-cli-tools.php');

//garden-cli argument parser
require_once(PS_CLI_ROOT . '/lib/garden-cli/Args.php');
require_once(PS_CLI_ROOT . '/lib/garden-cli/Cli.php');
require_once(PS_CLI_ROOT . '/lib/garden-cli/Table.php');

// do not run as root (unless --allow-root is given)
PS_CLI_UTILS::check_user_root();

$conf = PS_CLI_CONFIGURE::getConfigurationInstance();
$conf->preload_configure();

//load ps core
PS_CLI_UTILS::ps_cli_load_ps_core();

$conf->postload_configure();

$interface = PS_CLI_INTERFACE::getInterface();

// todo: create a runner and export this code in it; we'll need to create an interface instance before
//find what to run
$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();
try {
	$arguments->runArgCommand();
}
catch (Exception $e) {
	//echo "Got generic error from PrestaShop: ".$e->getMessage() ."\n";
	$interface->add_exception($e);
}
catch (PrestaShopException $e) {
	//echo "Error from PrestaShop core: ".$e->getMessage() . "\n";
	$interface->add_exception($e);
}
catch (PrestaShopDatabaseException $e) {
	//echo "Error from PrestaShop database: ".$e->getMessage() ."\n";
	$interface->add_exception($e);
}
catch (PrestaShopModuleException $e) {
	//echo "Error from PrestaShop module: ".$e->getMessage() . "\n";
	$interface->add_exception($e);
}
catch (PrestaShopPaymentException $e) {
	//echo "Error from PrestaShop payment: ".$e->getMessage() ."\n";
	$interface->add_exception($e);
}

$interface->display();
$interface->exit_program();

?>
