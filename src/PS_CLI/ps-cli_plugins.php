<?php

/*
 * 2015 DoYouSoft
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author Damien PIQUET <piqudam@gmail.com>
 * @copyright 2015 DoYouSoft SA
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of DoYouSoft SA
*/

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
