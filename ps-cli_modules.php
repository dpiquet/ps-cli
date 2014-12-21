<?php

#
# Modules functions
#	MODULES
#	  - disable_module
#	  - enable_module
#	  - print_module_list
#	  - Install modules
#	  - Uninstall modules
#	  - reset module
#
#	TODO
#	  - Update modules
#	  - deactivation on tablet / mobile / computers
#	  - upgrade database (files already updated)
#	  - autoupgrade all
#	  - module details
#	  - module list formats (export csv)
#	  
#	  - improve error messages / handling


function delete_module() {
	/**postProcessDelete() **/
	return true;
}

function reset_module($moduleName) {

	if ( $module = Module::getInstanceByName($moduleName) ) {
		if ( Validate::isLoadedObject($module) ) {
			if ( method_exists($module, 'reset') ) {
				if ( $module->reset() ) {
					echo "Module $moduleName successfully reset\n";
					return true;
				}
				else {
					echo "Cannot reset this module\n";
					return false;
				}
			}
			else {
				if ( $module->uninstall() ) {
					if ( $module->install() ) {
						echo "Module $moduleName successfully reset (uninstalled and reinstalled\n";
						return true;
					}
					else {
						echo "Could not reinstall module $moduleName\n";
						return false;
					}
				}
				else {
					echo "Could not uninstall module $moduleName\n";
					return false;
				}		
			}
		}
		else {
			echo "Could not load $moduleName object\n";
			return false;
		}
	}
	else {
		echo "Unknown module $moduleName\n";
		return false;
	}
}

function uninstall_module($moduleName) {
	
	if ( $module = Module::getInstanceByName($moduleName) ) {
		if ( ! Module::isInstalled($moduleName) ) {
			echo "module $moduleName is already uninstalled\n";
			return true;
		}

		$res = $module->uninstall();
		if ( $res ) {
			echo "module $moduleName successfully uninstalled !\n";
			return true;
		}
		else {
			echo "error, could not uninstall $moduleName\n";
			return false;
		}
	}
	else {
		echo "Unknown module $moduleName\n";
		return false;
	}
}

function install_module($moduleName) {

	if ( $module = Module::getInstanceByName($moduleName) ) {
		if ( Module::isInstalled($moduleName) ) {
			echo "module $moduleName is already installed\n";
			return true;
		}

		$res = $module->install();
		if ( $res ) {
			echo "module $moduleName successfully installed\n";
			return true;
		}
		else {
			echo "error, could not install module $moduleName\n";
			return false;
		}
	}
	else {
		echo "Unknown module $moduleName\n";
		return false;
	}
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

		if ( ! Module::isInstalled( $moduleName ) ) {
			echo "Module $moduleName is not installed !\n";
			return false;
		}

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

				// check for updates
				if ( Module::needUpgrade($module) ) {
					echo 'need upgrade';
				}
				else {
					echo 'up2date';
				}

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

