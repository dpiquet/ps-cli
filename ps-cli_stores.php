<?php

class PS_CLI_STORES {

	public static function show_status() {

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_STORE_DISPLAY_FOOTER', 'Display link to store locator in the footer');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_STORE_DISPLAY_SITEMAP', 'Display link to store locator in the sitemap');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_STORE_SIMPLIFIED', 'Show a simplified store locator');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_STORES_CENTER_LAT', 'Default latitude');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_STORES_CENTER_LONG', 'Default longitude');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_NAME', 'Shop name');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_EMAIL', 'Shop email');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_DETAILS', 'Shop details');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_ADDR1', 'Shop address line 1');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_ADDR2', 'Shop address line 2');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_CODE', 'Zip/postal code');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_CITY', 'City');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_COUNTRY_ID', 'Country ID');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_STATE_ID', 'State ID');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_PHONE', 'Phone number');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_FAX', 'Fax number');

		$table->display();
		
		return;
	}


}

?>
