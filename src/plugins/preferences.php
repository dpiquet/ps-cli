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

class PS_CLI_Preferences extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('preferences', 'Set up PrestaShop preferences');
        $command->addOpt('show-status', 'Show preferences configuration')
            ->addOpt('update', 'Update configuration value', false, 'boolean')
            ->addOpt('key', 'Configuration key to update', false, 'string')
            ->addOpt('value', 'Value to assign to the configuration key', false, 'string');

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('show-status', false)) {
			$this->show_preferences_status();
			$status = true;
        }
        elseif($arguments->getOpt('udpate', false)) {
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
			$arguments->show_command_usage('preferences');
			$interface->error();
		}

		if($status) {
			$interface->success();
		}
		else {
			$interface->error();
		}
	}

	public static function show_preferences_status() {
		$table = new Cli\Table();

		$activities = array(
                                2 => 'Animals and Pets',
                                3 => 'Art and Culture',
                                4 => 'Babies',
                                5 => 'Beauty and Personal Care',
                                6 => 'Cars',
                                7 => 'Computer Hardware and Software',
                                8 => 'Download',
                                9 => 'Fashion and accessories',
                                10 => 'Flowers, Gifts and Crafts',
                                11 => 'Food and beverage',
                                12 => 'HiFi, Photo and Video',
                                13 => 'Home and Garden',
                                14 => 'Home Appliances',
                                15 => 'Jewelry',
                                1 => 'Lingerie and Adult',
                                16 => 'Mobile and Telecom',
                                17 => 'Services',
                                18 => 'Shoes and accessories',
                                19 => 'Sport and Entertainment',
                                20 => 'Travel'
                        );

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_TOKEN_ENABLE', 'Front office tokens');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ALLOW_HTML_IFRAME', 'Allow HTML iframes');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_USE_HTMLPURIFIER', 'Use HTML purifier library');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISPLAY_SUPPLIERS', 'Enable FO suppliers and manufacturers page');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_DISPLAY_BEST_SELLERS', 'Enable FO Best Sellers page');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_COOKIE_CHECKIP', 'Check cookie IP address');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_COOKIE_LIFETIME_FO', 'Front office cookie lifetime');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_COOKIE_LIFETIME_BO', 'Back office cookie lifetime');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_LIMIT_UPLOAD_FILE_VALUE', 'Maximum upload size (MB)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_LIMIT_UPLOAD_IMAGE_VALUE', 'Maximum image upload size (MB)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_ATTACHMENT_MAXIMUM_SIZE', 'Maximum attachment size (MB)');

		$line = Array('PS_PRICE_ROUND_MODE', 'Round mode (0=superior, 1=inferior, 2=classic)');
		$roundMode = Configuration::get('PS_PRICE_ROUND_MODE');
		switch($roundMode) {
			case 0:
				array_push($line, 'Superior');
				break;
			case 1:
				array_push($line, 'Inferior');
				break;
			case 2:
				array_push($line, 'Classic');
				break;
		}

		$table->addRow($line);

		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_SSL_ENABLED', 'Force SSL on all pages');

		$shopActivity = Configuration::get('PS_SHOP_ACTIVITY');
		$line = Array(
			'PS_SHOP_ACTIVITY',
			'Shop activity',
			$activities[$shopActivity] . " [$shopActivity]"
		);

		$table->addRow($line);

		$table->display();
    }

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {

            case 'PS_TOKEN_ENABLE':
            case 'PS_ALLOW_HTML_IFRAME':
            case 'PS_USE_HTML_PURIFIER':
            case 'PS_DISPLAY_SUPPLIERS':
            case 'PS_DISPLAY_BEST_SELLERS':
            case 'PS_COOKIE_CHECKIP':
            case 'PS_SSL_ENABLED':
                $validValue = Validate::isBool($value);
                break;

            case 'PS_COOKIE_LIFETIME_FO':
            case 'PS_COOKIE_LIFETIME_FO':
            case 'PS_LIMIT_UPLOAD_FILE_VALUE':
            case 'PS_LIMIT_UPLOAD_IMAGE_VALUE':
            case 'PS_ATTACHMENT_MAXIMUM_SIZE':
                $validValue = Validate::isUnsignedInt($value);
                break;

            case 'PS_PRICE_ROUND_MODE':
                $validValue = (Validate::isUnsignedInt($value) &&
                    $value <= 2);
                break;

            case 'PS_SHOP_ACTIVITY':
                $validValue = (Validate::isUnsignedInt($value) && 
                    $value <= 20);
                break;

            default:
                $interface->error("The configuration key '$key' is not handled by this plugin");
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
}

PS_CLI_Configure::register_plugin('PS_CLI_Preferences');

?>
