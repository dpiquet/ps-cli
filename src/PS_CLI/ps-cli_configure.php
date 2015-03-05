<?php

// ps-cli configuration
class PS_CLI_CONFIGURE {

	private static $_instance = NULL;

	public $allowRoot = false;

	public $psPath = NULL;

	public $boPath = '.';

	public $psVersion = NULL;

	public $lang;

	public $verbose = false;

	public $porcelain = false;

	public $groupid;

	public $shopid;

	public $global = false;

	// singleton private constructor, get an instance with getConfigurationInstance()
	private function __construct() {
		//empty constructor
	}

	public static function getConfigurationInstance() {
		if(is_null(self::$_instance)) {
			self::$_instance = new PS_CLI_CONFIGURE();
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

		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

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
	}

	// configuration after PrestaShop core loading
	public function postload_configure() {

		$context = Context::getContext();
		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

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
			

			PS_CLI_SHOPS::set_current_shop_context($opt);
		}
	}

	public static function find_ps_root($current = NULL) {
		// we found controllers; modules; classes and !config! directory

		if(is_null($current)) {
			$current = getcwd();
		}

		$dir = opendir($current);
		if(!$dir) {
			echo "Fatal error: could not read current directory\n";
			echo "Do you have read access to the filesystem ?\n";
			return false;
		}

		$foundConfig = is_dir($current.'/config') && is_file($current.'/config/config.inc.php');
		$foundControllers = is_dir($current.'/controllers');
		$foundModules = is_dir($current.'/modules');

		if($foundConfig && $foundControllers && $foundModules) {
			return $current;
		}
		else {
			$parent = dirname($current);

			if($parent == '.') {
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
					return $this->psPath.'/'.$cur;
				}
			}
		}

		echo "Error, could not find admin directory\n";
		return false;
	}
}

?>
