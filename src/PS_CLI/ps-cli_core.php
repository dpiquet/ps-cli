<?php
#
# Core public static functions
#
# TODO: enable / disable API; generate API keys ?
#

class PS_CLI_CORE {

	public static function core_check_version() {
		$upgrader = new UpgraderCore;
		$latest = $upgrader->checkPSVersion(true);

		if ($latest) {
			foreach ( $latest as $newVersion ) {	
				echo $newVersion['name'] . ' ' . $newVersion['version'];
			}

		}
		else { echo 'Prestashop is up to date'; }
	}

	public static function core_show_version() {
		$version = _PS_VERSION_;

		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		if($configuration->porcelain) {
			echo "$version\n";
		}
		else {
			echo "PrestaShop version is $version\n";
		}
	}

	public static function core_list_changed_files() {
		$upgrader = new UpgraderCore;
		$files = $upgrader->getChangedFilesList();

		// as in AdminInformationController.php
		$excludeRegexp = '(install(-dev|-new)?|themes|tools|cache|docs|download|img|localization|log|mails|translations|upload|modules|override/(:?.*)index.php$)';

		$modFiles = Array();

		$table = new Cli\Table();
		$table->setHeaders(Array('Part', 'file'));

		if($files) {
			foreach ( $files as $changedFileKey => $changedFileVal ) {

				if (!isset($modFiles[$changedFileKey])) {
					$modFiles[$changedFileKey] = Array();
				}

				foreach ( $changedFileVal as $modifiedFiles ) {
					if (preg_match('#^'.$excludeRegexp.'#', $modifiedFiles)) {
						continue;
					}
					array_push($modFiles[$changedFileKey], $modifiedFiles);
				}
			}

			foreach ($modFiles as $curModFiles => $values) {

				if (empty($values)) {
					continue;
				}

				foreach($values as $value) {
					//echo "  $value\n";
					$table->addRow(Array($curModFiles, $value));
				}
			}

		}
	
		if ($table->countRows() > 0) {	
			$table->display();
		}
		else {
			echo "No modified files to show\n";
		}

		return;
	}

	public static function clear_smarty_cache() {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

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

	public static function print_cache_status() {

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table, 
			'PS_SMARTY_CACHE', 
			'Smarty Template Cache');

		PS_CLI_UTILS::add_configuration_value(
			$table, 
			'PS_SMARTY_CACHING_TYPE', 
			'Smarty Caching type', 
			'1.6.0.11');

		PS_CLI_UTILS::add_configuration_value(
			$table,
			'PS_SMARTY_CONSOLE',
			'Display smarty console (0 for never, 1 for URL, 2 for always)',
			'1.6.0.11');

		PS_CLI_UTILS::add_configuration_value(
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

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table, 
			'PS_CSS_THEME_CACHE', 
			'Css cache');

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table, 
			'PS_JS_THEME_CACHE', 
			'JS cache'); 

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table, 
			'PS_HTACCESS_CACHE_CONTROL', 
			'Htaccess cache control');

		PS_CLI_UTILS::add_boolean_configuration_status(
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

	// we should not load core before loading this
	public static function upgrade_core() {
		//todo: rewrite with new path vars
		if (! @chdir('../install/upgrade/') ) {
			echo "Could not find ../install/upgrade directory\n";
			return false;
		}

		if (! @include_once('upgrade.php') ) {
			echo "Error, could not find the upgrade.php script\n";
			return false;
		}

		echo "End of upgrade process\n";
	}

	public static function print_server_info() {
		$context = Context::getContext();


		$params_optional_results = ConfigurationTest::check(ConfigurationTest::getDefaultTestsOp());

		$table = new Cli\Table();

		$table->addRow(Array(
			'MySQL version',
			Db::getInstance()->getVersion()
			)
		);

		$table->addRow(Array(
			'MySQL server',
			_DB_SERVER_
			)
		);

		$table->addRow(Array(
			'Database name',
			_DB_NAME_
			)
		);
		
		$table->addRow(Array(
			'User',
			_DB_USER_
			)
		);

		$table->addRow(Array(
			'Prefix',
			_DB_PREFIX_
			)
		);

		$table->addRow(Array(
			'Engine',
			_MYSQL_ENGINE_
			)
		);

		$table->addRow(Array(
			'PrestaShop version',
			_PS_VERSION_
			)
		);

		$table->addRow(Array(
			'Shop base URL',
			$context->shop->getBaseURL()
			)
		);

		foreach ($params_optional_results as $key => $value) {
			$table->addRow(Array($key, $value));
		}

		$table->display();
	}
}

?>
