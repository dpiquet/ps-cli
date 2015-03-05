<?php

class PS_CLI_INTERFACE {

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
	private function __construct {
		ob_start();

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

		return self::$_instance();
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
		ob_clean();

		foreach ($this->exceptions as $e) {
			echo "Got exception $e\n";
		}

		ob_end_flush();
		exit(self::RET_EXCEPTION);
	}

	public function add_content($content) {
		$this->buffer .= $content;
	}

	public function set_ret_val($val, $force = false) {
		if($force) { $this->retVal = $val; }
		else { $this->retVal |= $val; }
	}

	public function add_table(Cli\Table $table) {
		if($table->countRows() > 0) {
			$lines = $table->getDisplayLines();

			foreach($lines as $line) {
				$this->buffer .= $line;
			}
		}
	}

	public function display() {
		ob_clean();

		if(!empty($this->exceptions)) {
			$this->exception_quit();
		}

		echo $this->buffer;
		
		ob_end_flush();
	}

	public function exit_program() {
		exit($this->retVal);
	}
}

?>
