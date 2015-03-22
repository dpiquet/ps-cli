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

		$command = new PS_CLI_Command('autoupgrade', 'Manage autoupgrade plugin');
		$command->addOpt('show-status', 'Show configuration', false, 'boolean');

		//$command->register($this);
		$this->register_command($command);
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

PS_CLI_CONFIGURE::register_plugin('PS_CLI_Autoupgrade');

?>
