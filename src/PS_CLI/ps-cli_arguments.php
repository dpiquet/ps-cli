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

	// associative array command => plugin object instance
	private $_commands;

	// singleton class, get the instance with getArgumentsInstance()
	private function __construct() {
		$this->create_schema();

		// do not parse arguments now as plugin must be able to extend schema
		//$this->parse_arguments();
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

			->command('cache')
				->description('Manage PrestaShop cache')
				->opt('clear-cache', 'Clear smarty cache', false)
				->opt('show-status', 'show cache configuration', false, 'string')
				->opt('disable-cache', 'Disable PrestaShop cache', false)
				->opt('enable-cache', 'Enable PrestaShop cache', false, 'string')
				->opt('cache-depth', 'Set cache depth (default 1)', false, 'integer')
				->arg('<cachetype>', 'Cache to use (fs, memcache, xcache, apc)', false)

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
					'Specify PrestaShop install path',
					false
				);

	}

	// find and run user command
	public function runArgCommand() {
		$command = $this->_arguments->getCommand();

		//check if command if handled by a plugin
		//doing it now allows command overriding
		if(is_object($this->_commands[$command])) {
			$runner = $this->_commands[$command]->getInstance();

			return  $runner->run();
		}
		else {
			die('Fatal error');
		}

		// TODO: move all those functions to plugins	
		//else try ps-cli core
		switch($command) {
			case 'core':
				$this->_parse_core_arguments($this->_arguments);
				break;
			
			case 'cache':
				$this->_parse_cache_arguments($this->_arguments);
				break;

			case 'option':
				$this->_parse_option_arguments($this->_arguments);
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

	public function show_command_usage($command, $error = false) {
		if($error) {
			echo "$error\n";
		}

		$this->_cli->writeHelp($command);
	}

	public function _show_command_usage($command, $error = false) {
		echo "DEPRECATED\n";

		$this->show_command_usage($command, $error);
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

			$status = PS_CLI_UTILS::update_configuration_value($key, $value, $successMsg, $errMsg, $notChanged);

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

	public function add_command(PS_CLI_Command $command, $handler) {
		$interface =  PS_CLI_INTERFACE::getInterface();

		if(is_object($handler)) {

			$this->_cli->command($command->name);
			$this->_cli->description($command->description);

			foreach($command->getOpts() as $opt => $fields) {
				$this->_cli->opt($opt, $fields['description'], $fields['required'], $fields['type']);
			}

			foreach($command->getArgs() as $arg => $fields) {
				$this->_cli->arg($arg, $fields['description'], $fields['required']); 
			}

			//keep track of who handles what
			$this->_commands[$command->name] = $handler;

			return true;
		}
		else {
			$interface->add_warning("Could not add command $command\n");
			return false;
		}
	}
}

?>
