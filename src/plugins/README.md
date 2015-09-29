#PS-cli plugins#

PS-cli supports user written plugins to extend its functionalities.

A plugin is a class extending `PS_CLI_Plugin`. It must define at least two methods:
 * `__construct()`
 * `run()`

The `__construct` method should define the command(s) handled by the plugin. The `run` method contains the arguments parsing and/or the plugin logic.

A plugin is then registered using the `PS_CLI_Configure::register_plugin( className )` function.

###Minimal Ps-cli plugin example:###

```
	class PS_CLI_Example extends PS_CLI_Plugin {

		protected function __construct() {
			$command = new PS_CLI_Command('hello-world', 'Hello world plugin');
			$command->addOpt('name', 'A name', false, 'string');

			$this->register_command($command);
		}

		public function run() {
			$interface = PS_CLI_Interface::getInterface();
			$arguments = PS_CLI_Arguments::getArgumentsInstance();

			if($name = $arguments->getOpt('name', false)) {
				$interface->success("Hello $name");
			}
			else {
				$interface->error("Hello world !");
			}
		}
	}

	PS_CLI_Configure::register_plugin('PS_CLI_Example');
```

## Arguments / Commands ##

Ps-cli uses the garden-cli library to manage command line arguments.

You can create commands, options and arguments. 

* Commands are the first argument given to the script and determines which plugin will be run
* Options are passed by `--name` full name
* Arguments are just strings separated by spaces

### Command definition ###

A plugin **must** define at least one command. To create a command, create a `PS_CLI_Command` object like this:

`$command = new PS_CLI_Command('command-name', 'command-defition');`

The `PS_CLI_Command` class offers a few methods:
* addOpt(name, description, required, type)
* addArg(name, description, required)
* setDescription(description)

Once a command is ready, register it:

`$this->register_command($command);`

### Argument parsing ###

The plugin's `run` method must implement the argument parsing. To do so, first get the `PS_CLI_Arguments` singleton instance.

If the plugin depends on a module, check it's main class exists using PHP's `class_exists` function. You can see the autoupgrade plugin as an example.

`$arguments = PS_CLI_Arguments::getArgumentsInstance();`

#### Get the called command ####

Use the `getCommand` method to find out which command was called. This is only useful when you register more than one command to the same plugin.

```
	$interface = PS_CLI_Interface::getInterface();
	$arguments = PS_CLI_Arguments::getArgumentsInstance();

	$command = $arguments->getCommand();

	switch($command) {
    	case: 'com1':
			$interface->success('com1 command called');
			break;

		case 'com2':
			$interface->success('com2 command called');
			break;
	}
```		

#### Test arguments ####

Use the `getOpt` method to find out if an option was given.

```
	$arguments = PS_CLI_Arguments::getArgumentsInstance();
	$value = $arguments->getOpt('name', 'Roger'); // get the 'name' option value with default 'Roger'
```

use the `getArg` method to retrieve arguments.

## Plugin utils functions ##

The `PS_CLI_Plugin` class defines some final methods you can not override.

* public GetInstance()
* protected register_command()
* public getCommands()
