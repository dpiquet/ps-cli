<?php

// final class ?
class PSCLI_Command {

	public $name;

	public $description;

	private $_opts = [];

	private $_args = [];

	public function __construct($name, $description = '') {
		$this->name = $name;
		$this->description = $description;
	}

	public function addArg($arg, $desc = '', $required = false) {
		if(defined($this->_args[$arg])) {
			$interface = PS_CLI_INTERFACE::getInterfaceInstance();
			$interface->add_warning("argument $arg has already been defined");

			return false;
		}

		$this->_args[$arg] = Array(
			'description' => $desc,
			'required' => $required
		);
	}

	public function addOpt($opt, $desc = '', $type = 'string', $required = false) {
		if(array_key_exists($opt, $this->_opts)) {
			$interface = PS_CLI_INTERFACE::getInterface();
			$interface->add_warning("option $opt has already been defined");

			return false;
		}

		$this->_opts[$opt] = Array(
			'description' => $desc,
			'type' => $type,
			'required' => $required
		);
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
