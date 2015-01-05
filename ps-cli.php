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
$_SERVER['REQUEST_URI'] = 'admin.php?id_shop=2';

$_GET['id_shop'] = '2';
$_POST['id_shop'] = '2';

$_GET['setShopContext'] = 's-2';
$_POST['setShopContext'] = 's-2';


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

require_once('ps-cli_modules.php');
//require_once('ps-cli_themes.php');
require_once('ps-cli_core.php');
require_once('ps-cli_db.php');
require_once('ps-cli_utils.php');
require_once('ps-cli_employee.php');
require_once('ps-cli_shops.php');

// init context, etc...
PS_CLI_UTILS::ps_cli_initialize();

//print_module_list('all');
//enable_module('gamification');
//disable_module('gamification');
//core_list_changed_files(); 
//core_check_version();
//database_create_backup();
//uninstall_module('gamification');
//install_module('gamification');
//list_employees();
//delete_employee('cloud.strife@shinra.jp');

//list_employees();
//echo "\n";

//add_employee( 'tifa@seventhsky.jp', '123456789', 1, 'Tifa', 'Strife' );
//change_employee_password('dpiquet@doyousoft.com', '123456789');

//list_employees();

//print_theme_list();

//disable_shop();
//enable_shop();

//disable_automatic_module_update_checks();
//enable_automatic_module_update_checks();

//disable_non_native_modules();

//print_cache_status();

//enable_cache('CacheFs');
//disable_cache();
//print_cache_status();

//list_employees();

//print_module_list()

//PS_CLI_MODULES::upgrade_all_modules();

//PS_CLI_SHOPS::print_shop_list_tree();

//PS_CLI_SHOPS::set_current_shop_context(2);

//PS_CLI_MODULES::enable_module('gridhtml');

PS_CLI_UTILS::parse_arguments();

?>
