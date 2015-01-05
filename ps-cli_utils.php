<?php

class PS_CLI_UTILS {

	public static function ps_cli_initialize() {
		self::ps_cli_init_admin_context();
	}

	public static function ps_cli_init_admin_context() {
		$context = Context::getContext();

		// todo: load admin list and pick from it instead of assuming there's a user '1
		$context->employee = new Employee(1);
	}

	
	// maybe use github.com/c9s/GetOptionKit.git instead ?
	public static function parse_arguments() {
		$arguments = array_slice($GLOBALS['argv'], 1);

		print_r($arguments);

		switch ($arguments[0]) {
			case '--core':
				self::parse_core_arguments(array_slice($arguments, 1));
				break;
			case '--modules':
				self::parse_modules_arguments(array_slice($arguments, 1));
				break;
			case '--employees':
				self::parse_employees_arguments(array_slice($arguments, 1));
				break;
			case '--db':
				self::parse_db_arguments(array_slice($arguments, 1));
				break;
			case '--themes':
				self::parse_themes_arguments($array_slice($arguments, 1));
				break;
			case '--shop':
				self::parse_shop_arguments($array_slice($arguments, 1));
				break;
			default:
				self::print_usage();
				break;
		}
	}

	private static function print_usage() {

		echo "Available arguments:\n";
		echo "  --core [check-version | list-modified-files | clear-smarty-cache]\n";
		echo "  --modules\n";
		echo "  --themes\n";
		echo "  --employees\n";
		echo "  --shop\n";
		return;

	}

	private static function parse_core_arguments($arguments) {
		foreach($arguments as $arg) {
			switch($arg) {
				case 'check-version':
					PS_CLI_CORE::core_check_version();
					break;
				case 'list-modified-files':
					PS_CLI_CORE::core_list_changed_files();
					break;
				case 'clear-smarty-cache':
					PS_CLI_CORE::clear:smarty_cache();
					break;
				default:
					self::print_usage();
					die();
					break;
			}
		}

		return;
	}

	private static function parse_modules_arguments($arguments) {
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
