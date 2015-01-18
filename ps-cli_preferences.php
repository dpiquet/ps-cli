<?php

class PS_CLI_PREFERENCES {

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

		$line = Array('Round mode');
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
			'Shop activity',
			$activities[$shopActivity]
		);

		$table->addRow($line);

		$table->display();
	}

	public static function set_round_mode($roundMode) {
		$currentRoundMode = Configuration::get('PS_PRICE_ROUND_MODE');

		switch($roundMode) {
			case 'superior':
				$newRoundMode = 0;
				break;

			case 'inferior':
				$newRoundMode = 1;
				break;

			case 'classic':
				$newRoundMode = 2;
				break;

			default:
				echo "Invalid round mode: $roundMode\n";
				return false;
		}

		if($newRoundMode == $currentRoundMode) {
			echo "$roundMode is already the current round mode\n";
			return true;
		}

		return Configuration::updateValue('PS_PRICE_ROUND_MODE', $newRoundMode);
	}

	public static function set_max_file_size($maxFileSize) {
		$current = Configuration::get('PS_LIMIT_UPLOAD_FILE_VALUE');

		if($maxFileSize == $current) {
			echo "Maximum file upload size is already $maxFileSize\n";
			return true;
		}

		if ($maxFileSize <= 0) {
			echo "Error, max file size must be higher than 0\n";
			return false;
		}

		if(Configuration::updateValue('PS_LIMIT_UPLOAD_FILE_VALUE', $maxFileSize)) {
			echo "Successfully set max file size to $maxFileSize\n";
			return true;
		}
		else {
			echo "Error, could not update max file size value\n";
			return false;
		}
	}

	public static function set_max_image_size($maxFileSize) {
		$current = Configuration::get('PS_LIMIT_UPLOAD_IMAGE_VALUE');

		if($maxFileSize == $current) {
			echo "Maximum image upload size is already $maxFileSize\n";
			return true;
		}

		if ($maxFileSize <= 0) {
			echo "Error, max image size must be higher than 0\n";
			return false;
		}

		if(Configuration::updateValue('PS_LIMIT_UPLOAD_IMAGE_VALUE', $maxFileSize)) {
			echo "Successfully set max image size to $maxFileSize\n";
			return true;
		}
		else {
			echo "Error, could not update max image size upload value\n";
			return false;
		}
	}

	public static function set_max_attachment_size($maxFileSize) {
		$current = Configuration::get('PS_ATTACHMENT_MAXIMUM_SIZE');

		if($maxFileSize == $current) {
			echo "Maximum attachment size is already $maxFileSize\n";
			return true;
		}

		if ($maxFileSize <= 0) {
			echo "Error, max attachment size must be higher than 0\n";
			return false;
		}

		if(Configuration::updateValue('PS_ATTACHMENT_MAXIMUM_SIZE', $maxFileSize)) {
			echo "Successfully set max attachment size to $maxFileSize\n";
			return true;
		}
		else {
			echo "Error, could not update max attachment size upload value\n";
			return false;
		}
	}
}

?>
