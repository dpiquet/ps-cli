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

// final class ?
class PS_CLI_Command {

	public $name;

	public $description;

	private $_opts = [];

	private $_args = [];

	public function __construct($name, $description = '') {
		$this->name = $name;
		$this->description = $description;
	}

	public function addArg($arg, $desc = '', $required = false) {
		if(array_key_exists($arg, $this->_args)) {
			$interface = PS_CLI_INTERFACE::getInterfaceInstance();
			$interface->add_warning("argument $arg has already been defined");
		}

		$this->_args[$arg] = Array(
			'description' => $desc,
			'required' => $required
		);

		return $this;
	}

	public function addOpt($opt, $desc = '', $required = false, $type = 'string') {
		if(array_key_exists($opt, $this->_opts)) {
			$interface = PS_CLI_INTERFACE::getInterface();
			$interface->add_warning("option $opt has already been defined");
		}

		$this->_opts[$opt] = Array(
			'description' => $desc,
			'type' => $type,
			'required' => $required
		);

		return $this;
	}

	public function getArgs() {
		return $this->_args;
	}

	public function getOpts() {
		return $this->_opts;
	}

	public function register($handler) {
		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

		$arguments->add_command($this, $handler);
	}

	public function set_description($description) {
		$this->description = $description;
	}
}

?>
