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


class PS_CLI_Modules extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('modules', 'Manage PrestaShop modules');
		$command->addOpt('enable', 'Enable module', false, 'string')
			->addOpt('disable', 'Disable module', false, 'string')
			->addOpt('reset', 'Reset module', false, 'string')
			->addOpt('list', 'List modules', false, 'string')
			->addOpt('install', 'Install module', false, 'string')
			->addOpt('uninstall', 'Uninstall module', false, 'string')
			->addOpt('upgrade', 'Upgrade modules from PrestaShop addons', false)
			->addOpt('upgrade-db', 'Run modules database upgrades', false)
			->addOpt('download', 'Download a module', false, 'string')
			->addOpt('show-status', 'Show module configuration', false)
            ->addArg('<modulename>', 'The module to activate', true);

        $prefCommand = new PS_CLI_Command('modules-preferences', 'Manage modules preferences');
        $prefCommand->addOpt('show-status', 'Show module configuration', false, 'boolean')
            ->addOpt('update', 'Update configuration value', false, 'boolean')
            ->addOpt('key', 'Configuration key to update', false, 'string')
            ->addOpt('value', 'Value to assign to the configuration key', false, 'string');
		
		$this->register_command($command);
		$this->register_command($prefCommand);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
        $interface = PS_CLI_Interface::getInterface();
        $command = $arguments->getCommand();

		$status = null;

		//TODO: check modulename was given, print a message otherwise
		// maybe add an else die smth ?
		if ($opt = $arguments->getOpt('enable', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('modules');
				$interface->error();
			}

			$status = $this->enable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('disable', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('modules');
				$interface->error();
			}
			
			$status = $this->disable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('reset', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('modules');
				$interface->error();
			}

			$status = $this->reset_module($opt);
		}
		elseif ($opt = $arguments->getOpt('install', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('modules');
				$interface->error();
			}

			$status = $this->install_module($opt);
		}
		elseif ($opt = $arguments->getOpt('uninstall', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('modules');
				$interface->error();
			}
			$status = $this->uninstall_module($opt);
		}
		elseif ($opt = $arguments->getOpt('list', false)) {
			$status = $this->print_module_list();
		}
		elseif($arguments->getOpt('show-status', false)) {
			$this->print_module_status();
			$status = true;
        }
        elseif($arguments->getOpt('update', false)) {
            $key = $arguments->getOpt('key', NULL);
            $value = $arguments->getOpt('value', NULL);

            if(is_null($key)) {
                $interface->error('You must provide --key with --update');
            }

            if(is_null($value)) {
                $interface->error('You must provide --value with --update');
            }

            $this->_update_configuration($key, $value); 
        }
		elseif ($opt = $arguments->getOpt('upgrade', false)) {
			$status = $this->upgrade_all_modules();
		}
		elseif ($opt = $arguments->getOpt('upgrade-db', false)) {
			$status = $this->upgrade_all_modules_database();
		}
		elseif($moduleName = $arguments->getOpt('download', false)) {
			$status = $this->download_install_module($moduleName);
		}
		elseif ($opt = $arguments->getOpt('enable-overrides', false)) {
			$successMsg = 'modules overrides enabled';
			$errMsg = 'modules overrides could not be enabled';
			$notChanged = 'modules overrides were already enabled';

			$status = PS_CLI_Utils::update_global_value('PS_DISABLE_OVERRIDES', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-overrides', false)) {
			$successMsg = 'modules overrides disabled';
			$errMsg = 'modules overrides could not be disabled';
			$notChanged = 'modules overrides were already disabled';

			$status = PS_CLI_Utils::update_global_value('PS_DISABLE_OVERRIDES', false, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('enable-non-native', false)) {
			$successMsg = 'non native modules enabled';
			$errMsg = 'non native modules could not be enabled';
			$notChanged = 'non native modules were already enabled';

			$status = PS_CLI_Utils::update_global_value('PS_DISABLE_NON_NATIVE_MODULE', false, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-non-native', false)) {
			$successMsg = 'non native modules disabled';
			$errMsg = 'non native modules could not be disabled';
			$notChanged = 'non native modules were already disabled';

			$status = PS_CLI_Utils::update_global_value('PS_DISABLE_NON_NATIVE_MODULE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('enable-check-update', false)) {
			$successMsg = 'modules auto updates check enabled';
			$errMsg = 'modules auto updates could not be enabled';
			$notChanged = 'modules auto updates checks were already enabled';

			$status = PS_CLI_Utils::update_global_value('PRESTASTORE_LIVE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-check-update', false)) {
			$successMsg = 'modules auto updates check disabled';
			$errMsg = 'modules auto updates could not be disabled';
			$notChanged = 'modules auto updates checks were already disabled';

			$status = PS_CLI_Utils::update_global_value('PRESTASTORE_LIVE', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			$arguments->show_command_usage($command);
			$interface->error();
		}

		$interface = PS_CLI_Interface::getInterface();
		if ($status === false) {
			$interface->set_ret_val(PS_CLI_Interface::RET_ERR);
		}

		$interface->success();

	}

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
							echo "Module $moduleName successfully reset (uninstalled and reinstalled)\n";
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

		// module getInstanceByName take from db; if not installed, it's not in db
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

	public static function print_module_list($status = 'all', $onDiskOnly = true) {
		$_GET['controller'] = 'AdminModules';
		$_GET['tab'] = 'AdminModules';

		$modulesOnDisk = Module::getModulesOnDisk();

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

					if($onDiskOnly && isset($module->not_on_disk)) {
						continue;
					}

					Module::isInstalled($module->name) ? $iStat = 'Yes' : $iStat = 'No';
					Module::isEnabled($module->name) ? $aStat = 'Yes' : $aStat = 'No';

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

		$interface = PS_CLI_Interface::getInterface();

		if($table) {
			//$table->display();
			$interface->add_table($table);
		}

		return true;
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
	// param $module is a module object from addonsRequest (xml object ?)
	private static function _download_module_archive(SimpleXMLElement $module) {

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

	public function print_module_status() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_DISABLE_NON_NATIVE', 'Disable non native modules');
		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_DISABLE_OVERRIDES', 'Disable all overrides');
		PS_CLI_Utils::add_boolean_configuration_status($table, 'PRESTASTORE_LIVE', 'Automatically check modules updates');

		$table->display();
    }

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {
            case 'PS_DISABLE_NON_NATIVE':
            case 'PS_DISABLE_OVERRIDES':
            case 'PRESTASTORE_LIVE':
                $validValue = Validate::isBool($value);
                break;

            default:
                $interface->error("Configuration key '$key' is not handled by this command");
                break;
        }

        if(!$validValue) {
            $interface->error("'$value' is not a valid value for configuration key '$key'");
        }

        if(PS_CLI_Utils::update_configuration_value($key, $value)) {
            $interface->success("Successfully updated $key configuration");
        }
        else {
            $interface->error("Could not updated $key configuration");
        }
    }

	// download (and extract ?) module
	public static function download_install_module($moduleName) {
		
		$raw = Tools::file_get_contents(_PS_ROOT_DIR_.Module::CACHE_FILE_DEFAULT_COUNTRY_MODULES_LIST);
		$xmlModuleLists[] = @simplexml_load_string($raw, null, LIBXML_NOCDATA);

                $moduleStore = NULL;

                foreach($xmlModuleLists as $xmlModuleList) {
			foreach ($xmlModuleList->module as $km) {

				if($km->name != $moduleName) {
					continue;
				}

				$moduleStore = $km;
			}
		}

		if(is_null($moduleStore)) {
			echo "Error, could not find $moduleName in addons store...\n";
			return false;
		}

		if(! $moduleArchive = self::_download_module_archive($moduleStore)) {
			echo "Error, could not download module\n";
			return false;
		}

		 if (! Tools::ZipExtract($moduleArchive, _PS_MODULE_DIR_)) {
			 echo "Could not extract $module->name archive\n";
			 return false;
		 }

		@unlink($moduleArchive);

		echo "Sucessfully downloaded module $moduleName\n";

		return true;
	}
}

PS_CLI_CONFIGURE::register_plugin('PS_CLI_Modules');

?>
