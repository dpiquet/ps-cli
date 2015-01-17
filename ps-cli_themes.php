<?php

class PS_CLI_THEMES {

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
		$themes = Theme::getAvailable(false);

		print_r($themes);
	}

	// todo: significative return value
	public static function install_theme($themeId) {
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

}

?>
