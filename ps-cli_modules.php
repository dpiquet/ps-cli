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


class PS_CLI_MODULES {

	function delete_module() {
		/**postProcessDelete() **/
		return true;
	}

	public static function reset_module($moduleName) {

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

	public static function uninstall_module($moduleName) {
		
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

	public static function install_module($moduleName) {

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
	public static function disable_module($moduleName) {
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
	public static function enable_module($moduleName) {	
		$_GET['controller'] = 'AdminModules';
		$_GET['tab'] = 'AdminModules';

		if ( $module = Module::getInstanceByName($moduleName) ) {

			if ( ! Module::isInstalled( $moduleName ) ) {
				echo "Module $moduleName is not installed !\n";
				return false;
			}

			if (! Module::isEnabled($moduleName) ) {
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

	public static function print_module_list($status = 'all') {
		$_GET['controller'] = 'AdminModules';
		$_GET['tab'] = 'AdminModules';

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
				$table = new cli\Table();
				$table->setHeaders( Array(
					'ID',
					'Name',
					'Installed',
					'Active',
					'Upgradable'
					)
				);

				foreach( $modulesOnDisk as $module ) {
					$module->installed ? $iStat = 'Yes' : $iStat = 'No';
					$module->active ? $aStat = 'Yes' : $aStat = 'No';

					// check for updates
					if (isset($module->version_addons) && $module->version_addons) {
						$uStat = 'Yes';
					}
					else {
						$uStat = 'No';
					}

					$table->addRow( Array(
						$module->id,
						$module->name,
						$iStat,
						$aStat,
						$uStat
						)
					);
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

		if($table) {
			$table->display();
		}

		return true;
	}

	//TODO: tests
	public static function disable_non_native_modules() {
		$currentStatus = (int)Configuration::get('PS_DISABLE_NON_NATIVE_MODULE');

		if ($currentStatus == 0) {
			if (Configuration::updateGlobalValue('PS_DISABLE_NON_NATIVE_MODULE', 1)) {
				echo "Non native modules successfully disabled\n";
				return true;
			}
			else {
				echo "Error, could not disable non native modules\n";
				return false;
			}
		}
		else {
			echo "Non native modules already disabled\n";
			return true;
		}
	}

	// TODO: tests
	public static function enable_non_native_modules() {
		$currentStatus = (int)Configuration::get('PS_DISABLE_NON_NATIVE_MODULE');

		if ($currentStatus == 1) {
			if (Configuration::updateGlobalValue('PS_DISABLE_NON_NATIVE_MODULE', 0)) {
				echo "Non native modules successfully enabled\n";
				return true;
			}
			else {
				echo "Error, could not enable non native modules\n";
				return false;
			}
		}
		else {
			echo "Non native modules already enabled\n";
			return true;
		}
	}

	//TODO: tests
	public static function disable_overrides() {
		$currentStatus = (int)Configuration::get('PS_DISABLE_OVERRIDES');

		if ($currentStatus == 0) {
			if (Configuration::updateGlobalValue('PS_DISABLE_OVERRIDES', 1)) {
				echo "Overrides successfully disabled\n";
				return true;
			}
			else {
				echo "Error, could not disable overrides\n";
				return false;
			}
		}
		else {
			echo "Overrides already disabled\n";
			return true;
		}
	}

	// TODO: tests
	public static function enable_overrides() {
		$currentStatus = (int)Configuration::get('PS_DISABLE_OVERRIDES');

		if ($currentStatus == 1) {
			if (Configuration::updateGlobalValue('PS_DISABLE_OVERRIDES', 0)) {
				echo "Overrides successfully enabled\n";
				return true;
			}
			else {
				echo "Error, could not enable overrides\n";
				return false;
			}
		}
		else {
			echo "Overrides already enabled\n";
			return true;
		}
	}

	// Todo: support for bought modules (prestashop login, etc...)
	public static function upgrade_all_modules() {	

		//types:
		// addonsMustHave
		// addonsNative
		// addonsBought ? 
		
		// todo support for loggedOnAddons
		$loggedOnAddons = false;

		// Reload caches
		file_put_contents(
			_PS_ROOT_DIR_.Module::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST, 
			Tools::addonsRequest('native')
		);

		file_put_contents(
			_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST,
			Tools::addonsRequest('must-have')
		);

		if ($loggedOnAddons) {
			file_put_contents(
				_PS_ROOT_DIR_.Module::CACHE_FILE_CUSTOMER_MODULES_LIST,
				Tools::addonsRequest('customer')
			);
		}

		$xmlModuleLists = Array();

		$raw = Tools::file_get_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST);
		$xmlModuleLists[] = @simplexml_load_string($raw, null, LIBXML_NOCDATA);
		
		$raw = Tools::file_get_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_MUST_HAVE_MODULES_LIST);
		$xmlModuleLists[] = @simplexml_load_string($raw, null, LIBXML_NOCDATA);

		$moduleStore = Array();

		foreach($xmlModuleLists as $xmlModuleList) {
			foreach ($xmlModuleList->module as $km) {
				$moduleStore[] = $km;
			}
		}

		$modulesOnDisk = Module::getModulesOnDisk(true);

		$upgradeErrors = Array();

		foreach($modulesOnDisk as $module) {

		    foreach($moduleStore as $km) {

			if ( $km->name != $module->name ) {
				continue;
			}

			if (version_compare($module->version, $km->version, '<') == 0) {
				echo "$module->name: $module->version is equal to $km->version\n";
				continue;
			}

			echo "Downloading $module->name archive\n";
			$moduleArchive = self::_download_module_archive($km);
			if (! $moduleArchive ) {
				echo "Could not download $module->name update\n";
				$upgradeErrors[] = "$module->name could not be downloaded\n";
				continue;
			}

			echo "Extracting $module->name archive\n";

			if (! Tools::ZipExtract($moduleArchive, _PS_MODULE_DIR_)) {
				echo "Could not extract $module->name archive\n";	
				$upgradeErrors[] = "$module->name could not be extracted\n";
				continue;
			}
			
			// clean
			unlink($moduleArchive);
		    }
		}

		self::upgrade_all_modules_database();

		return true;
	}

	//todo manage loggedOnAddons
	private static function _download_module_archive($module) {

		if (file_exists(_PS_MODULE_DIR_.$module->name.'.zip')) {
			unlink(_PS_MODULE_DIR_.$module->name.'.zip');
		}

		$ret = file_put_contents(
			_PS_MODULE_DIR_.$module->name.'.zip',
			Tools::addonsRequest('module', Array('id_module' => pSQL($module->id)))
		);

		if ($ret) {
			return _PS_MODULE_DIR_.$module->name.'.zip';
		}
		else {
			return false;
		}

	}

	public static function upgrade_all_modules_database() {
		//reload modules from disk and perform upgrades
		$modules = Module::getModulesOnDisk();
		foreach ($modules as $module) {

			if( Module::initUpgradeModule($module) ) {

				if (!class_exists($module->name)) {
					if(!file_exists(_PS_MODULE_DIR_.$module->name.'/'.$module->name.'.php')) {
						echo "Could not find $module->name.php file\n";
						continue;
					}

					require_once(_PS_MODULE_DIR_.$module->name.'/'.$module->name.'.php');
				}

				if ( $object = new $module->name() ) {

					$object->runUpgradeModule();
					if ((count($errors_module_list = $object->getErrors()))) {
						$upgradeErrors[] = 'name: '. $module->displayName .
								'message: '. $errors_module_list;
					}

					unset($object);
				}
				else {
					echo "error, could not create object from $module->name\n";
				}
			}

			Language::updateModulesTranslations(array($module->name));
		}

		if (empty($upgradeErrors)) {
			return true;
		}
		else {
			foreach($upgradeErrors as $error) {
				echo $error['name'].': '.$error['message']."\n";
			}

			return false;
		}
	}
}

?>
