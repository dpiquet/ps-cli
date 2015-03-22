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


class PS_CLI_OrderPreferences extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('order-preferences', 'PrestaShop orders preferences');
		$command->addOpt('show-status', 'Show current order configuration', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();

		if($arguments->getOpt('show-status', false)) {
			$this->print_order_preferences();
			$status = true;
		}
		else {
			$arguments->show_command_usage('order-preferences');
			exit(1);
		}

		if($status) {
			exit(0);
		}
		else {
			exit(1);
		}

	}

	public static function print_order_preferences() {
		$table = new Cli\Table();

		$table->setHeaders(Array('Key', 'Configuration', 'Value'));

		$line = Array('PS_ORDER_PROCESS_TYPE', 'Checkout process');
		$orderType = Configuration::get('PS_ORDER_PROCESS_TYPE');

		if($orderType == PS_ORDER_PROCESS_STANDARD) {
			array_push($line, 'Standard');
		}
		else {
			array_push($line, 'One page checkout');
		}

		$table->addRow($line);

		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_GUEST_CHECKOUT_ENABLED', 'Allow guest visitors to place orders');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISALLOW_HISTORY_REORDERING', 'Disable one click reorder');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ALLOW_MULTISHIPPING', 'Allow shipping to multiple addresses');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_SHIP_WHEN_AVAILABLE', 'Allow shipping delay');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_CONDITIONS', 'Require customers to accept terms of service');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PURCHASE_MINIMUM', 'Minimum purchase total required (0=disabled)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_CONDITIONS_CMS_ID', 'Store\'s condition of use page ID');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_GIFT_WRAPPING', 'Offer gift wrapping');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_GIFT_WRAPPING_PRICE', 'Gift wrapping price');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_GIFT_WRAPPING_TAX_RULES_GROUP', 'tax group for gift wrapping');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_RECYCLE_PACK', 'Suggest recycled packaging');

		$table->display();
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_OrderPreferences');

?>
