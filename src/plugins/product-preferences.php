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

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {
            case 'PS_CATALOG_MODE':
            case 'PS_CART_REDIRECT':
            case 'PS_FORCE_FRIENDLY_PRODUCT':
            case 'PS_DISPLAY_QTIES':
            case 'PS_DISPLAY_JQZOOM':
            case 'PS_DISP_UNAVAILABLE_ATTR':
            case 'PS_ATTRIBUTE_CATEGORY_DISPLAY':
            case 'PS_DISPLAY_DISCOUNT_PRICE':
            case 'PS_ORDER_OUT_OF_STOCK':
            case 'PS_STOCK_MANAGEMENT':
            case 'PS_ADVANCED_STOCK_MANAGEMENT':
            case 'PS_FORCE_ASM_NEW_PRODUCT':
                $validValue = Validate::isBool($value);
                break;

            case 'PS_COMPARATOR_MAX_ITEM':
            case 'PS_NB_DAYS_NEW_PRODUCT':
            case 'PS_PRODUCT_SHORT_DESC_LIMIT':
            case 'PS_PRODUCTS_PER_PAGE':
            case 'PS_LAST_QTIES':
                $validValue = Validate::isUnsignedInt($value);
                break;

            case 'PS_PRODUCTS_ORDER_BY':
                $validValue = (Validate::isUnsignedInt($value) &&
                    $value <= 7);
                break;

            case 'PS_ATTRIBUTE_ANCHOR_SEPARATOR':
                //todo
                break;

            case 'PS_DEFAULT_WAREHOUSE_NEW_PRODUCT':
                $validValue = Validate::isUnsignedId($value);
                break;

            default:
                $interface->error("The configuration key '$key' is not handled by this command");
                break;
        }

        if(!$validValue) {
            $interface->error("'$value' is not a valid value for configuration key '$key'");
        }

        if(PS_CLI_Utils::update_configuration($key, $value)) {
            $interface->success("Successfully updated configuration key '$key'");
        }
        else {
            $interface->error("Could not update configuration key '$key'");
        }
    }
}

PS_CLI_Configure::register_plugin('PS_CLI_ProductPreferences');

?>
