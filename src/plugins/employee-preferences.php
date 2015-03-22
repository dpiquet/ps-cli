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

#
#
#	Employee preferences configuration
#

class PS_CLI_EmployeePreferences extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('employee-preferences', 'Manage PrestaShop employees preferences');
		$command->addOpt('show-status', 'Show employee configuration', false, 'boolean')
			->addOpt('update', 'Update a configuration key', false, 'boolean')
			->addOpt('key', 'Configuration key to update', false, 'string')
			->addOpt('value', 'Value to give to the configuration key', false, 'string');
		$this->register_command($command);
	}

	// TODO: refactor in plugin
	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('show-status', false)) {
			$this->print_employee_options();
			$interface->success();
		}
		elseif($arguments->getOpt('update', false)) {
			$key = $arguments->getOpt('key', null);
			$value = $arguments->getOpt('value', null);

			if(is_null($key)) {
				$interface->error("You must provide --key with --update");
			}
			
			if(is_null($value)) {
				$interface->error("You must provide --value with --update");
			}

			$this->_update_configuration_key($key, $value);
		}
		else {
			$arguments->show_command_usage('employee-preferences');
			$interface->error();
		}
	}

	private function _update_configuration_key($key, $value) {
		$interface = PS_CLI_Interface::getInterface();

		$validValue = true;

		switch($key) {

			case 'PS_PASSWD_TIME_BACK':
				$validValue = Validate::isUnsignedInt($value);
				break;

			case 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG':
				$validValue = Validate::isBool($value);
				break;

			default:
				$interface->error("This configuration key is not handled by this plugin");
				break;
		}

		if(!$validValue) {
			$interface->error("'$value' is not a valid value for configuration key '$key'");
		}

		if(PS_CLI_Utils::update_configuration_value($key, $value)) {
			$interface->success("Configuration key '$key' successfully updated");
		}
		else {
			$interface->error("Could not update configuration key '$key'");
		}
	}

	public static function print_employee_options() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_PASSWD_TIME_BACK', 'Minimum delay for password regeneration');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 'Memorize last used language in forms');

		$table->display();
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_EmployeePreferences');
?>
