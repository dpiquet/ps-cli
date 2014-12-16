<?php

#
# Prestashop cli tool
#
#  Author: Damien PIQUET dpiquet@doyousoft.com
#
#
# TODO
#	List modules
#	Install modules
#	Deactivate modules
#	Uninstall modules
#	Update modules
#

$_SERVER['REQUEST_URI'] = '/index.php?controller=AdminModules';

if (!defined('_PS_ADMIN_DIR_'))
        define('_PS_ADMIN_DIR_', getcwd());

if (!defined('PS_ADMIN_DIR'))
        define('PS_ADMIN_DIR', _PS_ADMIN_DIR_);

require(_PS_ADMIN_DIR_.'/../config/config.inc.php');
require(_PS_ADMIN_DIR_.'/functions.php');

function delete_module() {
	/**postProcessDelete() **/
	return true;
}

function list_modules() {
	$_GET['controller'] = 'AdminModules';
	$_GET['tab'] = 'AdminModules';

	$controller = new AdminModulesControllerCore;
	$controller->ajaxProcessRefreshModuleList(true);

	$modulesOnDisk = Module::getModulesOnDisk();
	/**

	modules on disk Objects Structure
		id
		warning
		name
		version
		tab
		displayName
		description
		author
		limited_countries (Array)
		parent_class
		is_configurable
		need_instance
		active
		trusted
		currencies
		currencies_mode
		confirmUninstall
		description_full
		additional_description
		compatibility
		nb_rates
		avg_rates
		badges
		url
		onclick_option
		version_addons (SimpleXMLElement Object)
		installed
		database_version
		interest
		enable_device
	**/

	foreach( $modulesOnDisk as $module ) {
		echo "$module->id ; $module->name ; $module->installed\n";
	}
}

list_modules();

?>
