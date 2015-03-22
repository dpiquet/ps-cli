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

class PS_CLI_CustomerPreferences extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('customer-preferences', 'PrestaShop customers preferences');
		$command->addOpt('show-status', 'Show current customer preferences', false)
			->addOpt('update', 'Update a configuration value')
			->addOpt('option', 'Configuration key to update')
			->addOpt('value', 'value to give to the configuration key');

		$this->register_command($command);	
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('show-status', false)) {
			$this->show_status();
			$status = true;
		}
		elseif($arguments->getOpt('update', false)) {
			$key = $arguments->getOpt('option', NULL);
			$value = $arguments->getOpt('value', NULL);

			if(is_null($key)) {
				$interface->error("You must provide --option with --update");
			}

			if(is_null($value)) {	
				$interface->error("You must provide --value with --update");
			}

			$this->_update_value($key, $value);
		}
		else {
			$arguments->show_command_usage('customer-preferences');
			exit(1);
		}

		if($status) { $interface->success(); }
		else { $interface->error(); }
	}

	private function _update_value($key, $value) {
		$interface = PS_CLI_Interface::getInterface();

		$validValue = true;

		switch($key) {
			case 'PS_ONE_PHONE_AT_LEAST':
			case 'PS_REGISTRATION_PROCESS_TYPE':
			case 'PS_CART_FOLLOWING':
			case 'PS_CUSTOMER_CREATION_EMAIL':
			case 'PS_B2B_ENABLE':
				$validValue = Validate::isBool($value);
				break;

			case 'PS_PASSWD_TIME_FRONT':
				$validValue = Validate::isUnsignedInt($value);
				break;

			default:
				$interface->error("The configuration key $key is not handled by this plugin !");
				break;
		}

		if(!$validValue) {
			$interface->error("Invalid value '$value' for configuration key '$key'");
		}

		if(PS_CLI_Utils::update_configuration_value($key, $value)) {
			$interface->success("Successfully updated configuration key '$key'");
		}
		else {
			$interface->error("Could not update configuration key '$key'");
		}
	}

	public static function show_status() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_REGISTRATION_PROCESS_TYPE', 'Registration type (0=only account; 1=account and address)');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ONE_PHONE_AT_LEAST', 'Customer have to provide at least one phone number');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_CART_FOLLOWING', 'Recall last shopping cart');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_CUSTOMER_CREATION_EMAIL', 'Send an email with account information');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PASSWD_TIME_FRONT', 'Minimum time between two passwords resets');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_B2B_ENABLE', 'B2B mode');

		$table->display();

	}
}

PS_CLI_Configure::register_plugin('PS_CLI_CustomerPreferences');

?>
