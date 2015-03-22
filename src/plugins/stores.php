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

class PS_CLI_Stores extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('store', 'manage stores');
		$command->addOpt('show-status', 'Show configuration', false, 'boolean')
		        ->addOpt('update', 'Update configuration', false, 'boolean')
		        ->addOpt('key', 'Configuration key to update', false, 'string')
		        ->addOpt('value', 'Value to assign to the configuration key', false, 'string');

		$this->register_command($command);
	}

	public function run() {
        $arguments = PS_CLI_Arguments::getArgumentsInstance();
        $interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('show-status')) {
			$this->show_status();
			$interface->success();
        }
        elseif($arguments->getOpt('update')) {
            $key = $arguments->getOpt('key', NULL);
            $value = $arguments->getOpt('value', NULL);

            if(is_null($key)) {
                $interface->error("You must provide --key with --update");
            }

            if(is_null($value)) {
                $interface->error("You must provide --value with --update");
            }

            $this->_update_configuration($key, $value);
        }
		else {
			$arguments->show_command_usage('store');
			$interface->error();
		}

		if($status) {
			$interface->success();
		}
		else {
			$interface->error();
		}
    }

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {
            case 'PS_STORE_DISPLAY_FOOTER':
            case 'PS_STORE_DISPLAY_SITEMAP':
            case 'PS_STORE_SIMPLIFIED':
                $validValue = Validate::isBool($value);
                break;

            case 'PS_STORES_CENTER_LAT':
            case 'PS_STORES_CENTER_LONG':
                $validValue = Validate::isCoordinate($value);
                break;

            case 'PS_SHOP_NAME':
                $validValue = Validate::isName($value);
                break;

            case 'PS_SHOP_EMAIL':
                $validValue = Validate::isEmail($value);
                break;

            case 'PS_SHOP_DETAILS':
                $validValue = Validate::isString($value);
                break;

            case 'PS_SHOP_ADDR1':
            case 'PS_SHOP_ADDR2':
                $validValue = Validate::isAddress($value);
                break;

            case 'PS_SHOP_CODE':
                $validValue = Validate::isPostCode($value);
                break;

            case 'PS_SHOP_CITY':
                $validValue = Validate::isCityName($value);
                break;

            case 'PS_SHOP_COUNTRY_ID':
                if(Validate::isUnsignedId($value)) {
                    $obj = new Country((int)$value);

                    $validValue = Validate::isLoadedObject($obj);
                }
                break;

            case 'PS_SHOP_STATE_ID':
                $validValue = Validate::isUnsignedId($value);
                break;

            case 'PS_SHOP_PHONE':
            case 'PS_SHOP_FAX':
                $validValue = Validate::isPhoneNumber($value);
                break;

            default:
                $interface->error("Configuration key '$key' is not handled by this command");
                break;
        }

        if(!$validValue) {
            $interface->error("value '$value' is not a valid value for configuration key '$key'");
        }

        if(PS_CLI_Utils::update_configuration_value($key, $value)) {
            $interface->success("Successfully updated '$key' configuration");
        }
        else {
            $interface->error("Could not update configuration key '$key'");
        }
    }

	public function show_status() {

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_STORE_DISPLAY_FOOTER', 'Display link to store locator in the footer');
		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_STORE_DISPLAY_SITEMAP', 'Display link to store locator in the sitemap');
		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_STORE_SIMPLIFIED', 'Show a simplified store locator');
		PS_CLI_Utils::add_configuration_value($table, 'PS_STORES_CENTER_LAT', 'Default latitude');
		PS_CLI_Utils::add_configuration_value($table, 'PS_STORES_CENTER_LONG', 'Default longitude');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_NAME', 'Shop name');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_EMAIL', 'Shop email');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_DETAILS', 'Shop details');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_ADDR1', 'Shop address line 1');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_ADDR2', 'Shop address line 2');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_CODE', 'Zip/postal code');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_CITY', 'City');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_COUNTRY_ID', 'Country ID');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_STATE_ID', 'State ID');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_PHONE', 'Phone number');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SHOP_FAX', 'Fax number');

		$table->display();
		
		return;
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Stores');

?>
