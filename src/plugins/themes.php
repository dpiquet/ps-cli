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

class PS_CLI_Themes extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('theme', 'Manage PrestaShop themes');
		$command->addOpt('list', 'List installed themes', false, 'boolean')
			->addOpt('list-available', 'List available themes', false, 'boolean')
			->addOpt('install-zip', 'Install theme from Zip archive', false, 'string')
			->addOpt('activate', 'Install theme', false, 'integer')
			->addArg('theme', 'Theme id', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if ($opt = $arguments->getOpt('list', false)) {
			$this->print_theme_list();
			exit(0);
		}

		elseif($opt = $arguments->getOpt('list-available', false)) {
			$this->print_available_themes();
			exit(0);
		}
		elseif($theme = $arguments->getOpt('activate', false)) {

			$this->activate_theme($theme);

			exit(0);
		}
		elseif($zip = $arguments->getOpt('install-zip', false)) {
			$this->install_theme_zip($zip);
		}
		else {
			$arguments->show_command_usage('theme');
			exit(1);
		}

	}

	public static function print_theme_list() {
		$themes = Theme::getThemes();
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'ID',
			'name',
			'directory',
			'responsive',
			'is used'
			)
		);

		foreach ($themes as $theme) {
			$table->addRow(Array(
				$theme->id,
				$theme->name,
				$theme->directory,
				$theme->responsive,
				$theme->isUsed()
				)
			);
		}

		$table->display();
	}

	public static function print_available_themes() {
		$installedThemesDirs = Array();

		$themes = Theme::getAvailable(false);
		$installedThemes = Theme::getThemes();

		foreach($installedThemes as $installedTheme) {
			$installedThemesDirs[] = $installedTheme->directory;
		}

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Directory',
			'Installed'
			)
		);

		foreach ($themes as $theme) {
			//check if theme is installed

			if(array_search($theme,$installedThemesDirs) === false) {
				$isInstalled = 'No';
			}
			else {
				$isInstalled = 'Yes';
			}

			$table->addRow(Array($theme, $isInstalled));	
		}

		$table->display();
	}

	//todo; verbose output (also in subfunctions)
	public static function install_theme_zip($themeZip) {
		//test zip
		if(!Tools::ZipTest($themeZip)) {
			echo "Error, $themeZip is not a valid Zip archive\n";
			return false;
		}

		$uniqid = uniqid();
		$sandbox = _PS_CACHE_DIR_.'sandbox'.DIRECTORY_SEPARATOR.$uniqid.DIRECTORY_SEPARATOR;
		mkdir($sandbox);
		if(!Tools::ZipExtract($themeZip, $sandbox)) {
			echo "Could not extract zip file\n";
			return false;
		}
		
		//process install
		self::install_theme_files($sandbox);
	}

	public static function install_preinstalled_theme($themeDir) {
		$xmlFile = _PS_ROOT_DIR_.'/config/xml/themes/'.$themeDir.'.xml';
		$errors = Array();

		if(!self::checkXmlFields($xmlFile)) {
			echo "Error, bad xml configuration file $xmlFile\n";
			return false;
		}
		
		$importedTheme = self::importThemeXmlConfig(simplexml_load_file($xmlFile));
		if($importedTheme === false) {
			echo "Error, could not install theme\n";
			return false;
		}

		echo "Successfully installed $themeDir\n";
		return true;
	}

	// todo: significative return value
	// todo: verbose output
	public static function activate_theme($themeId) {
		if(!Validate::isInt($themeId)) {
			echo "Error, $themeId is not a valid theme id\n";
			return false;
		}

		//from Prestashop's AdminThemeController.php
		$theme = new Theme($themeId);

		$context = Context::getContext();
		if($context->shop->id_theme == $themeId) {
			echo "$theme->name is already the active theme\n";
			return true;
		}


		$xml = false;

		if (file_exists(_PS_ROOT_DIR_.'/config/xml/themes/'.$theme->directory.'.xml'))
                        $xml = simplexml_load_file(_PS_ROOT_DIR_.'/config/xml/themes/'.$theme->directory.'.xml');
                elseif (file_exists(_PS_ROOT_DIR_.'/config/xml/themes/default.xml'))
                        $xml = simplexml_load_file(_PS_ROOT_DIR_.'/config/xml/themes/default.xml');

		if ($xml) {
			$module_hook = array();
			foreach ($xml->modules->hooks->hook as $row) {
				$name = strval($row['module']);

				$exceptions = (isset($row['exceptions']) ? explode(',', strval($row['exceptions'])) : array());

				if (Hook::getIdByName(strval($row['hook']))) {

					$module_hook[$name]['hook'][] = array(
						'hook' => strval($row['hook']),
						'position' => strval($row['position']),
						'exceptions' => $exceptions
					);
				}
			}

			// list modules
			$theme_module['to_install'] = Array();
			$theme_module['to_enable'] = Array();
			$theme_module['to_disable'] = Array();

			foreach ($xml->modules->module as $row) {
				if (strval($row['action']) == 'install' && !in_array(strval($row['name']), $native_modules)) {
					$theme_module['to_install'][] = strval($row['name']);
				}
				elseif (strval($row['action']) == 'enable') {
					$theme_module['to_enable'][] = strval($row['name']);
				}
				elseif (strval($row['action']) == 'disable') {
					$theme_module['to_disable'][] = strval($row['name']);
				}
			}

			$img_error = self::_updateImages($xml);

			$modules_errors = array();
			
			foreach($theme_module['to_install'] as $value) {

				$module = Module::getInstanceByName($value);
				if ($module) {
					$is_installed_success = true;

					if (!Module::isInstalled($module->name)) {
						$is_installed_success = $module->install();
					}

					if ($is_installed_success) {
						if (!Module::isEnabled($module->name)) {
							$module->enable();
						}

						if ((int)$module->id > 0 && isset($module_hook[$module->name])) {
							self::_hookModule($module->id, $module_hook[$module->name], $id_shop);
						}
					}
					else {
						$modules_errors[] = array('module_name' => $module->name, 'errors' => $module->getErrors());
					}

					unset($module_hook[$module->name]);
				}
			}

			foreach ($theme_module['to_enable'] as $value) {
				$module = Module::getInstanceByName($value);
				if ($module) {
					$is_installed_success = true;

					if (!Module::isInstalled($module->name)) {
						$is_installed_success = $module->install();
					}

					if ($is_installed_success) {
						if (!Module::isEnabled($module->name)) {
							$module->enable();
						}

						if ((int)$module->id > 0 && isset($module_hook[$module->name])) {
							self::_hookModule($module->id, $module_hook[$module->name]);
						}
					}
					else {
						$modules_errors[] = array('module_name' => $module->name, 'errors' => $module->getErrors());
					}

					unset($module_hook[$module->name]);
				}

			}
			foreach ($theme_module['to_disable'] as $value) {
				$module_obj = Module::getInstanceByName($value);
				if (Validate::isLoadedObject($module_obj)) {
					if (Module::isEnabled($module_obj->name)) {
						$module_obj->disable();
					}

					unset($module_hook[$module_obj->name]);
				}
			}

			//todo shop update / save

//			$shop = New Shop((int)$id_shop);
//			$shop->id_theme = (int)Tools::getValue('id_theme');
//			$this->context->shop->id_theme = $shop->id_theme;
//			$this->context->shop->update();
//			$shop->save();

			$context = Context::getContext();
			$context->shop->id_theme = $themeId;
			$context->shop->update();

			if (Shop::isFeatureActive()) {
				Configuration::updateValue('PS_PRODUCTS_PER_PAGE', (int)$theme->product_per_page);
			}
			else {
				Configuration::updateValue('PS_PRODUCTS_PER_PAGE', (int)$theme->product_per_page);
			}

			$doc = array();
			foreach ($xml->docs->doc as $row) {
				$doc[strval($row['name'])] = __PS_BASE_URI__.'themes/'.$theme->directory.'/docs/'.basename(strval($row['path']));
			}
		}

		PS_CLI_CORE::clear_smarty_cache();

		echo "Successfully installed theme $theme->name\n";
		return true;
	}

	private static function _updateImages($xml) {

		$return = Array();

                if (isset($xml->images->image)) {
                        foreach ($xml->images->image as $row) {
                                Db::getInstance()->delete('image_type', '`name` = \''.pSQL($row['name']).'\'');
                                        Db::getInstance()->execute('
                                        INSERT INTO `'._DB_PREFIX_.'image_type` (`name`, `width`, `height`, `products`, `categories`, `manufacturers`, `suppliers`, `scenes`)
                                        VALUES (\''.pSQL($row['name']).'\',
                                                '.(int)$row['width'].',
                                                '.(int)$row['height'].',
                                                '.($row['products'] == 'true' ? 1 : 0).',
                                                '.($row['categories'] == 'true' ? 1 : 0).',
                                                '.($row['manufacturers'] == 'true' ? 1 : 0).',
                                                '.($row['suppliers'] == 'true' ? 1 : 0).',
                                                '.($row['scenes'] == 'true' ? 1 : 0).')');

                                        $return['ok'][] = array(
                                                'name' => strval($row['name']),
                                                'width' => (int)$row['width'],
                                                'height' => (int)$row['height']
                                        );
                        }
		}

		return $return;

	}

	private static function _hookModule($id_module, $module_hooks) {

		$context = Context::getContext();
		$shop = $context->shop->id;

		Db::getInstance()->execute('INSERT IGNORE INTO '._DB_PREFIX_.'module_shop (id_module, id_shop) VALUES('.(int)$id_module.', '.(int)$shop.')');

		Db::getInstance()->execute($sql = 'DELETE FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '.(int)$id_module.' AND id_shop = '.(int)$shop);

		foreach ($module_hooks as $hooks) {
			foreach ($hooks as $hook) {
				$sql_hook_module = 'INSERT INTO `'._DB_PREFIX_.'hook_module` (`id_module`, `id_shop`, `id_hook`, `position`)
									VALUES ('.(int)$id_module.', '.(int)$shop.', '.(int)Hook::getIdByName($hook['hook']).', '.(int)$hook['position'].')';

				if (count($hook['exceptions']) > 0) {
					foreach ($hook['exceptions'] as $exception) {
						$sql_hook_module_except = 'INSERT INTO `'._DB_PREFIX_.'hook_module_exceptions` (`id_module`, `id_hook`, `file_name`) VALUES ('.(int)$id_module.', '.(int)Hook::getIdByName($hook['hook']).', "'.pSQL($exception).'")';

						Db::getInstance()->execute($sql_hook_module_except);
					}
				}
				Db::getInstance()->execute($sql_hook_module);
			}
		}
	}

	private static function install_theme_files($sandbox) {
		$xmlFile = $sandbox.'/Config.xml';
		$errors = Array();

		if(!self::checkXmlFields($xmlFile)) {
			echo "Error, bad xml configuration file $xmlFile\n";
			return false;
		}
		
		$importedTheme = self::importThemeXmlConfig(simplexml_load_file($xmlFile));
		if($importedTheme === false) {
			echo "Error, could not install theme\n";
			return false;
		}

		foreach ($importedTheme as $theme) {
			if(Validate::isLoadedObject($theme)) {
				if (!copy($sandbox.'/Config.xml', _PS_ROOT_DIR_.'/config/xml/themes/'.$theme->directory.'.xml'))
				$errors[] = "Can't copy configuration file";

				$target_dir = _PS_ALL_THEMES_DIR_.$theme->directory;
				if (file_exists($target_dir))
					Tools::deleteDirectory($target_dir);

				$theme_doc_dir = $target_dir.'/docs/';
				if (file_exists($theme_doc_dir))
					Tools::deleteDirectory($theme_doc_dir);

				mkdir($target_dir);
				mkdir($theme_doc_dir);

				Tools::recurseCopy($sandbox.'/themes/'.$theme->directory.'/', $target_dir.'/');
				Tools::recurseCopy($sandbox.'/doc/', $theme_doc_dir);
				Tools::recurseCopy($sandbox.'/modules/', _PS_MODULE_DIR_);
			}
			else {
				$errors[] = $theme;
			}
		}

		Tools::deleteDirectory($sandbox);

		if(!count($errors)) {
			echo "Sucessfully installed theme files\n";
			return true;
		}
		else {
			echo "Error while installing theme files\n";
			return false;
		}
	}

	//from PrestaShop AdminThemeController.php
	private function checkXmlFields($xml_file) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		if (!file_exists($xml_file) || !$xml = simplexml_load_file($xml_file)) {
			if($configuration->verbose) {
				echo "Error, cannot load xml file\n";
			}
			return false;
		}

		if (!$xml['version'] || !$xml['name'])
			return false;

		foreach ($xml->variations->variation as $val) {
			if (!$val['name'] || !$val['directory'] || !$val['from'] || !$val['to'])
				return false;
		}

		foreach ($xml->modules->module as $val) {
			if (!$val['action'] || !$val['name'])
				return false;
		}

		foreach ($xml->modules->hooks->hook as $val) {
			if (!$val['module'] || !$val['hook'] || !$val['position'])
				return false;
		}

		return true;
	}

	// Adapted from PrestaShop AdminThemeController.php
	public static function isThemeInstalled($themeName) {

		$themes = Theme::getThemes();

		foreach ($themes as $themeObject) {
			if($themeObject->name == $themeName)
				return true;
		}
		return false;
	}

	//Adapted from PrestaShop AdminThemeController.php
	private static function importThemeXmlConfig(SimpleXMLElement $xml, $theme_dir = false) {
		$attr = $xml->attributes();
		$th_name = (string)$attr->name;
		if (self::isThemeInstalled($th_name)) {
			echo "Theme $th_name is already installed";
			exit(0);
		}

		$new_theme_array = array();
		foreach ($xml->variations->variation as $variation)
		{
			$name = strval($variation['name']);

			$new_theme = new Theme();
			$new_theme->name = $name;

			$new_theme->directory = strval($variation['directory']);

			if ($theme_dir)
			{
				$new_theme->name = $theme_dir;
				$new_theme->directory = $theme_dir;
			}

			if (self::isThemeInstalled($new_theme->name))
				continue;

			$new_theme->product_per_page = Configuration::get('PS_PRODUCTS_PER_PAGE');

			if (isset($variation['product_per_page']))
				$new_theme->product_per_page = intval($variation['product_per_page']);

			$new_theme->responsive = false;
			if (isset($variation['responsive']))
				$new_theme->responsive = (bool)strval($variation['responsive']);

			$new_theme->default_left_column = true;
			$new_theme->default_right_column = true;

			if (isset($variation['default_left_column']))
				$new_theme->default_left_column = (bool)strval($variation['default_left_column']);

			if (isset($variation['default_right_column']))
				$new_theme->default_right_column = (bool)strval($variation['default_right_column']);

			$fill_default_meta = true;
			$metas_xml = array();
			if ($xml->metas->meta)
			{
				foreach ($xml->metas->meta as $meta)
				{
					$meta_id = Db::getInstance()->getValue('SELECT id_meta FROM '._DB_PREFIX_.'meta WHERE page=\''.pSQL($meta['meta_page']).'\'');
					if ((int)$meta_id > 0)
					{
						$tmp_meta = array();
						$tmp_meta['id_meta'] = (int)$meta_id;
						$tmp_meta['left'] = intval($meta['left']);
						$tmp_meta['right'] = intval($meta['right']);
						$metas_xml[(int)$meta_id] = $tmp_meta;
					}
				}
				$fill_default_meta = false;
				if (count($xml->metas->meta) < (int)Db::getInstance()->getValue('SELECT count(*) FROM '._DB_PREFIX_.'meta'))
					$fill_default_meta = true;

			}

			if ($fill_default_meta == true)
			{
				$metas = Db::getInstance()->executeS('SELECT id_meta FROM '._DB_PREFIX_.'meta');
				foreach ($metas as $meta)
				{
					if (!isset($metas_xml[(int)$meta['id_meta']]))
					{
						$tmp_meta['id_meta'] = (int)$meta['id_meta'];
						$tmp_meta['left'] = $new_theme->default_left_column;
						$tmp_meta['right'] = $new_theme->default_right_column;
						$metas_xml[(int)$meta['id_meta']] = $tmp_meta;
					}
				}
			}

			if (!is_dir(_PS_ALL_THEMES_DIR_.$new_theme->directory))
				if (!mkdir(_PS_ALL_THEMES_DIR_.$new_theme->directory)) {
					echo "Error, could not create directory $new_theme->directory\n";
					return false;
				}

			$new_theme->add();

			if ($new_theme->id > 0)
			{
				$new_theme->updateMetas($metas_xml);
				$new_theme_array[] = $new_theme;
			}
			else {
				echo "Error while installing theme $new_theme->name\n";
				return false;
			}

		}

		return $new_theme_array;
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Themes');

?>
