<?php

// todo: extend class Cli instead of modifying it
// 	 set exit code depending on the functions return

class PS_CLI_UTILS {

	public static $VERBOSE = false;
	public static $ALLOW_ROOT = false;
	public static $LANG = NULL;

	private static $_cli = false;

	//callback arrays
	private static $preHooks = Array();
	private static $postHooks = Array();

	public static function ps_cli_initialize() {

		ps_cli_init_admin_context();
		self::$LANG = Configuration::get('PS_LANG_DEFAULT');

		self::ps_cli_init_admin_context();
	}


	public static function ps_cli_init_admin_context() {
		$context = Context::getContext();

		// todo: load admin list and pick from it instead of assuming there's a user '1
		$context->employee = new Employee(PS_CLI_EMPLOYEE::get_any_superadmin_id());

		// some controllers die with fatal error if not set
		Cache::store('isLoggedBack'.$context->employee->id, true);

		$context->shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));

		// load a generic front controller by default
		$context->controller = new FrontController();
	}

	// Load PrestaShop core
	public static function ps_cli_load_ps_core() {

		//todo: load path
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();
		
		if (!defined('_PS_ADMIN_DIR_')) {
		//	define('_PS_ADMIN_DIR_', getcwd());
			define('_PS_ADMIN_DIR_', $configuration->boPath);
		}

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

		require_once(_PS_ADMIN_DIR_.'/../config/config.inc.php');
		require_once(_PS_ADMIN_DIR_.'/functions.php');

	}

	public static function parse_arguments() {

		$args = self::$_cli->parse($GLOBALS['argv']);

		if( $opt = $args->getOpt('allow-root', false) ) {
			self::$ALLOW_ROOT = true;
		}

		if( $opt = $args->getOpt('verbose', false) ) {
			self::$VERBOSE = true;
		}

		if($opt = $args->getOpt('global', false)) {
			$context = Context::getContext();

			$context->shop->id_shop_group = Shop::CONTEXT_ALL;

			Shop::setContext(Shop::CONTEXT_ALL);
		}

		if ($langOpt = $args->getOpt('lang', false)) {
			if( $langOpt === "1" ) {
				echo "You must specify an isocode with --lang\n";
				exit(1);
			}

			//we must check if iso code validates to avoid a die in ps code
			if (!Validate::isLanguageIsoCode($langOpt)) {
				echo "Error, $langOpt is not a valid iso code\n";
				exit(1);
			}

			$langID = Language::getIdByIso($langOpt);
			$language = new Language($langID);
			if (Validate::isLoadedObject($language)) {
				self::$LANG = $langID;
			}
			else {
				echo "Error, could not load language $langOpt\n";
				exit(1);
			}

			$context = Context::getContext();
			$context->language = $language;
		}
		else {
			//we should be reliable and set $LANG in any cases
			// anyway we'd better del it and use context instead
		}

		// check if we have to switch shop id in context
		if( $opt = $args->getOpt('shopid', false) ) {
			PS_CLI_SHOPS::set_current_shop_context($opt);

			if (self::$VERBOSE) {
				echo "Changing shop id to $opt\n";
			}
		}
		if ($opt = $args->getOpt('groupid', false)) {
			$context = Context::getContext();

			$context->shop->shop_group_id = $opt;

			Shop::setContext(Shop::CONTEXT_GROUP);
		}

		return;
	}

	public static function check_user_root() {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		if ($configuration->allowRoot) {
			return;
		}

		if(!function_exists('posix_geteuid')) {
			return;
		}

		if(posix_geteuid() !== 0) {
			return;
		}

		echo "ps-cli must be run as the user running prestashop (ex: www-data)\n";
		echo "\n";
		echo "If you really want to run it as root, add --allow-root to your command line\n";
		exit(126);
	}

	public static function update_global_value($key, $status, $successMsg, $errMsg, $leftMsg) {
		$curStatus = Configuration::getGlobalValue($key);

		if($status == $curStatus) {
			echo "Success: $leftMsg\n";
			return true;
		}

		if(Configuration::updateGlobalValue($key, $status)) {
			echo "Success: $successMsg\n";
			return true;
		}
		else {
			echo "Error: $errMsg\n";
			return false;
		}
	}

	public static function update_configuration_value($key, $status, $successMsg, $errMsg, $leftMsg) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		if($configuration->global) {
			$curStatus = Configuration::getGlobalValue($key);
		}
		else {
			$curStatus = Configuration::getValue($key);
		}

		if($status == $curStatus) {
			echo "Success: $leftMsg\n";
			return true;
		}

		if($configuration->global) {
			$updated = Configuration::updateGlobalValue($key, $value);
		}
		else {
			$updated = Configuration::updateValue($key, $value);
		}

		if(Configuration::updateGlobalValue($key, $status)) {
			echo "Success: $successMsg\n";
			return true;
		}
		else {
			echo "Error: $errMsg\n";
			return false;
		}
	
	}

	public static function add_boolean_configuration_status(Cli\Table &$table, $key, $friendlyName, $since = NULL) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		//if since is given and we know we're bellow, skip the value
		if($since !== NULL) {
			if(version_compare($since, $configuration->psVersion, '>')) {
				return;
			}
		}

		$line = Array($key, $friendlyName);

		$value = Configuration::get($key);

		if($value == "1") {
			array_push($line, 'Enabled');
		}
		else {
			array_push($line, 'Disabled');
		}

		$table->addRow($line);
	}

	public static function add_configuration_value(Cli\Table &$table, $key, $friendlyName, $since = NULL) {

		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		//if since is given and we know we're bellow, skip the value
		if($since !== NULL) {
			if(version_compare($since, $configuration->psVersion, '>')) {
				return;
			}
		}

		$line = Array($key, $friendlyName);

		$value = Configuration::get($key);

		if($value === false) {
			$value = 'Unsupported on your current prestashop version';

			if($since !== null) {
				$value .= " (requires $since version)";
			}
		}

		array_push($line, $value);

		$table->addRow($line);
	}

	public static function add_pre_hook($function, $vars = Array()) {
		$hook = Array($function, $vars);

		array_push(self::$preHooks, $hook);
	}

	public static function add_post_hook($function, $vars = Array()) {
		$hook = Array($function, $vars);

		array_push(self::$postHooks, $hook);
	}

	public static function run_pre_hooks() {
		$status = true;

		foreach(self::$preHooks as $preHook) {
			if(is_callable($preHook[0])) {
				$status &= call_user_func_array($preHook[0], $preHook[1]);
			}
			else {
				if(self::$VERBOSE) {
					echo "[WARN] ".$preHook[0]." is not a callable function\n";
				}
				// should we set return value to false ?
			}
		}

		return $status;
	}

	public static function run_post_hooks() {
		$status = true;

		foreach(self::$postHooks as $postHook) {
			if(is_callable($postHook[0])) {
				if(self::$VERBOSE) { echo "Running postHook $postHook[0]\n"; }
				$status &= call_user_func_array($postHook[0], $postHook[1]);
			}
			else {
				if(self::$VERBOSE) {
					echo "[WARN] ".$postHook[0]." is not a callable function\n";
				}
				// should we set return value to false ?
			}
		}

		return $status;
	}
}

?>
