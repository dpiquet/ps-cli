<?php

class PS_CLI_Ccc extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('ccc', 'Manage CCC configuration');
		$command->addOpt('show-status', 'Show configuration status');
		$command->addOpt('update-option', 'Update option value');
		$command->addOpt('value', 'Value to give to the option');

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();

		if($arguments->getOpt('enable-html-minifier', false)) {
			$successMsg = 'HTML code reduction successfully enabled';
			$errMsg = 'Could not enable HTML code reduction';
			$notChanged = 'HTML code reduction was already enabled';

			$status = PS_CLI_UTILS::update_global_value('PS_HTML_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-html-minifier', false)) {	
			$successMsg = 'HTML code reduction successfully disabled';
			$errMsg = 'Could not disable HTML code reduction';
			$notChanged = 'HTML code reduction was already disabled';

			$status = PS_CLI_UTILS::update_global_value('PS_HTML_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('enable-js-minifier', false)) {
			$successMsg = 'JavaScript code reduction successfully enabled';
			$errMsg = 'Could not enable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already enabled';

			$status = PS_CLI_UTILS::update_global_value('PS_JS_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-js-minifier', false)) {	
			$successMsg = 'JavaScript code reduction successfully disabled';
			$errMsg = 'Could not disable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already disabled';

			$status = PS_CLI_UTILS::update_global_value('PS_JS_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			$arguments->show_command_usage('ccc');
			exit(1);
		}

		if($status) 	{ exit(0); }
		else 		{ exit(1); }

	}


	// todo
	private function update_option($option, $value) {
		PS_CLI_UTILS::update_configuration_value($option, $value, $success, $err, $notChanged);	
	}

	private static function enable_htaccess_cache() {
		$successMsg = 'Successfully enabled htaccess cache control';
		$errMsg = 'Could not enable htaccess cache control';
		$notChanged = 'Htaccess cache control wal already enabled';

		if (PS_CLI_TOOLS::update_global_value('PS_HTACCESS_CACHE_CONTROL', true, $successMsg, $errMsg, $notChanged)) {
			if(Tools::generateHtaccess()) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	private static function disable_htaccess_cache() {
		$successMsg = 'Successfully disabled htaccess cache control';
		$errMsg = 'Could not disable htaccess cache control';
		$notChanged = 'Htaccess cache control wal already disabled';

		if (PS_CLI_TOOLS::update_global_value('PS_HTACCESS_CACHE_CONTROL', false, $successMsg, $errMsg, $notChanged)) {
			if(Tools::generateHtaccess()) {
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	private static function set_cipher($cipher) {

		if($cipher == 1) {
			return self::enable_mcrypt_cipher();
		}
		elseif($cipher == 0) {
			return self::enable_blowfish_cipher();
		}
		else {
			echo "Invalid cipher value\n";
			return false;
		}
	}

	private static function enable_mcrypt_cipher() {
		if(Configuration::getGlobalValue('PS_CIPHER_ALGORITHM') == 1) {
			//echo "Rijndael/Mcrypt cipher is already enabled\n";
			//silently return as we are a core function now
			return true;
		}

		$prev_settings = file_get_contents(_PS_ROOT_DIR_.'/config/settings.inc.php');
		$new_settings = $prev_settings;

		if (!function_exists('mcrypt_encrypt')) {
			echo('The "Mcrypt" PHP extension is not activated on this server.');
			return false;
		}
		else {
			if (!strstr($new_settings, '_RIJNDAEL_KEY_')) {
				$key_size = mcrypt_get_key_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
				$key = Tools::passwdGen($key_size);
				$new_settings = preg_replace(
					'/define\(\'_COOKIE_KEY_\', \'([a-z0-9=\/+-_]+)\'\);/i',
					'define(\'_COOKIE_KEY_\', \'\1\');'."\n".'define(\'_RIJNDAEL_KEY_\', \''.$key.'\');',
					$new_settings
				);
			}
			if (!strstr($new_settings, '_RIJNDAEL_IV_')) {
				$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
				$iv = base64_encode(mcrypt_create_iv($iv_size, MCRYPT_RAND));
				$new_settings = preg_replace(
					'/define\(\'_COOKIE_IV_\', \'([a-z0-9=\/+-_]+)\'\);/i',
					'define(\'_COOKIE_IV_\', \'\1\');'."\n".'define(\'_RIJNDAEL_IV_\', \''.$iv.'\');',
					$new_settings
				);
			}

			if ($new_settings == $prev_settings || (
                                                copy(_PS_ROOT_DIR_.'/config/settings.inc.php', _PS_ROOT_DIR_.'/config/settings.old.php')
                                                && (bool)file_put_contents(_PS_ROOT_DIR_.'/config/settings.inc.php', $new_settings)
                                        )) {
                                                //Configuration::updateValue('PS_CIPHER_ALGORITHM', 1);
						//echo "Successfully enabled Rijndaelcipher\n";
                                                return true;
                                        }
			else {
				echo "Error, could not update configuration file\n";
				return false;
			}
		}
	}

	private static function enable_blowfish_cipher() {
		if(Configuration::getGlobalValue('PS_CIPHER_ALGORITHM') == 0) {
			//echo "Blowfish cipher is already enabled\n";
			return true;
		}

		$prev_settings = file_get_contents(_PS_ROOT_DIR_.'/config/settings.inc.php');
		$new_settings = $prev_settings;

		if (!strstr($new_settings, '_RIJNDAEL_KEY_')) {
			$key_size = mcrypt_get_key_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			$key = Tools::passwdGen($key_size);
			$new_settings = preg_replace(
				'/define\(\'_COOKIE_KEY_\', \'([a-z0-9=\/+-_]+)\'\);/i',
				'define(\'_COOKIE_KEY_\', \'\1\');'."\n".'define(\'_RIJNDAEL_KEY_\', \''.$key.'\');',
				$new_settings
			);
		}
		if (!strstr($new_settings, '_RIJNDAEL_IV_')) {
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
			$iv = base64_encode(mcrypt_create_iv($iv_size, MCRYPT_RAND));
			$new_settings = preg_replace(
				'/define\(\'_COOKIE_IV_\', \'([a-z0-9=\/+-_]+)\'\);/i',
				'define(\'_COOKIE_IV_\', \'\1\');'."\n".'define(\'_RIJNDAEL_IV_\', \''.$iv.'\');',
				$new_settings
			);
		}

		if ($new_settings == $prev_settings || (
					copy(_PS_ROOT_DIR_.'/config/settings.inc.php', _PS_ROOT_DIR_.'/config/settings.old.php')
					&& (bool)file_put_contents(_PS_ROOT_DIR_.'/config/settings.inc.php', $new_settings)
				)) {
					//Configuration::updateValue('PS_CIPHER_ALGORITHM', 0);
					//echo "Successfully enabled Blowfish cipher\n";
					return true;
				}
		else {
			echo "Error, could not update configuration file\n";
			return false;
		}

	}
}

PS_CLI_CONFIGURE::register_plugin('PS_CLI_Ccc');

?>
