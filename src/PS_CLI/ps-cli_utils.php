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

// todo: extend class Cli instead of modifying it
// 	 set exit code depending on the functions return

class PS_CLI_Utils {

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
		$configuration = PS_CLI_Configure::getConfigurationInstance();
		
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

	public static function check_user_root() {
		$configuration = PS_CLI_Configure::getConfigurationInstance();

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

	public static function update_configuration_value($key, $value) {
		$configuration = PS_CLI_Configure::getConfigurationInstance();

		if($configuration->global) {
			return Configuration::updateGlobalValue($key, $value);
		}
		else {
			return Configuration::updateValue($key, $value);
		}
	}

	public static function add_boolean_configuration_status(Cli\Table &$table, $key, $friendlyName, $since = NULL) {
		$configuration = PS_CLI_Configure::getConfigurationInstance();

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

		$configuration = PS_CLI_Configure::getConfigurationInstance();

		//if since is given and we know we're bellow, skip the value
		if($since !== NULL) {
			if(version_compare($since, $configuration->psVersion, '>')) {
				return;
			}
		}

		$line = Array($key, $friendlyName);

		$value = Configuration::get($key);

		array_push($line, $value);

		$table->addRow($line);
	}
}

?>
