<?php

/*
 *
 *	Abstract plugin class;
 *
 *	Extend this to create plugins
 *
 *	Plugins are singletons
 *
 */

abstract class PS_CLI_Plugin {

	protected $_deps = [];

	protected $_commands = [];

	protected $_configurationKeys = [];

	public $name;

	protected static $_instance = NULL;

	protected function __construct() {
		$this->name = get_class();
	}

	//needs at least php 5.3 :/
	final public function getInstance() {
		if(self::$_instance == NULL) {
			$class = get_called_class();

			self::$_instance = new $class();
		}

		return self::$_instance;
	}

	// add a command handled by the plugin
	final protected function register_command(PSCLI_Command $command) {
		$this->_commands[] = $command;
	}

	// called by the core to register this plugin
	final public function register_plugin() {
		//$this->_declare_dependancies();
		
		$this->_extendSchema();

		$this->_extend_validator();
	}

	final protected function _extendShema() {
		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

		foreach($this->_commands as $command) {
			$arguments->add_command($command);
		}
	}

	final protected function registerConfigurationKey($key) {
		$this->_configurationKeys[] = $key;
	}

	final protected function _extend_validator() {
		//$validator = PS_CLI_VALIDATOR
	}

	// declare prestashop dependancies (modules) so we dont try to call code that don't exists
	final protected function _declare_dependancies($deps) {
		if(!is_array($deps)) {
			return false;
		}

		$this->_deps = $deps;
	}

	final protected function get_dependancies() {
		return $this->_deps;
	}

	// this is the plugin's user code
	// must include business logic, argument parsing (from api)
	abstract public function run();

	// override this method in child class to add a validator for the option command
	public function validator() {
		return true;
	}
}

?>
