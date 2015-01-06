<?php

class PS_CLI_UTILS {

	public static $VERBOSE = false;

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

		$cli = Garden\Cli\Cli::Create()
			->command('modules')
				->description('Manage PrestaShop modules')
				->opt('enable', 'Enable module', false)
				->opt('disable', 'Disable module', false)
				->opt('list', 'List modules', false)
				->opt('install', 'Install module', false)
				->opt('uninstall', 'Uninstall module', false)
				->opt('upgrade', 'Upgrade modules from PrestaShop addons', false)
				->opt('upgrade-db', 'Run modules database upgrades', false)
				->opt('enable-overrides', 'Enable modules overrides', false)
				->opt('disable-overrides', 'Disable modules overrides', false)
				->opt('enable-non-native', 'Enable non native modules', false)
				->opt('disable-non-native', 'Disable non native modules', false)
				->arg('modulename', 'The module to activate', true)

			->command('core')
				->description('Manage PrestaShop core')
				->opt('clear-cache', 'Clear smarty cache', false)
				->opt('enable-shop', 'Enable Shop', false)
				->opt('disable-shop', 'Disable Shop', false)
			
			->command('cache')
				->description('Manage PrestaShop cache')	
				->opt('cache-status', 'show cache in use', false)
				->opt('disable-cache', 'Disable PrestaShop cache', false)
				->opt('enable-cache', 'Enable PrestaShop cache', false)
				->arg('cachetype', 'Cache to use (cachefs, memcache, xcache)', false)

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
				);

		$args = $cli->parse($GLOBALS['argv']);
				
		print_r($args);

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

			default:
				echo "Not implemented\n";
				break;

		}
	}


	private static function parse_core_arguments(Garden\Cli\Cli $arguments) {
		foreach($arguments as $arg) {
			switch($arg) {
				case 'check-version':
					PS_CLI_CORE::core_check_version();
					break;
				case 'list-modified-files':
					PS_CLI_CORE::core_list_changed_files();
					break;
				case 'clear-smarty-cache':
					PS_CLI_CORE::clear_smarty_cache();
					break;
				default:
					die("Invalid command\n");
					break;
			}
		}

		return;
	}

	private static function parse_modules_arguments($arguments) {

		//TODO: check modulename was given, print a message otherwise
		// maybe add an else die smth ?
		if ($opt = $arguments->getOpt('enable', false)) {
			PS_CLI_MODULES::enable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('disable', false)) {
			PS_CLI_MODULES::disable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('reset', false)) {
			PS_CLI_MODULES::reset_module($opt);
		}
		elseif ($opt = $arguments->getOpt('install', false)) {
			PS_CLI_MODULES::install_module($opt);
		}
		elseif ($opt = $arguments->getOpt('uninstall', false)) {
			PS_CLI_MODULES::uninstall_module($opt);
		}
		elseif ($opt = $arguments->getOpt('list', false)) {
			PS_CLI_MODULES::print_module_list();
		}
		elseif ($opt = $arguments->getOpt('upgrade', false)) {
			PS_CLI_MODULES::upgrade_all_modules();
		}
		elseif ($opt = $arguments->getOpt('upgrade-db', false)) {
			PS_CLI_MODULES::upgrade_all_modules_database();
		}
		elseif ($opt = $arguments->getOpt('enable-overrides', false)) {
			PS_CLI_MODULES::enable_overrides();
		}
		elseif ($opt = $arguments->getOpt('disable-overrides', false)) {
			PS_CLI_MODULES::disable_overrides();
		}
		elseif ($opt = $arguments->getOpt('enable-non-native', false)) {
			PS_CLI_MODULES::enable_non_native_modules();
		}
		elseif ($opt = $arguments->getOpt('disable-non-native', false)) {
			PS_CLI_MODULES::disable_non_native_modules();
		}


		return;
	}

	private static function parse_themes_arguments($arguments) {
		return;
	}


	private static function parse_employees_arguments($arguments) {
		return;
	}

	private static function parse_shop_arguments($arguments) {
		return;
	}

	public static function check_user_root() {
		if(!function_exists('posix_geteuid')) {
			return;
		}
		if(posix_geteuid() !== 0) {
			return;
		}

		echo "ps-cli must be run as the user running prestashop (ex: www-data)\n";
		die();
	}
}

?>
