<?php

/*
 *
 *	PS-Cli arguments class
 *
 */

class PS_CLI_Arguments {

    protected $_calledCommand = NULL;

	protected $_arguments;
	protected $_cli;
	private static $instance;

	// associative array command => plugin object instance
	private $_commands;

	// singleton class, get the instance with getArgumentsInstance()
	private function __construct() {
		$this->create_schema();
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
        $this->command = $command;

		//check if command if handled by a plugin
		//doing it now allows command overriding
		if(isset($this->_commands[$command])) {
			$runner = $this->_commands[$command]->getInstance();

			return  $runner->run();
		}
		else {
			$this->_cli->writeHelp();
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
