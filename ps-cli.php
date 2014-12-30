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
#	  - is installed
#         - list shops
#         - upgrade database (files already updated)
#	  - enable/disable cache
#	  - controles CCC
#	  - enable/disable maintenance mode
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
$_SERVER['REQUEST_URI'] = '';


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
require_once('ps-cli_themes.php');
require_once('ps-cli_core.php');
require_once('ps-cli_db.php');
require_once('ps-cli_utils.php');
require_once('ps-cli_employee.php');

// init context, etc...
ps_cli_initialize();

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

//list_employees();

print_theme_list();

?>
