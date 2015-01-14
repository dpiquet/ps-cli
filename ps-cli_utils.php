<?php

// todo: extend class Cli instead of modifying it
// 	 set exit code depending on the functions return

class PS_CLI_UTILS {

	public static $VERBOSE = false;
	public static $ALLOW_ROOT = false;
	public static $LANG = NULL;

	private static $_cli = false;

	public static function ps_cli_initialize() {
		self::$LANG = Configuration::get('PS_LANG_DEFAULT');

		self::_load_ps_cli_dependancies();
		self::ps_cli_init_admin_context();
	}

	private static function _load_ps_cli_dependancies() {
		require_once('php-cli-tools/load-php-cli-tools.php');

		// include garden-cli argument parser
		require_once('garden-cli/Args.php');
		require_once('garden-cli/Cli.php');
		require_once('garden-cli/Table.php');

	}

	public static function ps_cli_init_admin_context() {
		$context = Context::getContext();

		// todo: load admin list and pick from it instead of assuming there's a user '1
		$context->employee = new Employee(PS_CLI_EMPLOYEE::get_any_superadmin_id());

		$context->shop = new Shop(Configuration::get('PS_SHOP_DEFAULT'));
	}

	public static function parse_arguments() {

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
				->opt('enable-check-update', 'Enable auto check for updates', false)
				->opt('disable-check-update', 'Disable auto check for updates', false)
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

			->command('profile')
				->description('Manage PrestaShop profiles')
				->opt('list', 'List profiles', false)

			->command('shop')
				->description('Control shop')
				->opt('enable', 'Turn on maintenance mode on the shop', false)
				->opt('disable', 'Turn off maintenance mode on the shop', false)

			->command('db')
				->description('Perform database operations')
				->opt('backup', 'Create a backup', false)

			->command('theme')
				->description('Manage PrestaShop themes')
				->opt('list', 'List themes', false)
				->opt('list-available', 'List themes', false)
				->opt('install', 'Install theme', false)
				->arg('theme', 'Theme id', false)

			->command('cms')
				->description('Manage PrestaShop CMS')
				->opt('list-categories', 'List categories', false)
				->opt('list-pages', 'List pages', false)
				->arg('id', 'Category or page ID', false)

			->command('image')
				->description('Manage PrestaShop images')
				->opt('list', 'List images', false)
				->opt('regenerate-thumbs', 'Regenerate thumbnails', false)
				->opt('category', 'Specify images category (all, products, categories, manufacturers, suppliers, scenes, stores', false, 'string')
				->opt('keep-old-images', 'Keep old images', false)

			->command('url')
				->description('Manage SEO & URL')
				->opt('list-rewritings', false)

			->command('multistore')
				->description('Perform Multistore operations')
				->opt('list-shops', 'List shops')
				->opt('list-groups', 'List shop groups')
				->opt('enable-multistore', 'Enable multistore feature')
				->opt('disable-multistore', 'Disable multistore feature')

			->command('export')
				->description('Export PrestaShop data')
				->opt('categories', 'export catalog categories', false)
				->opt('csv', 'export in CSV format', false)
				->arg('data', 'Data to export (categories, products, manufacturers, suppliers, scenes, stores)', false)

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
					'lang',
					'Set the language to use',
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
				self::_parse_modules_arguments($args);
				break;

			case 'core':
				self::_parse_core_arguments($args);
				break;
			
			case 'cache':
				self::_parse_cache_arguments($args);
				break;

			case 'employee':
				self::_parse_employee_arguments($args);
				break;

			case 'profile':
				self::_parse_profile_arguments($args);
				break;

			case 'shop':
				self::_parse_shop_arguments($args);
				break;

			case 'db':
				self::_parse_db_arguments($args);
				break;

			case 'theme':
				self::_parse_theme_arguments($args);
				break;

			case 'cms':
				self::_parse_cms_arguments($args);
				break;

			case 'image':
				self::_parse_image_arguments($args);
				break;

			case 'url':
				self::_parse_url_arguments($args);
				break;

			case 'multistore':
				self::_parse_multistore_arguments($args);
				break;

			case 'export':
				self::_parse_export_arguments($args);
				break;

			default:
				echo "Not implemented\n";
				break;

		}

		return;
	}

	private static function _parse_cache_arguments(Garden\Cli\Args $arguments) {
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

	private static function _parse_core_arguments(Garden\Cli\Args $arguments) {

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

	private static function _parse_modules_arguments(Garden\Cli\Args $arguments) {

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
			if ($opt === "1") {
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
			if ($opt === "1") {
				self::_show_command_usage('modules');
				exit(1);
			}
			$status = PS_CLI_MODULES::uninstall_module($opt);
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
		elseif ($opt = $arguments->getOpt('enable-check-update', false)) {
			//todo
			exit(1);
		}
		elseif ($opt = $arguments->getOpt('disable-check-update', false)) {
			//todo
			exit(1);
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

	private static function _parse_themes_arguments($arguments) {
		return;
	}

	private static function _parse_employee_arguments(Garden\Cli\Args $arguments) {

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
			if ($email === "1") {
				self::_show_command_usage('employee');
				exit(1);
			}

			$pwdError = 'You must provide a password for the employee';
			if ($password = $arguments->getOpt('password', false)) {
				if ($password === "1") {
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
				//if($profile === "1") {
				if(!Validate::isInt($profile)) {
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
		elseif ($email = $arguments->getOpt('edit', false)) {
	
			if($email === "1") {
				echo "You must specify an email address!\n";
				self::_show_command_usage();
				exit(1);
			}

			if ($password = $arguments->getOpt('password', false)) {
				if($password === "1") {
					echo "You must specify a password with --password option\n";
					exit(1);
				}	
			}
			else {
				$password = NULL;
			}

			if ($profile = $arguments->getOpt('profile', false)) {
				//usual check === 1 cannot work with int values
				if (!Validate::isInt($profile)) {
					echo "$profile is not a valid profile id\n";
					exit(1);
				}
			}
			else {
				$profile = NULL;
			}

			if ($firstname = $arguments->getOpt('firstname', false)) {
				if ($firstname === "1") {
					echo "You must specify a name with --firstname option\n";
					exit(1);
				}
			}
			else {	
				$firstname = NULL;
			}

			if ($lastname = $arguments->getOpt('lastname', false)) {
				if ($firstname === "1") {
					echo "You must specify a name with --lastname option\n";
					exit(1);
				}
			}
			else {	
				$lastname = NULL;
			}

			$res = PS_CLI_EMPLOYEE::edit_employee($email, $password, $profile, $firstname, $lastname);

			if ($res) {
				echo "Employee $email successfully updated\n";
				exit(0);
			}
			else {
				echo "Error, could not update employee $email\n";
				exit(1);
			}

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

	private static function _parse_shop_arguments(Garden\Cli\Args $arguments) {

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

	private static function _parse_db_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('backup', false)) {
			$ret = PS_CLI_DB::database_create_backup();
	
			if ($ret === false) {
				exit(1);
			}
			else {
				echo "$ret\n";
				exit(0);
			}
		}
		else {
			self::_show_command_usage('db');
			exit(1);
		}
		
		exit(0);
	}

	private static function _parse_theme_arguments(Garden\Cli\Args $arguments) {
		if ($opt = $arguments->getOpt('list', false)) {
			PS_CLI_THEMES::print_theme_list();
			exit(0);
		}

		elseif($opt = $arguments->getOpt('list-available', false)) {
			PS_CLI_THEMES::print_available_themes();
			exit(0);
		}
		elseif($theme = $arguments->getOpt('install', false)) {
			if ($theme === "1") {
				echo "You must specify a theme to install\n";
				exit(1);
			}

			PS_CLI_THEMES::install_theme($theme);

			exit(0);
		}
		else {
			self::_show_command_usage('theme');
			exit(1);
		}
	}

	private static function _parse_profile_arguments(Garden\Cli\Args $arguments) {

		if ($opt = $arguments->getOpt('list', false)) {
			PS_CLI_PROFILE::print_profile_list();
		}
		else {
			self::_show_command_usage('profile');
			exit(1);
		}

		exit(0);
	}

	private static function _parse_cms_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('list-categories', false)) {
			PS_CLI_CMS::list_categories();
		}
		elseif($opt = $arguments->getOpt('list-pages', false)) {
			PS_CLI_CMS::list_pages();
		}
		else {
			self::_show_command_usage('cms');
			exit(1);
		}

		exit(0);
	}

	private static function _parse_image_arguments(Garden\Cli\Args $arguments) {

		if ($opt = $arguments->getOpt('list', false)) {
			PS_CLI_IMAGES::list_images();
		}
		elseif ($opt = $arguments->getOpt('regenerate-thumbs', false)) {

			if($category = $arguments->getOpt('category', false)) {
				$cats = Array(
					'categories',
					'manufacturers',
					'suppliers',
					'scenes',
					'products',
					'stores',
					'all'
				);

				if (!in_array($category, $cats)) {
					$error = '--category must be ';

					foreach ($cats as $cat) {
						$error .= $cat. ' ';
					}

					self::_show_command_usage('image', $error);
					exit(1);
				}
			}
			else { $category = 'all'; }

			if ($keepOld = $arguments->getOpt('keep-old-images', false)) {
				$deleteOldImages = false;
			}
			else { $deleteOldImages = true; }

			PS_CLI_IMAGES::regenerate_thumbnails($category, $deleteOldImages);
		}
		else {
			self::_show_command_usage('image');
			exit(1);
		}

		exit (0);
	}

	private static function _parse_url_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('list-rewritings', false)) {
			PS_CLI_URL::list_rewritings();
		}
		else {
			self::_show_command_usage('url');
			exit(1);
		}

		exit(0);
	}

	private static function _parse_multistore_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('list-shops', false)) {
			PS_CLI_MULTISTORE::list_shops();
		}
		elseif($opt = $arguments->getOpt('list-groups', false)) {
			PS_CLI_MULTISTORE::list_groups();
		}
		elseif($opt = $arguments->getOpt('enable-multistore', false)) {
			PS_CLI_MULTISTORE::enable_multistore();
		}
		elseif($opt = $arguments->getOpt('disable-multistore', false)) {
			PS_CLI_MULTISTORE::disable_multistore();
		}
		else {
			self::_show_command_usage('multistore');
			exit(1);
		}

		exit(0);
	}

	private static function _parse_export_arguments(Garden\Cli\Args $arguments) {

		if($opt = $arguments->getOpt('categories', false)) {

			PS_CLI_IMPORT::csv_export('categories');
		}
		else {
			self::_show_command_usage('export');
			exit(1);
		}
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
			$error = self::$_cli->red($error);
			echo("$error\n");
		}

		$schema = self::$_cli->getSchema($command);
		self::$_cli->writeHelp($schema);
	}
}

?>
