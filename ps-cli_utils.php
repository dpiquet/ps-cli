<?php

// todo: extend class Cli instead of modifying it
// 	 set exit code depending on the functions return

class PS_CLI_UTILS {

	public static $VERBOSE = false;
	public static $ALLOW_ROOT = false;

	private static $_cli = false;

	public static function ps_cli_initialize() {
		self::ps_cli_init_admin_context();
	}

	public static function ps_cli_init_admin_context() {
		$context = Context::getContext();

		// todo: load admin list and pick from it instead of assuming there's a user '1
		$context->employee = new Employee(1);
	}

	public static function parse_arguments() {

		// include garden-cli argument parser
		require_once('garden-cli/Args.php');
		require_once('garden-cli/Cli.php');
		require_once('garden-cli/Table.php');

		self::$_cli = Garden\Cli\Cli::Create()
			->command('modules')
				->description('Manage PrestaShop modules')
				->opt('enable', 'Enable module', false)
				->opt('disable', 'Disable module', false)
				->opt('reset', 'Reset module', false)
				->opt('list', 'List modules', false)
				->opt('install', 'Install module', false)
				->opt('uninstall', 'Uninstall module', false)
				->opt('upgrade', 'Upgrade modules from PrestaShop addons', false)
				->opt('upgrade-db', 'Run modules database upgrades', false)
				->opt('enable-overrides', 'Enable modules overrides', false)
				->opt('disable-overrides', 'Disable modules overrides', false)
				->opt('enable-non-native', 'Enable non native modules', false)
				->opt('disable-non-native', 'Disable non native modules', false)
				->arg('<modulename>', 'The module to activate', true)

			->command('core')
				->description('Manage PrestaShop core')
				->opt('check-version', 'check for available updates', false)
				->opt('list-modified-files', 'List modified files', false)
			
			->command('cache')
				->description('Manage PrestaShop cache')	
				->opt('clear-cache', 'Clear smarty cache', false)
				->opt('cache-status', 'show cache in use', false)
				->opt('disable-cache', 'Disable PrestaShop cache', false)
				->opt('enable-cache', 'Enable PrestaShop cache', false)
				->opt('cache-depth', 'Set cache depth (default 1)', false, 'integer')
				->arg('<cachetype>', 'Cache to use (fs, memcache, xcache, apc)', false)

			->command('employee')
				->description('Manage PrestaShop employees')
				->opt('list', 'List employees', false)
				->opt('delete', 'Delete an employee', false)
				->opt('disable', 'Disable an employee', false)
				->opt('enable', 'Enable an employee', false)
				->opt('create', 'Create an employee', false)
				->arg('<email address>', 'Employee email address', false)
				->opt('password', 'Employee password', false, 'string')
				->opt('profile', 'Employee profile', false, 'string')
				->opt('first-name', 'Employee first name', false, 'string')
				->opt('last-name', 'Employee last name', false, 'string')

			->command('shop')
				->description('Control shop')
				->opt('enable', 'Enable shop', false)
				->opt('disable', 'Disable shop', false)

			->command('*')
				->opt(
					'shopid',
					'Specify target shop for multistore installs',
					false,
					'integer'
				)
				->opt(
					'verbose',
					'Enable verbose mode',
					false
				)
				->opt(
					'allow-root',
					'Allow running as root user (not recommanded)',
					false
				);

		$args = self::$_cli->parse($GLOBALS['argv']);

		if( $opt = $args->getOpt('allow-root', false) ) {
			self::$ALLOW_ROOT = true;
		}

		// do not run as root if allow-root not supplied
		self::check_user_root();
		
		if( $opt = $args->getOpt('verbose', false) ) {
			self::$VERBOSE = true;
		}

		// check if we have to switch shop id in context
		if( $opt = $args->getOpt('shopid', false) ) {
			PS_CLI_SHOPS::set_current_shop_context($opt);

			if (self::$VERBOSE) {
				echo "Changing shop id to $opt\n";
			}
		}

		$command = $args->getCommand();
		switch($command) {
			case 'modules':
				self::parse_modules_arguments($args);
				break;

			case 'core':
				self::parse_core_arguments($args);
				break;
			
			case 'cache':
				self::parse_cache_arguments($args);
				break;

			case 'employee':
				self::parse_employee_arguments($args);
				break;

			case 'shop':
				self::parse_shop_arguments($args);
				break;

			default:
				echo "Not implemented\n";
				break;

		}

		return;
	}

	private static function parse_cache_arguments(Garden\Cli\Args $arguments) {
		if ($opt = $arguments->getOpt('cache-status', false)) {
			PS_CLI_CORE::print_cache_status();
		}
		elseif ($opt = $arguments->getOpt('disable-cache', false)) {
			PS_CLI_CORE::disable_cache();
		}
		elseif ($cache = $arguments->getOpt('enable-cache', false)) {

			if ($cache === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}

			switch($cache) {
				case 'fs':
					$cacheType = 'CacheFS';
					break;
				case 'memcache':
					$cacheType = 'CacheMemcache';
					break;
				case 'xcache':
					$cacheType = 'CacheXcache';
					break;
				case 'apc':
					$cacheType = 'CacheApc';
					break;
				default:
					$error = 'Cache type must be fs, memcache, xcache or apc';
					self::_show_command_usage('cache', $error);
					exit(1);
			}

			if ($depth = $arguments->getOpt('cache-depth', false)) {
				if ($depth <= 0) {
					$error = 'cache-depth must be a positive integer';
					exit(1);
				}
			}
			else {
				$depth = 1;
			}

			PS_CLI_CORE::enable_cache($cache, $depth);
		}
		elseif ($opt = $arguments->getOpt('clear-cache', false)) {
			PS_CLI_CORE::clear_smarty_cache();
		}
		else {
			self::_show_command_usage('cache');
			exit(1);
		}

		return;
	}

	private static function parse_core_arguments(Garden\Cli\Args $arguments) {

		if ($opt = $arguments->getOpt('check-version', false)) {
			PS_CLI_CORE::core_check_version();
		}
		elseif ($opt = $arguments->getOpt('list-modified-files', false)) {
			PS_CLI_CORE::core_list_changed_files();
		}
		else {
			self::_show_command_usage('core');
			exit(1);
		}

		return;
	}

	private static function parse_modules_arguments(Garden\Cli\Args $arguments) {

		$status = null;

		//TODO: check modulename was given, print a message otherwise
		// maybe add an else die smth ?
		if ($opt = $arguments->getOpt('enable', false)) {
			if ($otp === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}

			$status = PS_CLI_MODULES::enable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('disable', false)) {
			if ($otp === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}
			
			$status = PS_CLI_MODULES::disable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('reset', false)) {
			if ($otp === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}

			$status = PS_CLI_MODULES::reset_module($opt);
		}
		elseif ($opt = $arguments->getOpt('install', false)) {
			if ($otp === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}

			$status = PS_CLI_MODULES::install_module($opt);
		}
		elseif ($opt = $arguments->getOpt('uninstall', false)) {
			if ($otp === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}
			$status PS_CLI_MODULES::uninstall_module($opt);
		}
		elseif ($opt = $arguments->getOpt('list', false)) {
			$status = PS_CLI_MODULES::print_module_list();
		}
		elseif ($opt = $arguments->getOpt('upgrade', false)) {
			$status = PS_CLI_MODULES::upgrade_all_modules();
		}
		elseif ($opt = $arguments->getOpt('upgrade-db', false)) {
			$status = PS_CLI_MODULES::upgrade_all_modules_database();
		}
		elseif ($opt = $arguments->getOpt('enable-overrides', false)) {
			$status = PS_CLI_MODULES::enable_overrides();
		}
		elseif ($opt = $arguments->getOpt('disable-overrides', false)) {
			$status = PS_CLI_MODULES::disable_overrides();
		}
		elseif ($opt = $arguments->getOpt('enable-non-native', false)) {
			$status = PS_CLI_MODULES::enable_non_native_modules();
		}
		elseif ($opt = $arguments->getOpt('disable-non-native', false)) {
			$status = PS_CLI_MODULES::disable_non_native_modules();
		}
		else {
			self::_show_command_usage('modules');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		// functions exits 1 on error
		exit(0);
	}

	private static function parse_themes_arguments($arguments) {
		return;
	}

	private static function parse_employee_arguments($arguments) {

		$status = null;

		if ($opt = $arguments->getOpt('list', false)) {
			$status = PS_CLI_EMPLOYEE::list_employees();
		}

		elseif ($opt = $arguments->getOpt('delete', false)) {
			if ($opt === "1") {
				self::_show_command_usage('employee');
				exit(1);
			}
			$status = PS_CLI_EMPLOYEE::delete_employee($opt);
		}

		elseif ($opt = $arguments->getOpt('disable', false)) {
			if ($opt === "1") {
				self::_show_command_usage('employee');
				exit(1);
			}
			$status = PS_CLI_EMPLOYEE::disable_employee($opt);
		}

		elseif ($opt = $arguments->getOpt('enable', false)) {
			if ($opt === "1") {
				self::_show_command_usage('employee');
				exit(1);
			}
			$status = PS_CLI_EMPLOYEE::enable_employee($opt);
		}

		// todo: support for all options (optin, active, defaultTab, ...)
		elseif ($email = $arguments->getOpt('create', false)) {
			if ($password === "1") {
				self::_show_command_usage('employee');
				exit(1);
			}

			$pwdError = 'You must provide a password for the employee';
			if ($password = $arguments->getOpt('password', false)) {
				if ($opt === "1") {
					self::_show_command_usage('employee', $pwdError);
					exit(1);
				}
			}
			else {
				self::_show_command_usage('employee', $pwdError);
				exit(1);
			}

			$profileError = 'You must provide a profile for the Employee';
			if ($profile = $arguments->getOpt('profile', false)) {
				if($opt === "1") {
					self::_show_command_usage('employee', $profileError);
					exit(1);
				}
			}
			else {
				self::_show_command_usage('employee', $profileError);
				exit(1);
			}

			$firstnameError = 'You must specify a name with --first-name option';
			if ($firstname = $arguments->getOpt('first-name', false)) {
				if ($opt === "1") {
					self::_show_command_usage('employee', $firstnameError);
					exit(1);
				}
			}
			else {
				self::_show_command_usage('employee', $firstnameError);
				exit(1);
			}
			
			$lastnameError = 'You must specify a last name with --last-name option';
			if($lastname = $arguments->getOpt('last-name', false)) {
				if($opt === "1") {
					self::_show_command_usage('employee', $lastnameError);
					exit(1);
				}
			}
			else {
				self::_show_command_usage('employee', $lastnameError);
				exit(1);
			}

			$status = PS_CLI_EMPLOYEE::add_employee(
				$email,
				$password,
				$profile,
				$firstname,
				$lastname
			);	
		}
		else {
			self::_show_command_usage('employee');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		exit(0);
	}

	private static function parse_shop_arguments($arguments) {

		$status = NULL;

		if($opt = $arguments->getOpt('enable', false)) {
			$status = PS_CLI_CORE::enable_shop();
		}
		elseif($opt = $arguments->getOpt('disable', false)) {
			$status = PS_CLI_CORE::disable_shop();
		}
		else {
			self::_show_command_usage('shop');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		exit(0);
	}

	public static function check_user_root() {
		if (self::$ALLOW_ROOT) {
			return;
		}

		if(!function_exists('posix_geteuid')) {
			return;
		}

		if(posix_geteuid() !== 0) {
			return;
		}

		echo "ps-cli must be run as the user running prestashop (ex: www-data)\n";
		exit(126);
	}

	private static function _show_command_usage($command, $error = false) {
		if($error) {
			$error = self::$_CLI->red($error);
			echo("$error\n");
		}

		$schema = self::$_cli->getSchema($command);
		self::$_cli->writeHelp($schema);
	}
}

?>
