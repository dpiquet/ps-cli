<?php

class PS_CLI_ORDER_PREFERENCES {
	public static function print_order_preferences() {
		$table = new Cli\Table();

		$table->setHeaders(Array('Configuration', 'Value'));

		$line = Array('Checkout process');
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

?>
