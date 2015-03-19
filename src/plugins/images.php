<?php

class PS_CLI_IMAGES extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('image', 'Manage PrestaShop images');
		$command->addOpt('list', 'List images', false)
			->addOpt('regenerate-thumbs', 'Regenerate thumbnails', false)
			->addOpt('category', 'Specify images category for thumbnails regeneration (all, products, categories, manufacturers, suppliers, scenes, stores)', false, 'string')
			->addOpt('keep-old-images', 'Keep old images', false)
			->addOpt('show-status', 'Show configuration', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if ($opt = $arguments->getOpt('list', false)) {
			$this->list_images();
		}
		elseif($arguments->getOpt('show-status', false)) {
			$this->show_status();
		}
		elseif ($opt = $arguments->getOpt('regenerate-thumbs', false)) {

			if($category = $arguments->getOpt('category', false)) {
				$cats = Array(
					'categories',
					'manufacturers',
					'suppliers',
					'scenes',
					'products',
					'stores',
					'all'
				);

				if (!in_array($category, $cats)) {
					$error = '--category must be ';

					foreach ($cats as $cat) {
						$error .= $cat. ' ';
					}

					$this->_show_command_usage('image', $error);
					exit(1);
				}
			}
			else { $category = 'all'; }

			if ($keepOld = $arguments->getOpt('keep-old-images', false)) {
				$deleteOldImages = false;
			}
			else { $deleteOldImages = true; }

			$this->regenerate_thumbnails($category, $deleteOldImages);
		}
		else {
			$arguments->show_command_usage('image');
			exit(1);
		}

		exit (0);

	}

	//TODO: delete old support

	// adapted from PrestaShop AdminImagesController.php
	public static function regenerate_thumbnails($regType = 'all', $deleteOldImages = true) {
		$process = Array(
			Array('type' => 'categories', 'dir' => _PS_CAT_IMG_DIR_),
			Array('type' => 'manufacturers', 'dir' => _PS_MANU_IMG_DIR_),
			Array('type' => 'suppliers', 'dir' => _PS_SUPP_IMG_DIR_),
			Array('type' => 'scenes', 'dir' => _PS_SCENE_IMG_DIR_),
			Array('type' => 'products', 'dir' => _PS_PROD_IMG_DIR_),
			Array('type' => 'stores', 'dir' => _PS_STORE_IMG_DIR_)
		);

		$languages = Language::getLanguages(false);

		foreach($process as $proc) {

			if (($regType != 'all') && ($regType != $proc['type'])) {
				continue;
			}

			echo "Regenerating ".$proc['type']." thumbnails\n";

			$formats = ImageType::getImagesTypes($proc['type']);

			if ($proc['type'] == 'products') {
				$isProduct = true;
			}
			else { $isProduct = false; }

			if ($deleteOldImages) {
				self::_delete_old_images($proc['dir'], $formats, $isProduct);
			}

			$ret = self::_regenerate_new_images($proc['dir'], $formats, $isProduct);

			if($ret) {
				if($proc['type'] == 'products') {
					//regenerate watermarks
					self::_regenerate_watermarks($proc['dir']);
				}

				//regenerate no pictures
				self::_regenerate_no_pic_images($proc['dir'], $formats, $languages);
			}	
		}

		//todo return real value
		return true;
	}

	private static function _regenerate_watermarks($dir) {
		$result = Db::getInstance()->executeS('
                SELECT m.`name` FROM `'._DB_PREFIX_.'module` m
                LEFT JOIN `'._DB_PREFIX_.'hook_module` hm ON hm.`id_module` = m.`id_module`
                LEFT JOIN `'._DB_PREFIX_.'hook` h ON hm.`id_hook` = h.`id_hook`
                WHERE h.`name` = \'actionWatermark\' AND m.`active` = 1');

                if ($result && count($result)) {

                        $productsImages = Image::getAllImages();
                        foreach ($productsImages as $image) {

                                $imageObj = new Image($image['id_image']);
                                if (file_exists($dir.$imageObj->getExistingImgPath().'.jpg')) {

                                        foreach ($result as $module) {

                                                $moduleInstance = Module::getInstanceByName($module['name']);

                                                if ($moduleInstance && is_callable(array($moduleInstance, 'hookActionWatermark'))) {
                                                        call_user_func(array($moduleInstance, 'hookActionWatermark'), array('id_image' => $imageObj->id, 'id_product' => $imageObj->id_product));
						}

//                                                if (time() - $this->start_time > $this->max_execution_time - 4) // stop 4 seconds before the tiemout, just enough time to process the end of the page on a slow server
//                                                        return 'timeout';
                                        }
				}
                	}
        	}
	}

	private static function _regenerate_no_pic_images($dir, $formats, $languages) {

		$errors = false;

                foreach ($formats as $image_type) {

                        foreach ($languages as $language) {

                                $file = $dir.$language['iso_code'].'.jpg';
                                if (!file_exists($file)) {
                                        $file = _PS_PROD_IMG_DIR_.Language::getIsoById((int)Configuration::get('PS_LANG_DEFAULT')).'.jpg';
				}

                                if (!file_exists($dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.jpg')) {

                                        if (!ImageManager::resize($file, $dir.$language['iso_code'].'-default-'.stripslashes($image_type['name']).'.jpg', (int)$image_type['width'], (int)$image_type['height'])) {

                                                $errors = true;
					}
				}
                        }
		}

                return $errors;
	}

	// adapted from PrestaShop's AdminImagesController.php
	private static function _regenerate_new_images($dir, $formats, $isProduct) {
		if (!is_dir($dir)) {
			return false;
		}

		$errors = false;

		if(!$isProduct) {
			foreach (scandir($dir) as $image) {
				if (preg_match('/^[0-9]*\.jpg$/', $image)) {
                                        foreach ($formats as $k => $imageType) {
                                                // Customizable writing dir
                                                $newDir = $dir;

                                                if ($imageType['name'] == 'thumb_scene') {
                                                        $newDir .= 'thumbs/';
						}

                                                if (!file_exists($newDir)) {
                                                        continue;
						}

                                                if (!file_exists($newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg')) {
                                                        if (!file_exists($dir.$image) || !filesize($dir.$image)) {
                                                                $errors = true;
                                                                echo (sprintf(Tools::displayError('Source file does not exist or is empty (%s)'), $dir.$image));
                                                        }
                                                        elseif (!ImageManager::resize($dir.$image, $newDir.substr($image, 0, -4).'-'.stripslashes($imageType['name']).'.jpg', (int)$imageType['width'], (int)$imageType['height'])) {
                                                                $errors = true;
                                                                echo ( sprintf(Tools::displayError('Failed to resize image file (%s)'), $dir.$image));
                                                        }
                                                }

//                                                if (time() - $this->start_time > $this->max_execution_time - 4) // stop 4 seconds before the timeout, just enough time to process the end of the page on a slow server
//                                                        return 'timeout';
                                        }
				}
                	}
		}
		else {
                        foreach (Image::getAllImages() as $image) {
                                $imageObj = new Image($image['id_image']);
                                $existing_img = $dir.$imageObj->getExistingImgPath().'.jpg';

                                if (file_exists($existing_img) && filesize($existing_img)) {
                                        foreach ($formats as $imageType) {
                                                if (!file_exists($dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.jpg')) {
                                                        if (!ImageManager::resize($existing_img, $dir.$imageObj->getExistingImgPath().'-'.stripslashes($imageType['name']).'.jpg', (int)($imageType['width']), (int)($imageType['height']))) {
                                                                $errors = true;
                                                                echo ( Tools::displayError(sprintf('Original image is corrupt (%s) for product ID %2$d or bad permission on folder', $existing_img, (int)$imageObj->id_product)));
                                                        }
						}
					}
                                }
                                else {
                                        $errors = true;
                                        echo (Tools::displayError(sprintf('Original image is missing or empty (%1$s) for product ID %2$d', $existing_img, (int)$imageObj->id_product)));
                                }
//                                if (time() - $this->start_time > $this->max_execution_time - 4) // stop 4 seconds before the tiemout, just enough time to process the end of the page on a slow server
//                                        return 'timeout';
                        }
                }

		return !$errors;
	}

	// adapted from PrestaShop's AdminImageController.php
	private static function _delete_old_images($dir, $type, $product = false) {
		if (!is_dir($dir)) {
                        return false;
		}

                $toDel = scandir($dir);

                foreach ($toDel as $d) {
                        foreach ($type as $imageType) {
                                if (preg_match('/^[0-9]+\-'.($product ? '[0-9]+\-' : '').$imageType['name'].'\.jpg$/', $d) 
                                        || (count($type) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.jpg$/', $d))
                                        || preg_match('/^([[:lower:]]{2})\-default\-'.$imageType['name'].'\.jpg$/', $d)) {
                                        if (file_exists($dir.$d)) {
                                                unlink($dir.$d);
					}
				}
			}
		}

                // delete product images using new filesystem.
                if ($product) {

                        $productsImages = Image::getAllImages();
                        foreach ($productsImages as $image) {

                                $imageObj = new Image($image['id_image']);
                                $imageObj->id_product = $image['id_product'];

                                if (file_exists($dir.$imageObj->getImgFolder())) {

                                        $toDel = scandir($dir.$imageObj->getImgFolder());

                                        foreach ($toDel as $d) {

                                                foreach ($type as $imageType) {

                                                        if (preg_match('/^[0-9]+\-'.$imageType['name'].'\.jpg$/', $d) || (count($type) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.jpg$/', $d))) {

                                                                if (file_exists($dir.$imageObj->getImgFolder().$d)) {
                                                                        unlink($dir.$imageObj->getImgFolder().$d);
								}
							}
						}
					}
                                }
                        }
                }

		return true;
	}

	// todo get images full path
	public static function list_images() {
		$context = Context::getContext();
		$images = Image::getAllImages();

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'id_image', 
			'id_product',
			'product name',
			'path'
			)
		);

		foreach($images as $image) {
			$product = new Product($image['id_product']);
			$img = new Image($image['id_image']);

			//print_r($img);
			//die();

			$table->addRow(Array(
				$image['id_image'],
				$image['id_product'],
				$product->name[$context->language->id],
				$img->getImgPath() .'.'. $img->image_format
				)
			);
		}

		$table->display();

		return;
	}

	public static function show_status() {
				
		$table = new Cli\Table();
		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_IMAGE_QUALITY', 'Image format (jpg, png, png_all)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_JPEG_QUALITY', 'Jpeg compression (0-100)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PNG_QUALITY', 'Jpeg compression (0-9)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_IMAGE_GENERATION_METHOD', '(0=auto, 1=width, 2=height)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCT_PICTURE_MAX_SIZE', 'Maximum file size of customer pictures (in bytes)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCT_PICTURE_WIDTH', 'Width of product pictures custumers can upload (in px)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_PRODUCT_PICTURE_HEIGHT', 'Height of product pictures custumers can upload (in px)');

		$table->display();

		return;

	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Images');

?>
