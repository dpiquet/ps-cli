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
		self::$LANG = Configuration::get('PS_LANG_DEFAULT');

//		self::_load_ps_cli_dependancies();
		self::ps_cli_init_admin_context();
	}

	private static function _load_ps_cli_dependancies() {
//		require_once('php-cli-tools/load-php-cli-tools.php');

		// include garden-cli argument parser
//		require_once('garden-cli/Args.php');
//		require_once('garden-cli/Cli.php');
//		require_once('garden-cli/Table.php');

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
				->opt('show-status', 'Show module configuration', false)
				->arg('<modulename>', 'The module to activate', true)

			->command('core')
				->description('Manage PrestaShop core')
				->opt('check-version', 'check for available updates', false)
				->opt('list-modified-files', 'List modified files', false)
			
			->command('cache')
				->description('Manage PrestaShop cache')	
				->opt('clear-cache', 'Clear smarty cache', false)
				->opt('show-status', 'show cache configuration', false, 'string')
				->opt('disable-cache', 'Disable PrestaShop cache', false)
				->opt('enable-cache', 'Enable PrestaShop cache', false)
				->opt('cache-depth', 'Set cache depth (default 1)', false, 'integer')
				->opt('recompile-smarty', 'Set smarty compilation (allways, never, modified)', false, 'string')
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
				->opt('show-status', 'Show employee configuration', false)

			->command('profile')
				->description('Manage PrestaShop profiles')
				->opt('list', 'List profiles', false)

			->command('shop')
				->description('Control shop')
				->opt('enable', 'Turn off maintenance mode on the shop', false)
				->opt('disable', 'Turn on maintenance mode on the shop', false)

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
				->opt('delete-page', 'Delete page', false)
				->opt('disable-page', 'Disable a page', false)
				->opt('enable-page', 'Enable a page', false)
				->arg('id', 'Category or page ID', false)

			->command('image')
				->description('Manage PrestaShop images')
				->opt('list', 'List images', false)
				->opt('regenerate-thumbs', 'Regenerate thumbnails', false)
				->opt('category', 'Specify images category (all, products, categories, manufacturers, suppliers, scenes, stores', false, 'string')
				->opt('keep-old-images', 'Keep old images', false)
				->opt('show-status', 'Show configuration', false)

			->command('url')
				->description('Manage SEO & URL')
				->opt('list-rewritings', 'List rewriting rules', false)
				->opt('show-status', 'Show configuration', false)
				->opt('base-uri', 'Set shop base URI', false, 'string')

			->command('multistore')
				->description('Perform Multistore operations')
				->opt('list-shops', 'List shops', false)
				->opt('list-groups', 'List shop groups', false)
				->opt('create-group', 'Create a shop group', false)
				->opt('enable-multistore', 'Enable multistore feature', false)
				->opt('disable-multistore', 'Disable multistore feature', false)
				->opt('active', '', false)
				->opt('share-customers', 'share customers', false, 'boolean')
				->opt('share-orders', 'share orders', false, 'boolean')
				->opt('share-stock', 'share stock', false, 'boolean')
				->opt('name', 'name', false, 'string')

			->command('export')
				->description('Export PrestaShop data')
				->opt('categories', 'export catalog categories', false)
				->opt('products', 'export products', false)
				->opt('customers', 'export customers', false)
				->opt('manufacturers', 'export manufacturers', false)
				->opt('suppliers', 'export suppliers', false)
				->opt('orders', 'export orders', false)
				->opt('csv', 'export in CSV format', false)
				->arg('data', 'Data to export (categories, products, manufacturers, suppliers, scenes, stores)', false)

			->command('ccc')
				->description('Configuration CCC (Concatenation, Compression and Cache)')
				->opt('show-status', 'Show CCC configuration', false)

			->command('store')
				->description('manage stores')
				->opt('show-status', 'Show configuration')

			->command('preferences')
				->description('Set up PrestaShop preferences')
				->opt('show-status', 'Show preferences configuration')

			->command('order-preferences')
				->description('PrestaShop orders preferences')
				->opt('show-status', 'Show current order configuration', false)

			->command('product-preferences')
				->description('PrestaShop products preferences')
				->opt('show-status', 'Show current products preferences', false)

			->command('customer-preferences')
				->description('PrestaShop customers preferences')
				->opt('show-status', 'Show current customer preferences', false)

			->command('search-preferences')
				->description('PrestaShop search preferences')
				->opt('show-status', 'Show current search configuration', false)
				->opt('list-aliases', 'List search aliases', false)

			->command('option')
				->opt('action', 'action: get or update', true, 'string')
				->opt('option', 'Option name', true, 'string')
				->opt('value', 'Option value', false, 'string')

			->command('*')
				->opt(
					'shopid',
					'Specify target shop for multistore installs',
					false,
					'integer'
				)
				->opt(
					'groupid',
					'Specify the target group id in multistore installs',
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
					'Set PrestaShop context language to use',
					false
				)
				->opt(
					'global',
					'Apply to all shops in multistore context',
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

			case 'ccc':
				self::_parse_ccc_arguments($args);
				break;

			case 'preferences':
				self::_parse_preferences_arguments($args);
				break;

			case 'order-preferences':
				self::_parse_order_preferences_arguments($args);
				break;

			case 'product-preferences':
				self::_parse_product_preferences_arguments($args);
				break;

			case 'customer-preferences':
				self::_parse_customer_preferences_arguments($args);
				break;

			case 'search-preferences':
				self::_parse_search_preferences_arguments($args);
				break;

			case 'store':
				self::_parse_store_arguments($args);
				break;

			case 'option':
				self::_parse_option_arguments($args);
				break;

			default:
				echo "Not implemented\n";
				break;
		}

		return;
	}

	private static function _parse_cache_arguments(Garden\Cli\Args $arguments) {
		$status = true;

		if ($opt = $arguments->getOpt('show-status', false)) {
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

			$status = PS_CLI_CORE::enable_cache($cache, $depth);
		}
		elseif ($opt = $arguments->getOpt('clear-cache', false)) {
			$status = PS_CLI_CORE::clear_smarty_cache();
		}
		elseif($smarty = $arguments->getOpt('recompile-smarty', false)) {
			$status = PS_CLI_CORE::smarty_template_compilation($smarty);
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
		elseif($arguments->getOpt('show-status', false)) {
			PS_CLI_MODULES::print_module_status();
			$status = true;
		}
		elseif ($opt = $arguments->getOpt('upgrade', false)) {
			$status = PS_CLI_MODULES::upgrade_all_modules();
		}
		elseif ($opt = $arguments->getOpt('upgrade-db', false)) {
			$status = PS_CLI_MODULES::upgrade_all_modules_database();
		}
		elseif ($opt = $arguments->getOpt('enable-overrides', false)) {
			$successMsg = 'modules overrides enabled';
			$errMsg = 'modules overrides could not be enabled';
			$notChanged = 'modules overrides were already enabled';

			$status = self::update_global_value('PS_DISABLE_OVERRIDES', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-overrides', false)) {
			$successMsg = 'modules overrides disabled';
			$errMsg = 'modules overrides could not be disabled';
			$notChanged = 'modules overrides were already disabled';

			$status = self::update_global_value('PS_DISABLE_OVERRIDES', false, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('enable-non-native', false)) {
			$successMsg = 'non native modules enabled';
			$errMsg = 'non native modules could not be enabled';
			$notChanged = 'non native modules were already enabled';

			$status = self::update_global_value('PS_DISABLE_NON_NATIVE_MODULE', false, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-non-native', false)) {
			$successMsg = 'non native modules disabled';
			$errMsg = 'non native modules could not be disabled';
			$notChanged = 'non native modules were already disabled';

			$status = self::update_global_value('PS_DISABLE_NON_NATIVE_MODULE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('enable-check-update', false)) {
			$successMsg = 'modules auto updates check enabled';
			$errMsg = 'modules auto updates could not be enabled';
			$notChanged = 'modules auto updates checks were already enabled';

			$status = self::update_global_value('PRESTASTORE_LIVE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-check-update', false)) {
			$successMsg = 'modules auto updates check disabled';
			$errMsg = 'modules auto updates could not be disabled';
			$notChanged = 'modules auto updates checks were already disabled';

			$status = self::update_global_value('PRESTASTORE_LIVE', false, $successMsg, $errMsg, $notChanged);
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

		if ($arguments->getOpt('list', false)) {
			$status = PS_CLI_EMPLOYEE::list_employees();
		}
		elseif($arguments->getOpt('show-status', false)) {
			PS_CLI_EMPLOYEE::print_employee_options();
			$status = true;
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
			$successMsg = 'Maintenance mode disabled';
			$errMsg = 'Could not disable maintenance mode';
			$notChanged = 'Maintenance mode was already disabled';

			self::update_global_value('PS_SHOP_ENABLE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($opt = $arguments->getOpt('disable', false)) {
			$successMsg = 'Maintenance mode enabled';
			$errMsg = 'Could not enable maintenance mode';
			$notChanged = 'Maintenance mode was already enabled';

			self::update_global_value('PS_SHOP_ENABLE', false, $successMsg, $errMsg, $notChanged);
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
			$status = true;
		}
		elseif($opt = $arguments->getOpt('list-pages', false)) {
			PS_CLI_CMS::list_pages();
			$status = true;
		}
		elseif($opt = $arguments->getOpt('delete-page', false)) {
			$status = PS_CLI_CMS::delete_page($opt);
		}
		elseif($pageId = $arguments->getOpt('disable-page', false)) {
			$status = PS_CLI_CMS::disable_page($pageId);
		}
		elseif($pageId = $arguments->getOpt('enable-page', false)) {
			$status = PS_CLI_CMS::enable_page($pageId);
		}
		else {
			self::_show_command_usage('cms');
			exit(1);
		}

		if($status === true) {
			exit(0);
		}
		else {
			exit(1);
		}
	}

	private static function _parse_image_arguments(Garden\Cli\Args $arguments) {

		if ($opt = $arguments->getOpt('list', false)) {
			PS_CLI_IMAGES::list_images();
		}
		elseif($arguments->getOpt('show-status', false)) {
			PS_CLI_IMAGES::show_status();
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
		elseif($arguments->getOpt('show-status', false)) {
			PS_CLI_URL::show_status();
		}
		elseif($baseUri = $arguments->getOpt('base-uri', null)) {
			if(!Validate::isUrl($baseUri)) {
				echo "Error: '$baseUri' is not a valid URI\n";
				exit(1);
			}
			$status = PS_CLI_URL::update_base_uri($baseUri);
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
			$status = true;
		}
		elseif($opt = $arguments->getOpt('list-groups', false)) {
			PS_CLI_MULTISTORE::list_groups();
			$status = true;
		}
		elseif($opt = $arguments->getOpt('enable-multistore', false)) {
			PS_CLI_MULTISTORE::enable_multistore();
		}
		elseif($opt = $arguments->getOpt('disable-multistore', false)) {
			PS_CLI_MULTISTORE::disable_multistore();
		}
		elseif($opt = $arguments->getOpt('create-group', false)) {

			$active = $arguments->getOpt('active', false);

			$shareCustomers = $arguments->getOpt('share-customers', false);
			$shareStock = $arguments->getOpt('share-stock', false);
			$shareOrders = $arguments->getOpt('share-orders', false);
			if($name = $arguments->getOpt('name', false)) {
				if($name == "1") {
					echo "You must specify a name with --name option\n";
					exit(1);
				}
			}
			else {
				echo "You must specify group name with --name option\n";
				exit(1);
			}

			PS_CLI_MULTISTORE:: create_group($name, $shareCustomers, $shareStock, $shareOrders, $active = true); 
		}
		else {
			self::_show_command_usage('multistore');
			exit(1);
		}

		if ($status) {
			exit(0);
		}
		else exit(1);
	}

	private static function _parse_export_arguments(Garden\Cli\Args $arguments) {

		if($opt = $arguments->getOpt('categories', false)) {
			PS_CLI_IMPORT::csv_export('categories');
		}
		elseif($opt = $arguments->getOpt('products', false)) {
			PS_CLI_IMPORT::csv_export('products');
		}
		elseif($opt = $arguments->getOpt('customers', false)) {
			PS_CLI_IMPORT::csv_export('customers');
		}
		elseif($opt = $arguments->getOpt('manufacturers', false)) {
			PS_CLI_IMPORT::csv_export('manufacturers');
		}
		elseif($opt = $arguments->getOpt('suppliers', false)) {
			PS_CLI_IMPORT::csv_export('suppliers');
		}
		elseif($opt = $arguments->getOpt('orders', false)) {
			PS_CLI_IMPORT::csv_export('orders');
		}
		else {
			self::_show_command_usage('export');
			exit(1);
		}

		exit(0);
	}

	private static function _parse_ccc_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('enable-html-minifier', false)) {
			$successMsg = 'HTML code reduction successfully enabled';
			$errMsg = 'Could not enable HTML code reduction';
			$notChanged = 'HTML code reduction was already enabled';

			$status = self::update_global_value('PS_HTML_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-html-minifier', false)) {	
			$successMsg = 'HTML code reduction successfully disabled';
			$errMsg = 'Could not disable HTML code reduction';
			$notChanged = 'HTML code reduction was already disabled';

			$status = self::update_global_value('PS_HTML_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('enable-js-minifier', false)) {
			$successMsg = 'JavaScript code reduction successfully enabled';
			$errMsg = 'Could not enable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already enabled';

			$status = self::update_global_value('PS_JS_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-js-minifier', false)) {	
			$successMsg = 'JavaScript code reduction successfully disabled';
			$errMsg = 'Could not disable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already disabled';

			$status = self::update_global_value('PS_JS_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			self::_show_command_usage('ccc');
			exit(1);
		}

		if($status) 	{ exit(0); }
		else 		{ exit(1); }

	}

	public static function _parse_preferences_arguments(Garden\Cli\Args $arguments) {

		if($arguments->getOpt('show-status', false)) {
			PS_CLI_PREFERENCES::show_preferences_status();
			$status = true;
		}
		else {
			self::_show_command_usage('preferences');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}
	}
	
	private static function _parse_order_preferences_arguments(Garden\Cli\Args $arguments) {

		if($arguments->getOpt('show-status', false)) {
			PS_CLI_ORDER_PREFERENCES::print_order_preferences();
			$status = true;
		}
		else {
			self::_show_command_usage('order-preferences');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}
	}

	private static function _parse_product_preferences_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status', false)) {
			PS_CLI_PRODUCT_PREFERENCES::show_status();
			$status = true;
		}
		else {
			self::_show_command_usage('product-preferences');
			exit(1);
		}
	}

	private static function _parse_customer_preferences_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status', false)) {
			PS_CLI_CUSTOMER_PREFERENCES::show_status();
			$status = true;
		}
		else {
			self::_show_command_usage('customer-preferences');
			exit(1);
		}

		if($status) { exit(0); }
		else { exit(1); }

	}

	public static function _parse_store_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status')) {
			PS_CLI_STORES::show_status();
			$status = true;
		}
		else {
			self::_show_command_usage('store');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}
	}

	private static function _parse_search_preferences_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status', false)) {
			PS_CLI_SEARCH::show_status();
			$status = true;
		}
		elseif($arguments->getOpt('list-aliases', false)) {
			PS_CLI_SEARCH::list_aliases();
			$status = true;
		}
		else {
			_show_command_usage('search-preferences');
			exit(1);
		}

		if($status) {
			return true;
		}
		else {
			return false;
		}
	}

	private static function _parse_option_arguments(Garden\Cli\Args $arguments) {
                $key = $arguments->getOpt('option', null);
                $value = $arguments->getOpt('value', null);

		if(is_null($key)) {
			echo "Error, option argument must be set\n";
			self::_show_command_usage('option');
			exit(1);
		}

		$action = $arguments->getOpt('action', null);

		if($action == 'get') {
			$table = new Cli\Table();

			$table->setHeaders(Array('Option name', 'Value'));

			$value = Configuration::get($key);

			$table->addRow(Array(
				$key,
				$value
				)
			);

			$table->display();

			$status = true;
		}
		elseif($action == 'update') {

			if(is_null($value)) {
				echo "Error, value argument must be set\n";
				self::_show_command_usage('option');
				exit(1);
			}

			if(! PS_CLI_VALIDATOR::validate_configuration_key($key, $value)) {
				echo "Error, $value is not a valid value for $key\n";
				exit(1);
			}

			if(!self::run_pre_hooks()) {
				echo "Error, prehooks returned errors\n";
				exit(1);
			}

			$successMsg = "Option $key successfully set to $value";
			$errMsg = "Could not update option $key with value $value";
			$notChanged = "Option $key has already value $value";

			$status = self::update_global_value($key, $value, $successMsg, $errMsg, $notChanged);

			self::run_post_hooks();
		}
		else {
			echo "Invalid action argument\n";
			self::_show_command_usage('option');
			exit(1);
		}

                if($status) {
                        exit(0);
                }
                else {
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

	public static function add_boolean_configuration_status(Cli\Table &$table, $key, $friendlyName) {
		$line = Array($key, $friendlyName);
		if(Configuration::get($key)) {
			array_push($line, 'Enabled');
		}
		else {
			array_push($line, 'Disabled');
		}

		$table->addRow($line);
	}

	public static function add_configuration_value(Cli\Table &$table, $key, $friendlyName) {
		$line = Array($key, $friendlyName);

		$value = Configuration::get($key);
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
