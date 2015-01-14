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

			default:
				echo "Unknown data $what\n";
				return false;
		}
	}

	private static function _csv_export_categories() {
		$categories = Category::getCategories();

		$separator = ';';


		// notes
		//   parent category shoud be exported as human readable (not id)
		//   see http://pastebin.com/ARsTYmvQ for importable fields

		//print a header	
		echo 'id_category' 	. $separator .
		'id_parent' 		. $separator .
		'id_shop_default' 	. $separator .
		'active' 		. $separator .
		'date_add' 		. $separator .
		'date_upd' 		. $separator .
		'position' 		. $separator .
		'is_root_category' 	. $separator .
		'id_shop' 		. $separator .
		'id_lang' 		. $separator .
		'name' 			. $separator .
		'description' 		. $separator .
		'link_rewrite' 		. $separator .
		'meta_title' 		. $separator .
		'meta_keywords' 	. $separator .
		'meta_description';

		echo "\n";

		foreach($categories as $category) {
			foreach($category as $curCat) {
//				print_r($curCat['infos']);

				echo $curCat['infos']['id_category'] 	. $separator .
				$curCat['infos']['id_parent'] 		. $separator .
				$curCat['infos']['id_shop_default'] 	. $separator .
				$curCat['infos']['active'] 		. $separator .
				$curCat['infos']['date_add'] 		. $separator .
				$curCat['infos']['date_upd'] 		. $separator .
				$curCat['infos']['position'] 		. $separator .
				$curCat['infos']['is_root_category'] 	. $separator .
				$curCat['infos']['id_shop'] 		. $separator .
				$curCat['infos']['id_lang'] 		. $separator .
				$curCat['infos']['name'] 		. $separator .
				$curCat['infos']['description'] 	. $separator .
				$curCat['infos']['link_rewrite'] 	. $separator .
				$curCat['infos']['meta_title'] 		. $separator .
				$curCat['infos']['meta_keywords'] 	. $separator .
				$curCat['infos']['meta_description'];

				echo "\n";
				
			}
		}
	}
}


?>
