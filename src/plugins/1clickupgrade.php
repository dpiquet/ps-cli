<?php
/*
 *
 * 1 click upgrade module extension
 *
 */

class PS_CLI_Autoupgrade extends PS_CLI_Plugin {

	public function upgradeCore() {

	}

	public function updateModules() {

	}

	public function updateThemes() {

	}

	protected function __construct() {

		$command = new PSCLI_Command('autoupgrade', 'Manage autoupgrade plugin');

		$command->addOpt('show-status', 'Show configuration', 'boolean', false);

		$command->register($this);
	}

	public function run() {
		$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();
		$interface = PS_CLI_INTERFACE::getInterface();

		if($arguments->getOpt('show-status')) {
			$table = new Cli\Table();

			$table->setHeaders(Array(
				'Key', 'Configuration', 'Value'
				)
			);

			PS_CLI_UTILS::add_configuration_value($table, 'PS_UPGRADE_CHANNEL', 'PrestaShop upgrade Channel');

			$interface->add_table($table);
		}
		else {
			$interface->add_content("Not implemented");
			$interface->set_ret_value(1);
		}
	}

}

PS_CLI_CONFIGURE::register_plugin('PSCLI_Autoupgrade');

?>
