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

class PS_CLI_Ccc extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('ccc', 'Manage CCC configuration');
		$command->addOpt('show-status', 'Show configuration status')
		        ->addOpt('update', 'Update a configuration value')
		        ->addOpt('key', 'Configuration key to update')
                ->addOpt('value', 'Value to assign to the configuration key')
				->addOpt('clear-cache', 'Clear smarty cache', false)
				->addOpt('disable-cache', 'Disable PrestaShop cache', false)
				->addOpt('enable-cache', 'Enable PrestaShop cache', false, 'string')
				->addOpt('cache-depth', 'Set cache depth (default 1)', false, 'integer')
				->addArg('<cachetype>', 'Cache to use (fs, memcache, xcache, apc)', false);

		$this->register_command($command);
	}

	public function run() {
        $arguments = PS_CLI_Arguments::getArgumentsInstance();
        $interface = PS_CLI_Interface::getInterface();

        if($arguments->getOpt('show-status', false)) {
            $this->show_status();
            $interface->success();
        }
        elseif($arguments->getOpt('enable-html-minifier', false)) {
			$successMsg = 'HTML code reduction successfully enabled';
			$errMsg = 'Could not enable HTML code reduction';
			$notChanged = 'HTML code reduction was already enabled';

			$status = PS_CLI_Utils::update_global_value('PS_HTML_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-html-minifier', false)) {	
			$successMsg = 'HTML code reduction successfully disabled';
			$errMsg = 'Could not disable HTML code reduction';
			$notChanged = 'HTML code reduction was already disabled';

			$status = PS_CLI_Utils::update_global_value('PS_HTML_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('enable-js-minifier', false)) {
			$successMsg = 'JavaScript code reduction successfully enabled';
			$errMsg = 'Could not enable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already enabled';

			$status = PS_CLI_Utils::update_global_value('PS_JS_THEME_COMPRESSION', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($arguments->getOpt('disable-js-minifier', false)) {	
			$successMsg = 'JavaScript code reduction successfully disabled';
			$errMsg = 'Could not disable JavaScript code reduction';
			$notChanged = 'JavaScript code reduction was already disabled';

			$status = PS_CLI_Utils::update_global_value('PS_JS_THEME_COMPRESSION', false, $successMsg, $errMsg, $notChanged);
        }
		elseif ($opt = $arguments->getOpt('disable-cache', false)) {
			$this->disable_cache();
		}
		elseif ($cache = $arguments->getOpt('enable-cache', false)) {

			if($cache == "1") { $cache = 'default'; }

			switch($cache) {
				case 'fs':
					$cacheType = 'CacheFS';
					break;
				case 'memcache':
					$cacheType = 'CacheMemcache';
					break;
				case 'xcache':
					$cacheType = 'CacheXcache';
					break;
				case 'apc':
					$cacheType = 'CacheApc';
					break;
				case 'default':
					$cacheType = 'default';
					break;
				default:
					$error = 'Cache type must be fs, memcache, xcache or apc';
					$arguments->show_command_usage('cache', $error);
					exit(1);
			}

			if ($depth = $arguments->getOpt('cache-depth', false)) {
				if ($depth <= 0) {
					$error = 'cache-depth must be a positive integer';
					exit(1);
				}
			}
			else {
				$depth = 1;
			}

			$status = $this->enable_cache($cache, $depth);
		}
		elseif ($opt = $arguments->getOpt('clear-cache', false)) {
			$status = $this->clear_smarty_cache();
		}
		else {
			$arguments->show_command_usage('ccc');
			$interface->error();
		}

		if($status) 	{ exit(0); }
		else 		{ exit(1); }

	}

    protected function update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {
            case 'PS_SMARTY_CACHE':
            case 'PS_CSS_THEME_CACHE':
            case 'PS_JS_THEME_CACHE':
                $validValue = Validate::isBool($value);
                break;

            case 'PS_SMARTY_CACHING_TYPE':
                switch($value) {
                    case 'filesytem':
                    case 'mysql':
                        $validValue = true;
                        break;
                    default:
                        $validValue = false;
                        break;
                }
                break;

            case 'PS_SMARTY_CONSOLE':
            case 'PS_SMARTY_FORCE_COMPILE':
                $validValue = (Validate::isUnsignedInt($value) &&
                    $value <= 2);
                break;

            case 'PS_SMARTY_CONSOLE_KEY':
                $validValue = Validate::isString($value);
                break;

            default:
                $interface->error("Configuration key '$key' is not handled by this plugin");
                break;
        }

        if(!$validValue) {
            $configuration->error("'$value' is not a valid value for configuration key '$key'");
        }

        // todo: callbacks ?
        if(PS_CLI_Utils::update_configuration_value($key, $value)) {
            $interface->success("Successfully updated configuration key '$key'");
        }
        else {
            $interface->error("Could not update configuration key '$key'");
        }
    }

	// todo
//	private function update_option($option, $value) {
//		PS_CLI_Utils::update_configuration_value($option, $value, $success, $err, $notChanged);	
//	}

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

	public static function clear_smarty_cache() {
		$configuration = PS_CLI_Configure::getConfigurationInstance();

		echo "Clearing cache...\n";

		if($configuration->verbose) {
			echo "Smarty cache ";
		}
		Tools::clearSmartyCache();

		if($configuration->verbose) {
			echo "[OK]\nXML cache ";
		}
		Tools::clearXMLCache();

		if($configuration->verbose) {
			echo "[OK]\nClearing media cache ";
		}
		Media::clearCache();

		if($configuration->verbose) {
			echo "[OK]\nRegenerating index ";
		}
		Tools::generateIndex();

		if($configuration->verbose) {
			echo "[OK]\n";
		}

		echo "Done !\n";

		return true;	
	}

    public function show_status() {

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_Utils::add_boolean_configuration_status(
			$table, 
			'PS_SMARTY_CACHE', 
			'Smarty Template Cache');

		PS_CLI_Utils::add_configuration_value(
			$table, 
			'PS_SMARTY_CACHING_TYPE', 
			'Smarty Caching type', 
			'1.6.0.11');

		PS_CLI_Utils::add_configuration_value(
			$table,
			'PS_SMARTY_CONSOLE',
			'Display smarty console (0 for never, 1 for URL, 2 for always)',
			'1.6.0.11');

		PS_CLI_Utils::add_configuration_value(
			$table,
			'PS_SMARTY_CONSOLE_KEY',
			'Smarty console key',
			'1.6.0.11');

		$currentConfig = Configuration::getGlobalValue('PS_SMARTY_FORCE_COMPILE');

		$line = Array(
			'PS_SMARTY_FORCE_COMPILE',
			'Smarty Template Compilation ('.
				_PS_SMARTY_NO_COMPILE_.' for never, '.
				_PS_SMARTY_CHECK_COMPILE_.' for updated, '.
				_PS_SMARTY_FORCE_COMPILE_.' for always)'
		);
		switch($currentConfig) {
			case _PS_SMARTY_NO_COMPILE_:
				array_push($line, $currentConfig.' (never)');
				break;
			case _PS_SMARTY_CHECK_COMPILE_:
				array_push($line, $currentConfig.' (if updated)');
				break;
			case _PS_SMARTY_FORCE_COMPILE_:
				array_push($line, $currentConfig.' (Always)');
				break;
		}

		$table->addRow($line);

		PS_CLI_Utils::add_boolean_configuration_status(
			$table, 
			'PS_CSS_THEME_CACHE', 
			'Css cache');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table, 
			'PS_JS_THEME_CACHE', 
			'JS cache'); 

		PS_CLI_Utils::add_boolean_configuration_status(
			$table, 
			'PS_HTACCESS_CACHE_CONTROL', 
			'Htaccess cache control');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table, 
			'PS_MEDIA_SERVERS', 
			'Use Media Servers');

		$line = Array('PS_CIPHER_ALGORITHM', 'Cipher (0=blowfish, 1=rijndael)');

		if(Configuration::getGlobalValue('PS_CIPHER_ALGORITHM')) {
			array_push($line, 'RIJNDAEL/Mcrypt');
		}
		else {
			array_push($line, 'Local Blowfish');
		}

		$table->addRow($line);

		$line = Array('Const: _PS_CACHE_ENABLED_', 'Cache');

		if ( _PS_CACHE_ENABLED_ ) {
			array_push($line, 'enabled');
		}
		else {
			array_push($line, 'disabled');
		}

		$table->addRow($line);

		$table->addRow(Array(
			'Const: _PS_CACHING_SYSTEM_',
			'Active Caching system',
			_PS_CACHING_SYSTEM_
			)
		);

		$table->display();

		return;
	}

	public static function disable_cache() {
		// direct edition of the config file (as in the prestashop code)
		$new_settings = $prev_settings = file_get_contents(_PS_ROOT_DIR_.'/config/settings.inc.php');

		$new_settings = preg_replace('/define\(\'_PS_CACHE_ENABLED_\', \'([01]?)\'\);/Ui', 'define(\'_PS_CACHE_ENABLED_\', \'0\');', $new_settings);

		if ( $new_settings == $prev_settings ) {
			echo "Cache already disabled\n";
			return true;
		}

		if (! copy(_PS_ROOT_DIR_.'/config/settings.inc.php', _PS_ROOT_DIR_.'/config/settings.old.php') ){
			echo "Could not backup "._PS_ROOT_DIR_."/config/settings.inc.php before processing\n";
			echo "Operation canceled\n";
			return false;
		}

		if ( file_put_contents(_PS_ROOT_DIR_.'/config/settings.inc.php', $new_settings) ) {
			echo "Cache successfully disabled\n";

			// clean cache
			if (_PS_CACHING_SYSTEM_ == 'CacheFs') {
				CacheFs::deleteCacheDirectory();
			}

			return true;
		}
		else {
			echo "Could not update settings.inc.php file\n";
			return false;
		}
	}

	public static function enable_cache($cache, $cacheFSDepth = 1) {

		if (! Validate::isInt($cacheFSDepth) ) {
			echo "Error, cacheFSDepth must be integer\n";
			return false;
		}

		if ($cacheFSDepth <= 0) {
			echo "Error, depth must be superior to 0\n";
			return false;
		}
	
		$new_settings = $prev_settings = file_get_contents(_PS_ROOT_DIR_.'/config/settings.inc.php');

		$new_settings = preg_replace(
			'/define\(\'_PS_CACHE_ENABLED_\', \'([01]?)\'\);/Ui', 
			'define(\'_PS_CACHE_ENABLED_\', \'1\');', 
			$new_settings
		);

		if($cache = 'default') {
			$cache = _PS_CACHING_SYSTEM_;
		}

		echo "Enabling $cache cache system\n";

		switch($cache) {
			case 'CacheMemcache':
				if (! extension_loaded('memcache') ) {
					echo "Error: PHP memcache PECL extension is not loaded\n";
					return false;
				}

				break;

			case 'CacheApc':
				if (! extension_loaded('apc') ) {
					echo "Error: PHP APC PECL extension is not loaded\n";
					return false;
				}

				break;

			case 'CacheXcache':
				if (! extension_loaded('xcache') ) {
					echo "Error: PHP Xcache extension not loaded\n";
					return false;
				}

				break;

			case 'CacheFs':
				if (! is_dir(_PS_CACHEFS_DIRECTORY_) ) {
					if (! @mkdir(_PS_CACHE_FS_DIR_, 0750, true) ) {
						echo "Error, could not create cache directory\n";
						return false;
					}
				}
				elseif (! is_writeable(_PS_CACHEFS_DIRECTORY_) ) {
					echo "Error: Cache directory is not writeable\n";
					return false;
				}

				CacheFs::deleteCacheDirectory();
				CacheFs::createCacheDirectories($cacheFSDepth);
				Configuration::updateValue('PS_CACHEFS_DIRECTORY_DEPTH', $cacheFSDepth);

				break;

			default:
				echo "Unknown cache type: $cache\n";
				return false;
		}

		$new_settings = preg_replace(
			'/define\(\'_PS_CACHING_SYSTEM_\', \'([a-z0-9=\/+-_]*)\'\);/Ui',
			'define(\'_PS_CACHING_SYSTEM_\', \''.$cache.'\');',
			$new_settings
		);

		if ($new_settings == $prev_settings) {
			echo "Cache $cache is already in use\n";
			return true;
		}

		if (! @copy(_PS_ROOT_DIR_.'/config/settings.inc.php', _PS_ROOT_DIR_.'/config/settings.old.php') ) {
			echo "Error, could not backup config file\n";
			return false;
		}

		if ( file_put_contents(_PS_ROOT_DIR_.'/config/settings.inc.php', $new_settings) ) {
			echo "cache $cache successfully activated\n";
			return true;
		}
		else {
			echo "Could not update config file\n";
			return false;
		}

	}


}

PS_CLI_CONFIGURE::register_plugin('PS_CLI_Ccc');

?>
