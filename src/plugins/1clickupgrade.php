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



/*
 *
 * 1 click upgrade module extension
 *
 */

class PS_CLI_Autoupgrade extends PS_CLI_Plugin {

	private $modulePath = NULL;


	public function updateModules() {

	}

	public function updateThemes() {

	}

	/*
	 * TODO: http://php.net/manual/fr/language.oop5.autoload.php
	 * autoload PHP does not work with CLI; maybe try composer instead
	 *
	 */
	protected function __construct() {

		$command = new PS_CLI_Command('autoupgrade', 'Manage autoupgrade plugin');
		$command->addOpt('check-version', 'Check PrestShop version', false, 'boolean')
				->addOpt('list-modified-files', 'List files modified locally', false, 'boolean')
				->addOpt('upgrade-core', 'Upgrade PrestaShop core using autoupgrade module', false, 'boolean');
		$prefCommand = new PS_CLI_Command('autoupgrade-preferences', 'Manage autoupgrade plugin configuration');
		$prefCommand->addOpt('show-status', 'Show configuration', false, 'boolean')
			->addOpt('update', 'Update a configuration key', false, 'boolean')
			->addOpt('key', 'Configuration key to update', false, 'string')
			->addOpt('value', 'Value to assign to the configuration key', false, 'string');

		$this->register_command($command);
		$this->register_command($prefCommand);

		$configuration = PS_CLI_Configure::getConfigurationInstance();

		//PS_CLI_Hooks::registerHook(__CLASS__.'::disablePsCoreLoad');
		$this->registerHook('before_load_ps_core', 'disablePsCoreLoad');

		$modulePath = $configuration->psPath.'modules'.DIRECTORY_SEPARATOR.'autoupgrade';

		$upgraderFile = '../'.$modulePath.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Upgrader.php';

		if(file_exists($upgraderFile)) {
			//require_once($upgraderFile);
		}
	}

	public function run() {
		if(!class_exists('Autoupgrade')) {
			PS_CLI_Interface::error('The 1clickUpgrade module seems not installed or not active !');
		}	

		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		$calledCommand = $arguments->getCommand();

		if($arguments->getOpt('show-status')) {
			$this->_show_status();
		}
		elseif($arguments->getOpt('update', false)) {
			$key = $arguments->getOpt('key', NULL);
			$value = $arguments->getOpt('value', NULL);

			if(is_null($key)) {
				PS_CLI_Interface::error("You must provide --key with --update");
			}

			if(is_null($value)) {
				PS_CLI_Interface::error("You must provide --value with --update");
			}

			$this->_update_configuration($key, $value);
		}
		elseif($arguments->getOpt('list-modified-files', false)) {
			$this->_listModifiedFiles();
		}
		elseif($arguments->getOpt('check-version', false)) {
			$this->_isUpToDate();
		}
		elseif($arguments->getOpt('upgrade-core', false)) {
			$this->_upgradeCore();
		}
		else {
			$arguments->show_command_usage($calledCommand);
			PS_CLI_Interface::error();
		}
	}

	protected function _show_status() {
		$interface = PS_CLI_Interface::getInterface();

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key', 'Configuration', 'Value'
			)
		);

		PS_CLI_Utils::add_configuration_value(
			$table,
			'PS_UPGRADE_CHANNEL',
			'PrestaShop upgrade Channel');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table,
			'PS_AUTOUP_BACKUP',
			'Automatically backup database and files');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table,
			'PS_AUTOUP_KEEP_IMAGES',
			'Do not backup images');

		PS_CLI_Utils::add_configuration_value(
			$table,
			'PS_AUTOUP_PERFORMANCE',
			'Server performance (1: Low; 2: Medium; 3; High)');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table,
			'PS_AUTOUP_CUSTOM_MOD_DESACT',
			'Disable non native modules');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table,
			'PS_AUTOUP_UPDATE_DEFAULT_THEME',
			'Use the upgrade default theme');

		PS_CLI_Utils::add_boolean_configuration_status(
			$table,
			'PS_AUTOUP_KEEP_MAILS',
			'Upgrade the default emails');

		PS_CLI_Interface::display_table($table);
	}

	/*
	 * Update configuration keys handled by this plugin
	 *
	 * @param string key configuration key to update
	 * @param string value Value to assign to configuration key. Can be bool, int or string.
	 */
	protected function _update_configuration($key, $value) {
		$validValue = false;

		switch($key) {
			case 'PS_AUTOUP_KEEP_MAILS':
			case 'PS_AUTOUP_UPDATE_DEFAULT_THEME':
			case 'PS_AUTOUP_CUSTOM_MOD_DESACT':
			case 'PS_AUTOUP_KEEP_IMAGES':
			case 'PS_AUTOUP_BACKUP':
				$validValue = Validate::isBool($value);
				break;

			case 'PS_AUTOUP_PERFORMANCE':
				$validValue = (Validate::isUnsignedInt($value) &&
					$value <= 3);
				break;

			/** Check other values too */
			case 'PS_UPGRADE_CHANNEL':
				$validChannels = Array('beta', 'rc', 'stable');
				$validValue = in_array($value, $validChannels);
				break;

			default:
				PS_CLI_Interface::error("The configuration key '$key' is not handled by this plugin");
				break;
		}

		if(!$validValue) {
			PS_CLI_Interface::error("'$value' is not a valid value for configuration key '$key'");
		}

		if(PS_CLI_Utils::update_configuration($key, $value)) {
			PS_CLI_Interface::success("Successfully updated configuration key '$key'");
		}
		else {
			PS_CLI_Interface::error("Could not update configuration key '$key'");
		}
	}

	/*
	 * Update PrestaShop core using autoupgrade module
	 *
	 */
	protected function _upgradeCore() {
		$configuration = PS_CLI_Configure::getConfigurationInstance();

		$upgrader = new Upgrader();
		if(!is_object($upgrader)) {
			PS_CLI_Interface::error('Could not load autoupgrade module !');
		}

		$upgrader->checkPSVersion(true, array('minor'));

		//$updates = $upgrader->checkPSVersion(true);


		$downloadPath = $configuration->boPath.DIRECTORY_SEPARATOR.'autoupgrade'.DIRECTORY_SEPARATOR.'download';

		if($configuration->debug) {
			PS_CLI_Interface::display_line("Downloading latest version to $downloadPath");
		}

		if(!$upgrader->downloadLast($downloadPath)) {
			PS_CLI_Interface::error("Could not download latest version to $downloadPath");
		}

		print_r($upgrader); die();
	}

	/*
	 * Check if PrestaShop is up to date
	 *
	 * @return false if not up to date
	 */
	protected function _isUpToDate() {
		$upgrader = new Upgrader();

		if(!is_object($upgrader)) {
			PS_CLI_Interface::error("Could not load upgrader module");
		}

		if($upgrader->isLastVersion() === false) {
			PS_CLI_Interface::success('This PrestaShop install needs to be updated');
		}
		else {
			PS_CLI_Interface::error('This PrestaShop install is up to date');
		}
	}

	/*
	 * Print the list of modified files
	 *
	 */
	protected function _listModifiedFiles() {

		$upgrader = new Upgrader();

		if(!is_object($upgrader)) {
			PS_CLI_Interface::error('Could not load upgrader module');
		}

		$modifiedFiles = $upgrader->getChangedFilesList(NULL, true);

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Category',
			'File')
		);

		foreach($modifiedFiles as $category => $filesArray) {
			foreach($filesArray as $id => $file) {
				$table->addRow(Array($category, $file));
			}
		}

		$table->display();
	}

	/*
	 * Disable PrestaShop core load (called by hook)
	 *
	 */
	public function disablePsCoreLoad() {
		echo "[DEBUG]: 1clickupgrade disable ps core load\n";
		$conf = PS_CLI_Configure::getInstance();
		//$conf->loadPsCore = false;
	}
}


PS_CLI_Configure::register_plugin('PS_CLI_Autoupgrade');

?>
