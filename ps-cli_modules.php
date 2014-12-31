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

	//throws fatal error
//	$controller = new AdminModulesControllerCore;
//	$controller->ajaxProcessRefreshModuleList(true);

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
			$mask = "| %3.3s | %32.32s | %12.12s | %12.12s | %12.12s |";

			printf($mask, 'ID', 'Name', 'Installed', 'Active', 'Upgradable');
			echo "\n";

			foreach( $modulesOnDisk as $module ) {
				$module->installed ? $iStat = 'Yes' : $iStat = 'No';
				$module->active ? $aStat = 'Yes' : $aStat = 'No';

				// check for updates
/*
				if ( isset($module->version_addons) && $module->version_addons ) {
					echo 'need upgrade';
				}
				else {
					echo 'up2date';
				}
*/
				if (isset($module->version_addons) && $module->version_addons) {
					$uStat = 'Yes';
				}
				else {
					$uStat = 'No';
				}


				printf($mask, "$module->id" ,
				     "$module->name" ,
				     " $iStat" ,
				     " $aStat",
				     " $uStat");
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

function disable_non_native_modules() {
	$currentStatus = (int)Configuration::get('PS_DISABLE_NON_NATIVE_MODULE');

	if ($currentStatus == 0) {
		Configuration::updateGlobalValue('PS_DISABLE_NON_NATIVE_MODULE', 1);
		echo "Non native modules successfully disabled\n";
		return true;
	}
	else {
		echo "Non native modules already disabled\n";
		return true;
	}
}

function enable_non_native_modules() {
	$currentStatus = (int)Configuration::get('PS_DISABLE_NON_NATIVE_MODULE');

	if ($currentStatus == 1) {
		Configuration::updateGlobalValue('PS_DISABLE_NON_NATIVE_MODULE', 0);
		echo "Non native modules successfully enabled\n";
		return true;
	}
	else {
		echo "Non native modules already enabled\n";
		return true;
	}
}

function disable_overrides() {
	$currentStatus = (int)Configuration::get('PS_DISABLE_OVERRIDES');

	if ($currentStatus == 0) {
		Configuration::updateGlobalValue('PS_DISABLE_OVERRIDES', 1);
		echo "Overrides successfully disabled\n";
		return true;
	}
	else {
		echo "Overrides already disabled\n";
		return true;
	}
}

function enable_overrides() {
	$currentStatus = (int)Configuration::get('PS_DISABLE_OVERRIDES');

	if ($currentStatus == 1) {
		Configuration::updateGlobalValue('PS_DISABLE_OVERRIDES', 0);
		echo "Overrides successfully enabled\n";
		return true;
	}
	else {
		echo "Overrides already enabled\n";
		return true;
	}
}

// Todo: rewrite to loop over xml modules; check if installed and upgradable; fire upgrade

function upgrade_all_modules() {	
//	$xmlModuleListContent = Tools::addonsRequest('customer');
	$raw = Tools::file_get_contents('api.prestashop.com/xml/modules_list_16.xml');
	$xmlModuleList = @simplexml_load_string($raw, null, LIBXML_NOCDATA);

	$modulesOnDisk = Module::getModulesOnDisk();

	foreach($xmlModuleList->module as $km) {

		if(! $module = Module::getInstanceByName($km->name)) {
			echo "Could not load module $km->name\n";
			continue;
		}
		echo $module->name;

		if ( $module->installed == 1 ) {
			if (isset($module->version_addons) && $module->version_addons) {
				echo "Downloading $module->name archive\n";
				_download_module_archive($module);
				die('ok'); // debug
			}
		}
		else {
			continue;
		}

		if ( Module::initUpgradeModule($module) ) {
			echo $module->name . " could be upgraded\n";

			$module->runUpgradeModule();
		}
	}
}

function upgrade_module($moduleName) {
	if ( $module = Module::getInstanceByName($moduleName) ) {
		// run upgrade process 
	}
	else {
		echo "Unknown module $moduleName\n";
		return false;
	}

}

//todo manage loggedOnAddons
function _download_module_archive($module) {

	$ret = Tools::addonsRequest('module', Array('id_module' => pSQL($module->id)));

	print_r($ret);
	die('ok');
	
	file_put_contents(
		_PS_MODULE_DIR_.$module->name.'.zip',
		Tools::addonsRequest('module', Array('id_module' => pSQL($module->id)))
	);
}

?>
