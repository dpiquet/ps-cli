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

	protected $_commands = [];

	protected static $_instances = array();

	/**
	 * Extend the schema from the constructor
	 * by creating PS_CLI_Command objects and
	 * registering them by $this->register_command(PS_CLI_Command Object Instance)
	 */
	protected function __construct() {

	}

	//needs at least php 5.3
	final public function getInstance() {
		$class = get_called_class();

		if(!isset(self::$_instances[$class])) {
			//$class = get_called_class();

			self::$_instances[$class] = new $class();
		}

		return self::$_instances[$class];
	}

	// add a command handled by the plugin
	final protected function register_command(PS_CLI_Command $command) {
		$this->_commands[] = $command;
	}

	// plugin's commands accessor, used by the core to load the plugin
	final public function getCommands() {
		return $this->_commands;
	}

	/**
	 *
	 * this is the plugin's user code
	 * must include logic and argument parsing (using arguments class)
	 *
	 */
	abstract public function run();
}

?>
