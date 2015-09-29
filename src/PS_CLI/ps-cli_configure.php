<?php

/*
 * 2015 DoYouSoft
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Damien PIQUET <piqudam@gmail.com>
 * @copyright 2015 DoYouSoft SA
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of DoYouSoft SA
*/

// ps-cli configuration
class PS_CLI_Configure {

	private static $_instance = NULL;

	public $allowRoot = false;

	public $loadPsCore = true;
	
	public $psCoreLoaded = false;

	public $psPath = NULL;

	public $boPath = '.';

	public $psVersion = NULL;

	public $lang;

	public $verbose = false;

	public $debug = true;

	public $porcelain = false;

	public $groupid;

	public $shopid;

	public $global = false;

	public $pluginDirs = [];

	// associative array pluginName => plugin instance
	public $pluginsLoaded = [];

	// singleton private constructor, get an instance with getConfigurationInstance()
	private function __construct() {
		//empty constructor
	}

	public static function getConfigurationInstance() {
		echo("[DEPRECATED] calling getConfigurationInstance is deprecated\n");

		if(is_null(self::$_instance)) {
			self::$_instance = new PS_CLI_Configure();
		}

		return self::$_instance;
	}

	public static function getInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new PS_CLI_Configure();
		}

		return self::$_instance;
	}

	// configuration before PrestaShop core loading
	public function preload_configure() {
		//check PHP version
		if (version_compare(preg_replace('/[^0-9.]/', '', PHP_VERSION), '5.1.3', '<')) {
			echo ('You need at least PHP 5.1.3 to run PrestaShop. Your current PHP version is '.PHP_VERSION);
			exit(1);
		}

		$this->pluginDirs[] = PS_CLI_ROOT . '/plugins';

		$arguments = PS_CLI_Arguments::getInstance();

		// preconfiguration done. Load the plugins
		$this->read_plugin_directories();

		$arguments->parse_arguments();

		$this->allowRoot = $arguments->getOpt('allow-root', false);

		//check if running as root here; remove from ps-cli.php

		if($path = $arguments->getOpt('path', false)) {
			$this->psPath = $path;
		}
		else {
			$this->psPath = self::find_ps_root();

			if(!$this->psPath) {
				exit(1);
			}
		}

		//check if we can find backoffice dir from paPath
		$this->boPath = $this->find_backoffice_dir();
		if(!$this->boPath) {
			exit(1);
		}

		if($arguments->getOpt('porcelain', false)) {
			$this->porcelain = true;
		}

		if($arguments->getOpt('verbose', false)) {
			$this->verbose = true;
		}

		$this->pluginDirs[] = PS_CLI_ROOT . '/plugins';
	}

	// configuration after PrestaShop core loading
	public function postload_configure() {

		// Do not try to configure PrestaShop if it's not loaded
		if(!$this->psCoreLoaded) {
			return;
		}

		$context = Context::getContext();
		$arguments = PS_CLI_Arguments::getArgumentsInstance();

		//store PS version for later use
		$this->psVersion = _PS_VERSION_;

		// language to use
		if($lng = $arguments->getOpt('lang', false)) {
			if(Validate::isLanguageIsoCode($lng)) {

				$this->lang = Language::getIdByIso($lng);

				$language = new Language($this->lang);
				if(validate::isLoadedObject($language)) {
					$context->lang = $language;
				}
				else {
					echo "Could not load language $lng\n";
					exit(1);
				}
			}
			else {
				echo "Warning: $lng is not a valid iso code\n";
				echo "Using default lang\n";

				$this->lang = Configuration::get('PS_LANG_DEFAULT');
				$language = new Language($this->lang);

				$context->lang = $language;
			}
		}
		else {
			$this->lang = Configuration::get('PS_LANG_DEFAULT');
			$language = new Language($this->lang);

			if(!Validate::isLoadedObject($language)) {
				echo "Fatal error: could not load default language !\n";
				exit(1);
			}

			$context->lang = $language;
		}

		// do we set global ?
		if($arguments->getOpt('global', false)) {
			$context->shop->id_shop_group = Shop::CONTEXT_ALL;
			Shop::setContext(Shop::CONTEXT_ALL);

			$this->global = true;
		}

		if($opt = $arguments->getOpt('groupid', false)) {

			//Check if we have set global before as it is non sense
			if($context->shop->id_shop_group === Shop::CONTEXT_ALL) {
				echo "You can not specify --global with --groupid !\n";
				exit(1);
			}

			if(Validate::isUnsignedInt($opt)) {
				Shop::setContext(Shop::CONTEXT_GROUP);

				$context->shop->shop_group_id = $opt;
			}
			else {
				echo "$opt is not a valid shop id\n";
				exit(1);
			}
		}

		if($opt = $arguments->getOpt('shopid')) {
			if($context->shop->id_shop_group === Shop::CONTEXT_ALL) {
				echo "You can not specify --shopid with --global";
				exit(1);
			}

			//todo: check if we are on group context
			

			self::set_current_shop_context($opt);
		}

		// activate classes autoload 
		#require_once( PS_ADMIN_DIR . '/../config/autoload.php' );
	}

	public static function find_ps_root($current = NULL) {
		// we found controllers; modules; classes and !config! directory

		if(is_null($current)) {
			$current = getcwd();
		}

		$foundConfig = is_dir($current.'/config') && is_file($current.'/config/config.inc.php');
		$foundControllers = is_dir($current.'/controllers');
		$foundModules = is_dir($current.'/modules');

		if($foundConfig && $foundControllers && $foundModules) {
			return $current;
		}
		else {
			$parent = dirname($current);

			// todo: works only with linux
			if($parent == '/') {
				echo "Could not find prestashop install !\n";
				return false;
			}

			$current = self::find_ps_root($parent);
		}

		return $current;
	}

	public function find_backoffice_dir() {
		if(is_null($this->psPath)) {
			echo "Fatal error, could not find PrestaShop installation dir !\n";
			exit(1);
		}
		
		$dir = opendir($this->psPath);
		if(!$dir) {
			echo "Fatal error: could not read current directory\n";
			echo "Do you have read access to the filesystem ?\n";
			return false;
		}

		while($cur = readdir($dir)) {
			if(is_dir($this->psPath.'/'.$cur)) {
				if(file_exists($this->psPath.'/'.$cur.'/get-file-admin.php')) {
					if($this->debug) {
						PS_CLI_Interface::display_line("Found backoffice in $this->psPath/$cur");
					}
					return $this->psPath.'/'.$cur;
				}
			}
		}

		echo "Error, could not find admin directory\n";
		return false;
	}

	public static function register_plugin($pluginClass) {
		$configuration = self::getConfigurationInstance();
		$interface = PS_CLI_INTERFACE::getInterface();

		if(!class_exists($pluginClass)) { return false; }

		$pluginInstance = $pluginClass::getInstance();

		if(is_subclass_of($pluginInstance, 'PS_CLI_Plugin')) {
			
			if($configuration->debug) {
				$interface->display_line("Registering plugin $pluginClass");
			}

			$configuration->_register_plugin($pluginClass, $pluginInstance);

			return true;
		}
		else {
			$interface->add_warning("Invalid load_plugin call !");
			return false;
		}
	}

	private function _register_plugin($pluginClass, $pluginInstance) {
		$commands = $pluginInstance->getCommands();
		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

		foreach($commands as $command) {
			$arguments->add_command($command, $pluginInstance);
		}

		$this->pluginsLoaded[$pluginClass] = $pluginInstance;
	}

	public function read_plugin_directories() {

		foreach($this->pluginDirs as $dir) {
			if($this->debug) {
				echo "scanning $dir for plugins\n";
			}

			$fh = opendir($dir);

			while($cur = readdir($fh)) {

				if(!preg_match('/\.php$/', $cur)) {
					continue;
				}

				if(is_file($dir.'/'.$cur) && is_readable($dir.'/'.$cur)) {
					if($this->verbose) {
						echo "Loading $cur plugin file\n";
					}
					include_once($dir.'/'.$cur);
				}
			}

			closedir($fh);
		}
	}


	//TODO: in progress, must be finished
	public static function set_current_shopgroup_context($idGroup) {
		$context = Context::getContext();

		if (!Validate::isLoadedObject($context->shop)) {
			$context->shop = new Shop();
		}
		
		$context->shop->id_shop_group = $idGroup;

		return true;
	}

	public static function set_current_shop_context($idShop) {

		//used by Tools::getValue
		$_GET['id_shop'] = $idShop;
		$_POST['id_shop'] = $idShop;

		Shop::setContext(Shop::CONTEXT_SHOP, $idShop);

		$context = Context::getContext();
		$context->shop = new Shop($idShop);

		if(Validate::isLoadedObject($context->shop)) {
			return true;
		}
		else {
			echo "Error, could not set current shop ID\n";
			return false;
		}
	}
}

?>
