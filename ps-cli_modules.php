<?php

#
# Modules functions
#	MODULES
#	  - disable_module
#	  - enable_module
#	  - print_module_list
#	TODO
#	  - Install modules
#	  - Uninstall modules
#	  - Update modules
#	  - Reinit modules
#	  - deactivation on tablet / mobile / computers
#	  - upgrade database (files already updated)
#	  - autoupgrade all
#	  - module details
#	  - module list formats (export csv)


function delete_module() {
	/**postProcessDelete() **/
	return true;
}

/*
	Disable modules by name
	$moduleName: string

	return bool
*/
function disable_module($moduleName) {
	$_GET['controller'] = 'AdminModules';
	$_GET['tab'] = 'AdminModules';

	if ( $module = Module::getInstanceByName($moduleName) ) {
		if ( $module->active ) {
			$module->disable();
			echo "Module $moduleName disabled\n";
			return true; /* disable does not return value */
		}
		else {
			echo "Module $moduleName is already disabled\n";
			return true;
		}
	}
	else {
		echo "Unknown module '$moduleName'\n";
		return false;
	}
}

/*
	Enable modules by name
	$moduleName: string

	return bool
*/
function enable_module($moduleName) {	
	$_GET['controller'] = 'AdminModules';
	$_GET['tab'] = 'AdminModules';

	if ( $module = Module::getInstanceByName($moduleName) ) {
		if (! $module->active ) {
			$res = $module->enable();
			if ( $res ) {
				echo "Module $moduleName enabled\n";
				return true;
			}
			else {
				echo "Error while enabling $moduleName\n";
				return false;	
			}
		}
		else {
			echo "Module '$moduleName' is already enabled\n";
			return true;
		}
	}
	else {
		echo "Unknown module '$moduleName'\n";
		return false;
	}
}

function print_module_list($status = 'all') {
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

		methods:
		- 
		
	**/

	switch($status) {

		case 'all':
			$mask = "| %3.3s | %32.32s | %12.12s | %12.12s |";

			printf($mask, 'ID', 'Name', 'Installed', 'Active');
			echo "\n";

			foreach( $modulesOnDisk as $module ) {
				$module->installed ? $iStat = 'Yes' : $iStat = 'No';
				$module->active ? $aStat = 'Yes' : $aStat = 'No';


				printf($mask, "$module->id" ,
				     "$module->name" ,
				     " $iStat" ,
				     " $aStat");
				echo "\n";
			}
			break;

		case 'installed':
			foreach ( $modulesOnDisk as $module ) {
				if ( $module->installed ) {
					echo "$module->id;" .
					     "$module->name;" .
					     "$module->installed;" .
					     "$module->active\n";
				}
			}
			break;

		case 'active':
			foreach ( $modulesOnDisk as $module ) {
				if ( $module->active ) {
					echo "$module->id;" .
					     "$module->name;" .
					     "$module->installed;" .
					     "$module->active\n";
				}
			}
			break;	

		default:
			return false;
			break;
	}
}

