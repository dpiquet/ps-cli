<?php

class PS_CLI_ProductPreferences extends PS_CLI_Plugin {

	protected function __construct() {
		$command= new PS_CLI_Command('product-preferences', 'PrestaShop products preferences');
		$command->addOpt('show-status', 'Show current products preferences', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();

		if($arguments->getOpt('show-status', false)) {
			$this->show_status();
			$status = true;
		}
		else {
			$arguments->show_command_usage('product-preferences');
			exit(1);
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

		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_CATALOG_MODE', 'Enable PS catalog mode');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_COMPARATOR_MAX_ITEM', 'Max selection for products comparison');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_NB_DAYS_NEW_PRODUCT', 'Number of days for which a product is considered new');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_CART_REDIRECT', 'Redirect after adding product to cart');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCT_SHORT_DESC_LIMIT', 'Maximum product short description size');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_QTY_DISCOUNT_ON_COMBINATION', 'How to calculate quantity discounts (0=products, 1=combination)');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_FORCE_FRIENDLY_PRODUCT', 'update friendly url on every save');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCTS_PER_PAGE', 'Number of products per page');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCTS_ORDER_BY', 'Products sorted by (0=name, 1=price, 2=add date, 3=modification date, 4=position category, 5=manufacturer, 6=Qtty, 7=reference)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCTS_ORDER_WAY', 'Default order method (0=asc, 1=desc)');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISPLAY_QTIES', 'Display available quantities');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_LAST_QTIES', 'Display remaining when lower than:');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISPLAY_JQZOOM', 'Enable Jqzoom on product page');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISP_UNAVAILABLE_ATTR', 'Display unavailable products');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ATTRIBUTE_CATEGORY_DISPLAY', 'Display add to cart button when product has attributes');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_ATTRIBUTE_ANCHOR_SEPARATOR', 'Separator of attribute anchor on product links');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISPLAY_DISCOUNT_PRICE', 'Display the new price with applied discount');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ORDER_OUT_OF_STOCK', 'Display add to cart on unavailable products');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_STOCK_MANAGEMENT', 'Enable stock management');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ADVANCED_STOCK_MANAGEMENT', 'Enabled advanced stock management');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_FORCE_ASM_NEW_PRODUCT', 'New products use advanced stock management');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_DEFAULT_WAREHOUSE_NEW_PRODUCT', 'Default warehouse of new products');

		$table->display();

		return;
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_ProductPreferences');

?>
