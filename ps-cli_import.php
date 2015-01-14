<?php

class PS_CLI_IMPORT {

	public static function csv_import($type, $file) {


		if(!file_exists($file)) {
			echo("Error, $file does not exits\n");
			return false;
		}
		

	}

	public static function csv_import_categories($file, $skip = 1, $iso88591 = false, $separator = ';') {
		
		if(!file_exists($file)) {
			echo("Error, $file does not exits\n");
			return false;
		}

		//set language (important for searchByName)
		
		//opencsvfile
		$FH = false;
		$FH = fopen($file, 'r');
		if(!$FH) {
			echo "Error, could not open csv file\n";
			return false;
		}

		//avoid BOM pbs
		rewind($FH);
		if (($bom = fread($handle, 3)) != "\xEF\xBB\xBF") {
                        rewind($handle);
		}

		//skip header lines
		for($a = 0; $a < $skip; $a++) {
			//max_line_size defined in AdminImportController.php
			$line = fgetcsv($FH, MAX_LINE_SIZE, $separator);
		}

		

		//validate fields ?

		//array walk | callback: fillInfo

		//parent name: Category::searchByName($id_lang, $category->parent, true);




		fclose($FH);
		return true;

	}


	public static function csv_export($what) {
		$exportable = Array(
			'categories'
		);

		switch($what) {

			case 'categories':	
				self::_csv_export_categories();
				break;

			case 'products':
				self::_csv_export_products();
				break;

			default:
				echo "Unknown data $what\n";
				return false;
		}
	}

	private static function _csv_export_products() {
		$lang = PS_CLI_UTILS::$LANG;

		$products = Product::getProducts($lang, 0, PHP_INT_MAX, 'id_product', 'ASC');

		$FH = fopen('php://output', 'w');

		foreach ($products as $product) {

			print_r($product);


			
		}

		fclose($FH);
	}

	private static function _csv_export_categories() {
		$categories = Category::getCategories();

		$FH = fopen('php://output', 'w');

		$separator = ';';


		// notes
		//   parent category shoud be exported as human readable (not id)
		//   see http://pastebin.com/ARsTYmvQ for importable fields

		//print a header	

		$csvVals = Array(
			'id_category',
			'id_parent',	
			'id_shop_default',
			'active',
			'date_add',
			'date_upd',
			'position',
			'is_root_category',
			'id_shop',
			'id_lang',
			'name',
			'description',
			'link_rewrite',
			'meta_title',
			'meta_keywords',
			'meta_description'
		);

		fputcsv($FH, $csvVals, $separator);

		foreach($categories as $category) {
			foreach($category as $curCat) {

				$csvVals = Array(
					$curCat['infos']['id_category'],
					$curCat['infos']['id_parent'],
					$curCat['infos']['id_shop_default'],
					$curCat['infos']['active'],
					$curCat['infos']['date_add'],
					$curCat['infos']['date_upd'],
					$curCat['infos']['position'],
					$curCat['infos']['is_root_category'],
					$curCat['infos']['id_shop'],
					$curCat['infos']['id_lang'],
					self::_csv_filter($curCat['infos']['name']),
					self::_csv_filter($curCat['infos']['description']),
					self::_csv_filter($curCat['infos']['link_rewrite']),
					self::_csv_filter($curCat['infos']['meta_title']),
					self::_csv_filter($curCat['infos']['meta_keywords']),
					self::_csv_filter($curCat['infos']['meta_description'])
				);

				fputcsv($FH, $csvVals, $separator);
			}
		}
	}

	private static function _csv_filter($content) {
		$content = Tools::safeOutput($content);

		//csv import do not like line returns
		$content = preg_replace('/\n/', '', $content);

		return $content;
	}
}


?>
