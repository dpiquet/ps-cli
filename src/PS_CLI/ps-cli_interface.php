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

class PS_CLI_Interface {

	const RET_OK 		= 0;
	const RET_WARN 		= 0;
	const RET_ERR 		= 1;
	const RET_EXCEPTION 	= 1;
	const RET_ARGERR 	= 1;

	// warnings Array
	private $warnings;

	// errors array
	private $errors;

	private $exceptions;

	// program return value
	private $retVal;

	private $buffer;

	private static $_instance = NULL;

	// singleton; get an instance with getInterface()
	private function __construct() {
		$this->warnings = Array();
		$this->errors = Array();
		$this->exceptions = Array();
		$this->retVal = self::RET_OK;
		$this->buffer = '';
	}

	public static function getInterface() {
		if(is_null(self::$_instance)) {
			self::$_instance = new PS_CLI_INTERFACE();
		}

		return self::$_instance;
	}

	public function add_warning($warnMsg) {
		$this->warnings[] = $warnMsg;

		$this->retVal |= self::RET_WARN;
	}

	public function add_err($errMsg) {
		$this->errors[] = $errMsg;

		$this->retVal |= self::RET_ERR;
	}

	public function add_exception($e, $die = true) {
		$this->exceptions[] = $e;

		if ($die === true) { $this->exception_quit(); }

		$this->retVal |= self::RET_EXCEPTION;
	}

	public function exception_quit() {
		foreach ($this->exceptions as $e) {
			echo "Got exception " . $e->getMessage() . "\n";
		}

		exit(self::RET_EXCEPTION);
	}

	public function add_content($content) {
		$this->buffer .= $content;
	}

	public function display_line($line) {
		echo "$line\n";
	}

	public function set_ret_val($val, $force = false) {
		if($force) { $this->retVal = $val; }
		else { $this->retVal |= $val; }
	}

	public function add_table(Cli\Table $table) {
		$table->display();
	}

	public function display_table(Cli\Table $table) {
		$table->display();
	}

	public function display() {

		if(!empty($this->exceptions)) {
			$this->exception_quit();
		}

		echo $this->buffer;
		
	}

	public function exit_program() {
		exit($this->retVal);
	}

	public function error($msg = '') {
		echo "$msg\n";
		exit(self::RET_ERR);
	}

	public function success($msg = '') {
		echo "$msg\n";
		exit(self::RET_OK);
	}
}

?>
