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

#
# Core public static functions
#
# TODO: enable / disable API; generate API keys ?
#

class PS_CLI_Core extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('core', 'Manage PrestaShop core');

		$command->addOpt('check-version', 'check for available updates', false)
			->addOpt('list-modified-files', 'List modified files', false)
			->addOpt('show-info', 'Show server configuration', false)
			->addOpt('show-version', 'Show PrestaShop version', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if ($arguments->getOpt('check-version', false)) {
			$this->core_check_version();
		}
		elseif ($arguments->getOpt('list-modified-files', false)) {
			$this->core_list_changed_files();
		}
		elseif($arguments->getOpt('show-info', false)) {
			$this->print_server_info();
		}
		elseif($arguments->getOpt('show-version', false)) {
			$this->core_show_version();
		}
		else {
			$arguments->show_command_usage('core');
			exit(1);
		}
    }

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

		$configuration = PS_CLI_Configure::getConfigurationInstance();

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

PS_CLI_Configure::register_plugin('PS_CLI_Core');

?>
