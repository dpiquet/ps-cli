<?php

#
# Prestashop cli tool
#
# FUNCTIONS:
#
#
# TODO
#	CORE
#	  - Check updates
#         - list shops
#         - upgrade database (files already updated)
#	  - controles CCC
#
#
#	TEMPLATES
#	  - list templates
#
#	INTERNALS
#	  - oop
#	  - cli arguments
#	
#

/* Define some $_SERVERS var to avoid errors */
//$_SERVER['REQUEST_URI'] = 'admin.php?id_shop=2';

//$_GET['id_shop'] = '2';
//$_POST['id_shop'] = '2';

//$_GET['setShopContext'] = 's-2';
//$_POST['setShopContext'] = 's-2';


/*=================================
|
|	Load Prestashop Core
|
\==================================**/

if (!defined('_PS_ADMIN_DIR_'))
        define('_PS_ADMIN_DIR_', getcwd());

if (!defined('PS_ADMIN_DIR'))
        define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);

/*
   Prestashop checks if config/settings.inc.php exists
   before loading. If it does not exists, it performs
   header('location'). ps-cli must check for this before
   loading prestashop core
*/

if (! file_exists(_PS_ADMIN_DIR_.'/../config/settings.inc.php') ) {
	echo "Prestashop seems not installed ! (no config/settings.inc.php found)\n";
	die();
}

require_once(_PS_ADMIN_DIR_.'/../config/config.inc.php');
require_once(_PS_ADMIN_DIR_.'/functions.php');

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
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_url.php');
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

require_once(PS_CLI_ROOT . '/PS_CLI/php-cli-tools/load-php-cli-tools.php');

// include garden-cli argument parser
require_once(PS_CLI_ROOT . '/PS_CLI/garden-cli/Args.php');
require_once(PS_CLI_ROOT . '/PS_CLI/garden-cli/Cli.php');
require_once(PS_CLI_ROOT . '/PS_CLI/garden-cli/Table.php');





// init context, etc...
PS_CLI_UTILS::ps_cli_initialize();

PS_CLI_UTILS::parse_arguments();

?>