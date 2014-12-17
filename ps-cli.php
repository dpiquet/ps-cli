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


/** Load Prestashop Core **/
if (!defined('_PS_ADMIN_DIR_'))
        define('_PS_ADMIN_DIR_', getcwd());

if (!defined('PS_ADMIN_DIR'))
        define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);

require(_PS_ADMIN_DIR_.'/../config/config.inc.php');
require(_PS_ADMIN_DIR_.'/functions.php');

/** Load ps-cli functions */
require_once('ps-cli_modules.php');
require_once('ps-cli_core.php');

//print_module_list('all');
//enable_module('statscheckup');
//disable_module('statscheckup');
//core_list_changed_files(); 
core_check_version();

?>
