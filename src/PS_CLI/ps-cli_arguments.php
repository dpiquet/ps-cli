<?php

/*
 *
 *	PS-Cli arguments class
 *
 */

class PS_CLI_ARGUMENTS {

	protected $_arguments;
	protected $_cli;
	private static $instance;

	// singleton class, get the instance with getArgumentsInstance()
	private function __construct() {
		$this->create_schema();
		$this->parse_arguments();
	}

	/* get the singleton arguments object */
	public static function getArgumentsInstance() {
		if(!isset(self::$instance))
			self::$instance = new PS_CLI_ARGUMENTS();

		return self::$instance;
	}

	//declare all available commands and options
	public function create_schema() {
		$this->_cli = Garden\Cli\Cli::Create()
			->command('modules')
				->description('Manage PrestaShop modules')
				->opt('enable', 'Enable module', false, 'string')
				->opt('disable', 'Disable module', false, 'string')
				->opt('reset', 'Reset module', false, 'string')
				->opt('list', 'List modules', false, 'string')
				->opt('install', 'Install module', false, 'string')
				->opt('uninstall', 'Uninstall module', false, 'string')
				->opt('upgrade', 'Upgrade modules from PrestaShop addons', false)
				->opt('upgrade-db', 'Run modules database upgrades', false)
				->opt('show-status', 'Show module configuration', false)
				->arg('<modulename>', 'The module to activate', true)

			->command('core')
				->description('Manage PrestaShop core')
				->opt('check-version', 'check for available updates', false)
				->opt('list-modified-files', 'List modified files', false)
				->opt('show-info', 'Show server configuration', false)
				->opt('show-version', 'Show PrestaShop version', false)
			
			->command('cache')
				->description('Manage PrestaShop cache')	
				->opt('clear-cache', 'Clear smarty cache', false)
				->opt('show-status', 'show cache configuration', false, 'string')
				->opt('disable-cache', 'Disable PrestaShop cache', false)
				->opt('enable-cache', 'Enable PrestaShop cache', false, 'string')
				->opt('cache-depth', 'Set cache depth (default 1)', false, 'integer')
				->arg('<cachetype>', 'Cache to use (fs, memcache, xcache, apc)', false)

			->command('employee')
				->description('Manage PrestaShop employees')
				->opt('list', 'List employees', false, 'boolean')
				->opt('delete', 'Delete an employee', false, 'string')
				->opt('disable', 'Disable an employee', false, 'string')
				->opt('enable', 'Enable an employee', false, 'string')
				->opt('create', 'Create an employee', false, 'string')
				->arg('<email address>', 'Employee email address', false)
				->opt('password', 'Employee password', false, 'string')
				->opt('profile', 'Employee profile', false, 'integer')
				->opt('first-name', 'Employee first name', false, 'string')
				->opt('last-name', 'Employee last name', false, 'string')
				->opt('show-status', 'Show employee configuration', false, 'boolean')

			->command('profile')
				->description('Manage PrestaShop profiles')
				->opt('list', 'List profiles', false)
				->opt('delete', 'Delete a profile', false, 'integer')
				->opt('list-permissions', 'List a profile permissions', false, 'integer')
				->arg('<ID>', 'Profile ID', false)

			->command('shop')
				->description('Control shop')
				->opt('enable', 'Turn off maintenance mode on the shop', false)
				->opt('disable', 'Turn on maintenance mode on the shop', false)

			->command('db')
				->description('Perform database operations')
				->opt('backup', 'Create a backup', false, 'boolean')
				->opt('skip-stats', 'Skip stats tables on backup', false, 'boolean')
				->opt('list', 'List backups', false, 'boolean')

			->command('email')
				->description('Manage email configuration')
				->opt('show-status', 'Show email configuration')

			->command('theme')
				->description('Manage PrestaShop themes')
				->opt('list', 'List themes', false, 'boolean')
				->opt('list-available', 'List themes', false, 'boolean')
				->opt('install', 'Install theme', false, 'integer')
				->arg('theme', 'Theme id', false)

			->command('cms')
				->description('Manage PrestaShop CMS')
				->opt('list-categories', 'List categories', false, 'boolean')
				->opt('list-pages', 'List pages', false, 'boolean')
				->opt('delete-page', 'Delete page', false, 'integer')
				->opt('disable-page', 'Disable a page', false, 'integer')
				->opt('enable-page', 'Enable a page', false, 'integer')
				->opt('enable-category', 'Enable a category', false, 'integer')
				->opt('disable-category', 'Disable a category', false, 'integer')
				->opt('create-category', 'Create a category', false, 'boolean')
				->opt('name', 'Name of the category to create', false, 'string')
				->opt('parent', 'Id of the parent category', false, 'integer')
				->opt('link-rewrite', 'Link rewrite', false, 'string')
				->opt('description', 'Description of the category', false, 'string')
				->arg('<ID>', 'Category or page ID', false, 'integer')

			->command('image')
				->description('Manage PrestaShop images')
				->opt('list', 'List images', false)
				->opt('regenerate-thumbs', 'Regenerate thumbnails', false)
				->opt('category', 'Specify images category for thumbnails regeneration (all, products, categories, manufacturers, suppliers, scenes, stores)', false, 'string')
				->opt('keep-old-images', 'Keep old images', false)
				->opt('show-status', 'Show configuration', false)

			->command('seo')
				->description('Manage SEO & URL')
				->opt('list-metas', 'List metas tags', false)
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

			->command('localization')
				->description('Manage PrestaShop localizations')
				->opt('list-languages', 'List installed languages', false)
				->opt('show-status', 'Show localization preferences', false)
				->opt('import', 'Import localization', false)
				->opt('enable', 'Enable language', false)
				->opt('disable', 'Disable language', false)
				->arg('<iso-code>', 'Iso code of language', false)

			->command('option')
				->description('Update PrestaShop preferences')
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
				)
				->opt(
					'porcelain',
					'Give porcelain output for scripting',
					false
				)
				->opt(
					'path',
					'Specify prestashop install path',
					false
				);

	}

	// find and run user command
	public function runArgCommand() {
		$command = $this->_arguments->getCommand();
		switch($command) {
			case 'modules':
				$this->_parse_modules_arguments($this->_arguments);
				break;

			case 'core':
				$this->_parse_core_arguments($this->_arguments);
				break;
			
			case 'cache':
				$this->_parse_cache_arguments($this->_arguments);
				break;

			case 'employee':
				$this->_parse_employee_arguments($this->_arguments);
				break;

			case 'profile':
				$this->_parse_profile_arguments($this->_arguments);
				break;

			case 'shop':
				$this->_parse_shop_arguments($this->_arguments);
				break;

			case 'db':
				$this->_parse_db_arguments($this->_arguments);
				break;

			case 'email':
				$this->_parse_email_arguments($this->_arguments);
				break;

			case 'theme':
				$this->_parse_theme_arguments($this->_arguments);
				break;

			case 'cms':
				$this->_parse_cms_arguments($this->_arguments);
				break;

			case 'image':
				$this->_parse_image_arguments($this->_arguments);
				break;

			case 'seo':
				$this->_parse_seo_arguments($this->_arguments);
				break;

			case 'multistore':
				$this->_parse_multistore_arguments($this->_arguments);
				break;

			case 'export':
				$this->_parse_export_arguments($this->_arguments);
				break;

			case 'ccc':
				$this->_parse_ccc_arguments($this->_arguments);
				break;

			case 'preferences':
				$this->_parse_preferences_arguments($this->_arguments);
				break;

			case 'order-preferences':
				$this->_parse_order_preferences_arguments($this->_arguments);
				break;

			case 'product-preferences':
				$this->_parse_product_preferences_arguments($this->_arguments);
				break;

			case 'customer-preferences':
				$this->_parse_customer_preferences_arguments($this->_arguments);
				break;

			case 'search-preferences':
				$this->_parse_search_preferences_arguments($this->_arguments);
				break;

			case 'store':
				$this->_parse_store_arguments($this->_arguments);
				break;

			case 'option':
				$this->_parse_option_arguments($this->_arguments);
				break;

			case 'localization':
				$this->_parse_localization_arguments($this->_arguments);
				break;

			default:
				echo "Not implemented\n";
				break;
		}

	}

	//parse given arguments
	public function parse_arguments() {
		try {
			$this->_arguments = $this->_cli->parse($GLOBALS['argv'], false);
		}
		catch (Exception $e) {
			echo $e->getMessage() . "\n";
			exit(1);
		}
	}

	//argument accessor
	public function getOpt($opt, $default = false) {
		return $this->_arguments->getOpt($opt, $default);
	}

	// command accessor
	public function getCommand() {
		return $this->_arguments->getCommand();
	}

	public function _show_command_usage($command, $error = false) {
		if($error) {
			echo "$error\n";
		}

		$this->_cli->writeHelp($command);
	}


	private function _parse_cache_arguments(Garden\Cli\Args $arguments) {
		$status = true;

		if ($opt = $arguments->getOpt('show-status', false)) {
			PS_CLI_CORE::print_cache_status();
		}
		elseif ($opt = $arguments->getOpt('disable-cache', false)) {
			PS_CLI_CORE::disable_cache();
		}
		elseif ($cache = $arguments->getOpt('enable-cache', false)) {

			if($cache == "1") { $cache = 'default'; }

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
				case 'default':
					$cacheType = 'default';
					break;
				default:
					$error = 'Cache type must be fs, memcache, xcache or apc';
					$this->_show_command_usage('cache', $error);
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
		else {
			$this->_show_command_usage('cache');
			exit(1);
		}

		return;
	}

	private function _parse_core_arguments(Garden\Cli\Args $arguments) {

		if ($arguments->getOpt('check-version', false)) {
			PS_CLI_CORE::core_check_version();
		}
		elseif ($arguments->getOpt('list-modified-files', false)) {
			PS_CLI_CORE::core_list_changed_files();
		}
		elseif($arguments->getOpt('show-info', false)) {
			PS_CLI_CORE::print_server_info();
		}
		elseif($arguments->getOpt('show-version', false)) {
			PS_CLI_CORE::core_show_version();
		}
		else {
			$this->_show_command_usage('core');
			exit(1);
		}

		return;
	}

	private function _parse_modules_arguments(Garden\Cli\Args $arguments) {

		$status = null;

		//TODO: check modulename was given, print a message otherwise
		// maybe add an else die smth ?
		if ($opt = $arguments->getOpt('enable', false)) {
			if ($otp === "1") {
				$this->_show_command_usage('modules');
				exit(1);
			}

			$status = PS_CLI_MODULES::enable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('disable', false)) {
			if ($opt === "1") {
				$this->_show_command_usage('modules');
				exit(1);
			}
			
			$status = PS_CLI_MODULES::disable_module($opt);
		}
		elseif ($opt = $arguments->getOpt('reset', false)) {
			if ($otp === "1") {
				$this->_show_command_usage('modules');
				exit(1);
			}

			$status = PS_CLI_MODULES::reset_module($opt);
		}
		elseif ($opt = $arguments->getOpt('install', false)) {
			if ($otp === "1") {
				$this->_show_command_usage('modules');
				exit(1);
			}

			$status = PS_CLI_MODULES::install_module($opt);
		}
		elseif ($opt = $arguments->getOpt('uninstall', false)) {
			if ($opt === "1") {
				$this->_show_command_usage('modules');
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

			$status = PS_CLI_UTILS::update_global_value('PS_DISABLE_OVERRIDES', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-overrides', false)) {
			$successMsg = 'modules overrides disabled';
			$errMsg = 'modules overrides could not be disabled';
			$notChanged = 'modules overrides were already disabled';

			$status = PS_CLI_UTILS::update_global_value('PS_DISABLE_OVERRIDES', false, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('enable-non-native', false)) {
			$successMsg = 'non native modules enabled';
			$errMsg = 'non native modules could not be enabled';
			$notChanged = 'non native modules were already enabled';

			$status = PS_CLI_UTILS::update_global_value('PS_DISABLE_NON_NATIVE_MODULE', false, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-non-native', false)) {
			$successMsg = 'non native modules disabled';
			$errMsg = 'non native modules could not be disabled';
			$notChanged = 'non native modules were already disabled';

			$status = PS_CLI_UTILS::update_global_value('PS_DISABLE_NON_NATIVE_MODULE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('enable-check-update', false)) {
			$successMsg = 'modules auto updates check enabled';
			$errMsg = 'modules auto updates could not be enabled';
			$notChanged = 'modules auto updates checks were already enabled';

			$status = PS_CLI_UTILS::update_global_value('PRESTASTORE_LIVE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif ($opt = $arguments->getOpt('disable-check-update', false)) {
			$successMsg = 'modules auto updates check disabled';
			$errMsg = 'modules auto updates could not be disabled';
			$notChanged = 'modules auto updates checks were already disabled';

			$status = PS_CLI_UTILS::update_global_value('PRESTASTORE_LIVE', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			$this->_show_command_usage('modules');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		// functions exits 1 on error
		exit(0);
	}

	private function _parse_employee_arguments(Garden\Cli\Args $arguments) {

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
				$this->_show_command_usage('employee');
				exit(1);
			}
			$status = PS_CLI_EMPLOYEE::delete_employee($opt);
		}

		elseif ($opt = $arguments->getOpt('disable', false)) {
			if ($opt === "1") {
				$this->_show_command_usage('employee');
				exit(1);
			}
			$status = PS_CLI_EMPLOYEE::disable_employee($opt);
		}

		elseif ($opt = $arguments->getOpt('enable', false)) {
			if ($opt === "1") {
				$this->_show_command_usage('employee');
				exit(1);
			}
			$status = PS_CLI_EMPLOYEE::enable_employee($opt);
		}

		// todo: support for all options (optin, active, defaultTab, ...)
		elseif ($email = $arguments->getOpt('create', false)) {

			if(!Validate::isEmail($email)) {
				echo "Error, $email is not a valid email address\n";
				exit(1);
			}

			$pwdError = 'You must provide a password for the employee';
			if ($password = $arguments->getOpt('password', false)) {
				if ($password === "1") {
					$this->_show_command_usage('employee', $pwdError);
					exit(1);
				}
			}
			else {
				$this->_show_command_usage('employee', $pwdError);
				exit(1);
			}

			$profileError = 'You must provide a profile for the Employee';
			if ($profile = $arguments->getOpt('profile', false)) {
				if(!Validate::isUnsignedInt($profile)) {
					$this->_show_command_usage('employee', $profileError);
					exit(1);
				}
			}
			else {
				$this->_show_command_usage('employee', $profileError);
				exit(1);
			}

			$firstnameError = 'You must specify a name with --first-name option';
			if ($firstname = $arguments->getOpt('first-name', false)) {
				if($firstname == '') {
					$this->_show_command_usage('employee', $firstnameError);
					exit(1);
				}
			}
			else {
				$this->_show_command_usage('employee', $firstnameError);
				exit(1);
			}
			
			$lastnameError = 'You must specify a last name with --last-name option';
			if($lastname = $arguments->getOpt('last-name', false)) {
				if($lastname == '') {
					$this->_show_command_usage('employee', $lastnameError);
					exit(1);
				}
			}
			else {
				$this->_show_command_usage('employee', $lastnameError);
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
				$this->_show_command_usage();
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
				if (!Validate::isUnsignedInt($profile)) {
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
			$this->_show_command_usage('employee');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		exit(0);
	}

	private function _parse_shop_arguments(Garden\Cli\Args $arguments) {

		$status = NULL;

		if($opt = $arguments->getOpt('enable', false)) {
			$successMsg = 'Maintenance mode disabled';
			$errMsg = 'Could not disable maintenance mode';
			$notChanged = 'Maintenance mode was already disabled';

			PS_CLI_UTILS::update_global_value('PS_SHOP_ENABLE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($opt = $arguments->getOpt('disable', false)) {
			$successMsg = 'Maintenance mode enabled';
			$errMsg = 'Could not enable maintenance mode';
			$notChanged = 'Maintenance mode was already enabled';

			PS_CLI_UTILS::update_global_value('PS_SHOP_ENABLE', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			$this->_show_command_usage('shop');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		exit(0);
	}

	private function _parse_db_arguments(Garden\Cli\Args $arguments) {

		if($arguments->getOpt('backup', false)) {

			$skipStats = $arguments->getOpt('skip-stats', false);

			$status = PS_CLI_DB::database_create_backup($skipStats);
		}
		elseif($arguments->getOpt('list', false)) {
			$status = PS_CLI_DB::list_database_backups();
		}
		else {
			$this->_show_command_usage('db');
			exit(1);
		}

		if($status === false) {
			exit(1);
		}
		else {
			exit(0);
		}
	}

	private function _parse_email_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('show-status', false)) {
			PS_CLI_EMAIL::show_status();
		}
		else {
			$this->_show_command_usage('email');
			exit(1);
		}

		exit(0);
	}

	private function _parse_theme_arguments(Garden\Cli\Args $arguments) {
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
			$this->_show_command_usage('theme');
			exit(1);
		}
	}

	private function _parse_profile_arguments(Garden\Cli\Args $arguments) {

		if ($opt = $arguments->getOpt('list', false)) {
			PS_CLI_PROFILE::print_profile_list();
		}
		elseif ($id = $arguments->getOpt('delete', false)) {
			$status = PS_CLI_PROFILE::delete_profile($id);
		}
		elseif ($id = $arguments->getOpt('list-permissions', false)) {
			$status = PS_CLI_PROFILE::list_permissions($id);
		}
		else {
			$this->_show_command_usage('profile');
			exit(1);
		}

		exit(0);
	}

	private function _parse_cms_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('list-categories', false)) {
			PS_CLI_CMS::list_categories();
			$status = true;
		}
		elseif($opt = $arguments->getOpt('list-pages', false)) {
			PS_CLI_CMS::list_pages();
			$status = true;
		}
		elseif($pageId = $arguments->getOpt('delete-page', false)) {
			$status = PS_CLI_CMS::delete_page($pageId);
		}
		elseif($pageId = $arguments->getOpt('disable-page', false)) {
			$status = PS_CLI_CMS::disable_page($pageId);
		}
		elseif($pageId = $arguments->getOpt('enable-page', false)) {
			$status = PS_CLI_CMS::enable_page($pageId);
		}
		elseif($catId = $arguments->getOpt('enable-category', false)) {
			$status = PS_CLI_CMS::enable_category($catId);
		}
		elseif($catId = $arguments->getOpt('disable-category', false)) {
			$status = PS_CLI_CMS::disable_category($catId);
		}
		elseif($arguments->getOpt('create-category', false)) {
			$name = $arguments->getOpt('name', false);
			$parent = $arguments->getOpt('parent', false);
			$rewrite = $arguments->getOpt('link-rewrite', false);
			$description = $arguments->getOpt('description', '');

			$status = PS_CLI_CMS::create_category($parent, $name, $rewrite, $description);
		}
		else {
			$this->_show_command_usage('cms');
			exit(1);
		}

		if($status === true) {
			exit(0);
		}
		else {
			exit(1);
		}
	}

	private function _parse_image_arguments(Garden\Cli\Args $arguments) {

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

					$this->_show_command_usage('image', $error);
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
			$this->_show_command_usage('image');
			exit(1);
		}

		exit (0);
	}

	private function _parse_seo_arguments(Garden\Cli\Args $arguments) {
		if($opt = $arguments->getOpt('list-metas', false)) {
			PS_CLI_SEO::list_metas();
		}
		elseif($arguments->getOpt('show-status', false)) {
			PS_CLI_SEO::show_status();
		}
		elseif($baseUri = $arguments->getOpt('base-uri', null)) {
			if(!Validate::isUrl($baseUri)) {
				echo "Error: '$baseUri' is not a valid URI\n";
				exit(1);
			}
			$status = PS_CLI_SEO::update_base_uri($baseUri);
		}
		else {
			$this->_show_command_usage('seo');
			exit(1);
		}

		exit(0);
	}

	private function _parse_multistore_arguments(Garden\Cli\Args $arguments) {

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
			$this->_show_command_usage('multistore');
			exit(1);
		}

		if ($status) {
			exit(0);
		}
		else exit(1);
	}

	private function _parse_export_arguments(Garden\Cli\Args $arguments) {

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
			$this->_show_command_usage('export');
			exit(1);
		}

		exit(0);
	}

	private function _parse_ccc_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('enable-html-minifier', false)) {
			$successMsg = 'HTML code reduction successfully enabled';
			$errMsg = 'Could not enable HTML code reduction';
			$notChanged = 'HTML code reduction was already enabled';

			$status = PS_CLI_UTILS::update_global_value('PS_HTML_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-html-minifier', false)) {	
			$successMsg = 'HTML code reduction successfully disabled';
			$errMsg = 'Could not disable HTML code reduction';
			$notChanged = 'HTML code reduction was already disabled';

			$status = PS_CLI_UTILS::update_global_value('PS_HTML_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('enable-js-minifier', false)) {
			$successMsg = 'JavaScript code reduction successfully enabled';
			$errMsg = 'Could not enable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already enabled';

			$status = PS_CLI_UTILS::update_global_value('PS_JS_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-js-minifier', false)) {	
			$successMsg = 'JavaScript code reduction successfully disabled';
			$errMsg = 'Could not disable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already disabled';

			$status = PS_CLI_UTILS::update_global_value('PS_JS_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			$this->_show_command_usage('ccc');
			exit(1);
		}

		if($status) 	{ exit(0); }
		else 		{ exit(1); }

	}

	private function _parse_preferences_arguments(Garden\Cli\Args $arguments) {

		if($arguments->getOpt('show-status', false)) {
			PS_CLI_PREFERENCES::show_preferences_status();
			$status = true;
		}
		else {
			$this->_show_command_usage('preferences');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}
	}
	
	private function _parse_order_preferences_arguments(Garden\Cli\Args $arguments) {

		if($arguments->getOpt('show-status', false)) {
			PS_CLI_ORDER_PREFERENCES::print_order_preferences();
			$status = true;
		}
		else {
			$this->_show_command_usage('order-preferences');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}
	}

	private function _parse_product_preferences_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status', false)) {
			PS_CLI_PRODUCT_PREFERENCES::show_status();
			$status = true;
		}
		else {
			$this->_show_command_usage('product-preferences');
			exit(1);
		}
	}

	private function _parse_customer_preferences_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status', false)) {
			PS_CLI_CUSTOMER_PREFERENCES::show_status();
			$status = true;
		}
		else {
			$this->_show_command_usage('customer-preferences');
			exit(1);
		}

		if($status) { exit(0); }
		else { exit(1); }

	}

	private function _parse_store_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status')) {
			PS_CLI_STORES::show_status();
			$status = true;
		}
		else {
			$this->_show_command_usage('store');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}
	}

	private function _parse_search_preferences_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('show-status', false)) {
			PS_CLI_SEARCH::show_status();
			$status = true;
		}
		elseif($arguments->getOpt('list-aliases', false)) {
			PS_CLI_SEARCH::list_aliases();
			$status = true;
		}
		else {
			$this->_show_command_usage('search-preferences');
			exit(1);
		}

		if($status) {
			return true;
		}
		else {
			return false;
		}
	}

	private function _parse_localization_arguments(Garden\Cli\Args $arguments) {
		if($arguments->getOpt('list-languages', false)) {
			PS_CLI_LOCALIZATION::list_languages();
		}
		elseif($arguments->getOpt('show-status', false)) {
			PS_CLI_LOCALIZATION::show_status();
		}
		elseif($id = $arguments->getOpt('enable', false)) {
			PS_CLI_LOCALIZATION::enable_language($id);
		}
		elseif($id = $arguments->getOpt('disable', false)) {
			PS_CLI_LOCALIZATION::disable_language($id);
		}
		elseif($isoCode = $arguments->getOpt('import', false)) {
			//todo: allow partial imports
			PS_CLI_LOCALIZATION::import_language($isoCode, 'all', true);
		}
		else {
			$this->_show_command_usage('localization');
			exit(1);
		}

		exit(0);
	}

	private function _parse_option_arguments(Garden\Cli\Args $arguments) {
                $key = $arguments->getOpt('option', null);
                $value = $arguments->getOpt('value', null);

		if(is_null($key)) {
			echo "Error, option argument must be set\n";
			$this->_show_command_usage('option');
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
				$this->_show_command_usage('option');
				exit(1);
			}

			if(! PS_CLI_VALIDATOR::validate_configuration_key($key, $value)) {
				echo "Error, $value is not a valid value for $key\n";
				exit(1);
			}

			if(! PS_CLI_UTILS::run_pre_hooks()) {
				echo "Error, prehooks returned errors\n";
				exit(1);
			}

			$successMsg = "Option $key successfully set to $value";
			$errMsg = "Could not update option $key with value $value";
			$notChanged = "Option $key has already value $value";

			$status = PS_CLI_UTILS::update_global_value($key, $value, $successMsg, $errMsg, $notChanged);

			PS_CLI_UTILS::run_post_hooks();
		}
		else {
			echo "Invalid action argument\n";
			$this->_show_command_usage('option');
			exit(1);
		}

                if($status) {
                        exit(0);
                }
                else {
                        exit(1);
                }
	}


}

?>
