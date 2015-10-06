<?php

/*
 * ps cli hooks system
 *
 * @author Damien PIQUET (piqudam@gmail.com)
 *
 */
class PS_CLI_Hooks {
	static private $_hooks = Array();

	static private $_instance = NULL;

	private function __construct() {
		//empty constructor
	}

	/*
	 * get the class singleton
	 *
	 */
	public function getInstance() {
		if(self::$_instance === NULL) {
			self::$_instance = new PS_CLI_Hooks();
		}

		return self::$_instance;
	}

	/*
	 * Register hook on specified event
	 *
	 */
	public static function registerHook($object, $event, $method, $args = Array()) {
		if(!isset(self::$_hooks[$event])) {
			self::$_hooks[$event] = Array();
		}

		$newHook = Array();
		$newHook[] = Array($object, $method);
		$newHook[] = $args;


		array_push(self::$_hooks[$event], $newHook);
	}

	/*
	 * Run registered hooks for specified event
	 *
	 */
	public static function callHooks($event) {

		echo("callHook called\n");
		$arguments = PS_CLI_Arguments::getInstance();
		$command = $arguments->get_command_handler_class();

		if(isset(self::$_hooks[$event])) {
			foreach(self::$_hooks[$event] as $hook) {
				if(is_callable($hook[0])) {
					//todo: try//catch ?
					call_user_func_array($hook[0], $hook[1]);
				}
				else {
					echo('[DEBUG]: uncallable hook in event '.$event);
				}
			}
		}
		else {
			echo("[DEBUG]: no hooks registered for event $event under command $command\n");
		}
	}
}

?>
