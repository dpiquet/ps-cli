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

class PS_CLI_Localization extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('localization', 'Manage PrestaShop localizations');
		$command->addOpt('list-languages', 'List installed languages', false)
			->addOpt('import', 'Import localization', false)
			->addOpt('enable', 'Enable language', false)
			->addOpt('disable', 'Disable language', false)
            ->addArg('<iso-code>', 'Iso code of language', false);

        $prefCommand = new PS_CLI_Command('localization-preferences', 'Manage PrestaShop localization preferences');
        $prefCommand->addOpt('show-status', 'Show localization preferences', false, 'boolean')
            ->addOpt('update', 'Update localization configuration', false, 'boolean')
            ->addOpt('key', 'Configuration key to update', false, 'string')
            ->addOpt('value', 'Value to assign', false, 'string');
		
		$this->register_command($command);
		$this->register_command($prefCommand);
	}

	public function run() {
        $arguments = PS_CLI_Arguments::getArgumentsInstance();
        $interface = PS_CLI_Interface::getInterface();
        $command = $arguments->getCommand();

		if($arguments->getOpt('list-languages', false)) {
			$this->list_languages();
		}
		elseif($arguments->getOpt('show-status', false)) {
			$this->show_status();
		}
		elseif($id = $arguments->getOpt('enable', false)) {
			$this->enable_language($id);
		}
		elseif($id = $arguments->getOpt('disable', false)) {
			$this->disable_language($id);
		}
		elseif($isoCode = $arguments->getOpt('import', false)) {
			//todo: allow partial imports
			$this->import_language($isoCode, 'all', true);
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
			$arguments->show_command_usage($command);
			$interface->error();
		}

		$interface->success();
	}

	public function show_status() {

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

        PS_CLI_Utils::add_configuration_value($table, 'PS_LANG_DEFAULT', 'Default language');
        PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_DETECT_LANG', 'Set language from browser');
        PS_CLI_Utils::add_configuration_value($table, 'PS_COUNTRY_DEFAULT', 'Default country');
        PS_CLI_Utils::add_configuration_value($table, 'PS_TIMEZONE', 'Default timezone');
        PS_CLI_Utils::add_configuration_value($table, 'PS_CURRENCY_DEFAULT', 'Default, currency');
        PS_CLI_Utils::add_configuration_value($table, 'PS_WEIGHT_UNIT', 'Default weight unit');
        PS_CLI_Utils::add_configuration_value($table, 'PS_DISTANCE_UNIT', 'Default distance unit');
        PS_CLI_Utils::add_configuration_value($table, 'PS_VOLUME_UNIT', 'Default volume unit');
        PS_CLI_Utils::add_configuration_value($table, 'PS_DIMENSION_UNIT', 'Default dimension unit');
        PS_CLI_Utils::add_configuration_value($table, 'PS_LOCALE_LANGUAGE', 'Webserver locale ISO code');
        PS_CLI_Utils::add_configuration_value($table, 'PS_LOCALE_COUNTRY', 'Webserver country iso code');

        $table->display();
    }

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {
            case 'PS_LANG_DEFAULT':
                if(Validate::isUnsignedId($value)) {
                    $obj = new Language((int)$value);

                    if(Validate::isLoadedObject($obj)) {
                        $validValue = true;
                    }
                    else {
                        $interface->error("No language with ID: $value could be loaded");
                    }
                }
                break;

            case 'PS_DETECT_LANG':
                $validValue = Validate::isBool($value);
                break;

            case 'PS_COUNTRY_DEFAULT':
                if(Validate::isUnsignedId($value)) {
                    $obj = new Country((int)$value);

                    if(Validate::isLoadedObject($obj)) {
                        $validValue = true;
                    }
                    else {
                        $interface->error("No country with ID $value could be loaded");
                    }
                }
                break;

            case 'PS_TIMEZONE':
                $validValue = Validate::isAnything($value);
                break;

            case 'PS_CURRENCY_DEFAULT':
                if(Validate::isUnsignedId($value)) {
                    $obj = new Currency((int)$value);

                    if(Validate::isLoadedObject($obj)) {
                        $validValue = true;
                    }
                    else {
                        $interface->error("Could not load a currency with ID '$value'");
                    }
                }
                break;

            case 'PS_WEIGHT_UNIT':
            case 'PS_VOLUME_UNIT':
                $validValue = Validate::isWeightUnit($value);
                break;

            case 'PS_DISTANCE_UNIT':
            case 'PS_DIMENSION_UNIT':
                $validValue = Validate::isDistanceUnit($value);
                break;

            case 'PS_LOCALE_LANGUAGE':
            case 'PS_LOCALE_COUNTRY':
                $validValue = Validate::isLanguageIsoCode($value);
                break;

            default:
                $interface->error("The configuration key '$key' is not handled by this command");
                break;
        }

        if(!$validValue) {
            $interface->error("'$value' is not a valid value for configuration key '$key'");
        }

        if(PS_CLI_Utils::update_configuration_value($value)) {
            $interface->success("Successfully updated configuration key '$key'");
        }
        else {
            $interface->error("Could not update configuration key '$key'");
        }
    }

	public function list_languages() {
		$languages = Language::getLanguages(false, false);

		$defaultLang = Configuration::get('PS_LANG_DEFAULT');

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'id',
			'name',
			'active',
			'iso_code',
			'language_code',
			'date_format_lite',
			'date_format_full',
			'is_rtl',
			'Default'
			)
		);

		foreach($languages as $lang) {
			$table->addRow(Array(
				$lang['id_lang'],
				$lang['name'],
				$lang['active'],
				$lang['iso_code'],
				$lang['language_code'],
				$lang['date_format_lite'],
				$lang['date_format_full'],
				$lang['is_rtl'],
				($lang['id_lang'] == $defaultLang ? 'X' : '')
				)
			);
		}

		$table->display();
	}

	public static function import_language($isoCode, $imports, $fromLocalPack = true) {
		//todo
		// from localization directory; or from prestashop.com (api ?)

		if($imports == 'all') {
			$selection = Array(
				'states',
				'taxes',
				'currencies',
				'languages',
				'units',
				'groups'
			);
		}
		else {
			$selection = split(',', $imports);

			foreach($selection as $selected) {
				if(!Validate::isLocalizationPackSelection($selected)) {
					echo "$selected is not a valid selection !\n";
					return false;
				}
			}
		}

		if($fromLocalPack) {
			if(defined('_PS_HOST_MODE_')) {
				$localizationPackFileName = _PS_CORE_DIR_.'/localization/'.$isoCode.'.xml';
			}
			else {
				$localizationPackFileName = _PS_ROOT_DIR_.'/localization/'.$isoCode.'.xml';
			}

			if(!is_readable($localizationPackFileName)) {
				echo "Could not read localization pack !\n";
				return false;
			}

			$pack = @Tools::file_get_contents($localizationPackFileName);
		}
		else {
			//todo: get content from prestashop servers
			$pack = false;
		}

		//todo: IN PROGRESS  seems this installs the language pack ?
		$localizationPack = new LocalizationPack();
		if(!$localizationPack->loadLocalisationPack($pack, $selection, false, $isoCode)) {
			echo "Could not load localization pack\n";
			return false;
		}

		// import lang pack
		Language::checkAndAddLanguage($isoCode, $localizationPack, false, null);
	}

	public static function enable_language($isoCode) {
		//first get id from isocode
		$langId = Language::getIdByIso($isoCode);

		$language = new Language($langId);

		if(Validate::isLoadedObject($language)) {
			if($language->active == 1) {
				echo "Language $language->name is already enabled\n";
				return true;
			}

			$language->active = 1;
			$language->save();

			echo "Successfully enabled language $language->name\n";
		}
		else {
			echo "Error, could not find language with ID $langId\n";
			return false;
		}

		return true;
	}

	public static function disable_language($isoCode) {
		//first get id from isocode
		$langId = Language::getIdByIso($isoCode);

		$language = new Language($langId);

		// make sure we got at least a language and default language is not deleted
		$defaultLang = Configuration::get('PS_LANG_DEFAULT');
		if($langId == $defaultLang) {
			echo "Error, you can not disable a language when it is the shop default lang\n";
			return false;
		}

		if(Validate::isLoadedObject($language)) {
			$language->active = 0;
			$language->save();

			echo "Successfully disabled language $language->name\n";
		}
		else {
			echo "Error, could not find language with ID $langId\n";
			return false;
		}
	}

	public static function delete_language($isoCode) {
		//todo
	}

	public static function list_available_local_xml() {
		//print out a table | iso_code | file.xml |


	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Localization');

?>
