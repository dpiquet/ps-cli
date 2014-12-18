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
#
#
#	TEMPLATES
#	  - list templates
#
#	USERS
#	  - Add user
#	  - delete user
#	  - list users
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

require(_PS_ADMIN_DIR_.'/../config/config.inc.php');
require(_PS_ADMIN_DIR_.'/functions.php');


/*====================================
|
|	 Load ps-cli functions
|
\=====================================*/

require_once('ps-cli_modules.php');
require_once('ps-cli_core.php');


print_module_list('all');
//enable_module('statscheckup');
//disable_module('statscheckup');
//core_list_changed_files(); 
//core_check_version();

?>
