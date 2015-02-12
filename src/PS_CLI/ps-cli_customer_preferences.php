<?php

class PS_CLI_CUSTOMER_PREFERENCES {

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

?>
