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

			case 'customers':
				self::_csv_export_customers();
				break;

			case 'manufacturers':
				self::_csv_export_manufacturers();
				break;

			case 'suppliers':
				self::_csv_export_suppliers();
				break;

			case 'orders':
				self::_csv_export_orders();
				break;

			default:
				echo "Unknown data $what\n";
				return false;
		}
	}

	private static function _csv_export_products() {
		$lang = PS_CLI_UTILS::$LANG;

		//we must update the context before running getProducts()
		$context = Context::getContext();
		$context->controller = new AdminProductsController();

		$separator = ';';

		$products = Product::getProducts($lang, 0, PHP_INT_MAX, 'id_product', 'ASC');

		$FH = fopen('php://output', 'w');

		//print a header
		$csvVals = Array(
			'ID',
			'name',
			'active',
			'manufacturer',
			'id_supplier',
			'available_for_order',
			'available_now',
			'available_later',
			'ean13',
			'upc',
			'reference',
			'supplier_reference',
			'width',
			'height',
			'depth',
			'price',
			'wholesale_price',
			'additional_shipping_cost',
			'online_only',
			'id_tax_rules_group',
			'ecotax',
			'minimal_quantity',
			//0 = deny; 1 = allow; 2 = default
			'out_of_stock',
			'quantity',
			'condition',
			'customizable',
			'date_add',
			'date_upd',
			'available_for_order',
			'visibility',
			'description',
			'description_short',
			'meta_keywords',
			'meta_description',
			'meta_title',
			'link_rewrite'
		);

		fputcsv($FH, $csvVals, $separator);

		foreach ($products as $product) {

			//print_r($product);
			$csvVals = Array(
				'ID',
				self::_csv_filter($product['name']),
				$product['active'],
				self::_csv_filter($product['manufacturer_name']),
				$product['id_supplier'],
				$product['available_for_order'],
				self::_csv_filter($product['available_now']),
				self::_csv_filter($product['available_later']),
				$product['ean13'],
				$product['upc'],
				self::_csv_filter($product['reference']),
				self::_csv_filter($product['supplier_reference']),
				$product['width'],
				$product['height'],
				$product['depth'],
				$product['price'],
				$product['wholesale_price'],
				$product['additional_shipping_cost'],
				$product['online_only'],
				$product['id_tax_rules_group'],
				$product['ecotax'],
				$product['minimal_quantity'],
				//0 = deny; 1 = allow; 2 = default
				$product['out_of_stock'],
				$product['quantity'],
				$product['condition'],
				$product['customizable'],
				$product['date_add'],
				$product['date_upd'],
				$product['available_for_order'],
				$product['visibility'],
				self::_csv_filter($product['description']),
				self::_csv_filter($product['description_short']),
				self::_csv_filter($product['meta_keywords']),
				self::_csv_filter($product['meta_description']),
				self::_csv_filter($product['meta_title']),
				self::_csv_filter($product['link_rewrite'])
			);

			fputcsv($FH, $csvVals, $separator);
			
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

	private static function _csv_export_customers() {
		$separator = ';';

		$customers = Customer::getCustomers();

		$FH = fopen('php://output', 'w');

		$csvVals = Array(
			'id_customer',
			'email',
			'firstname',
			'lastname'
		);

		fputcsv($FH, $csvVals, $separator);

		foreach ($customers as $customer) {
			$csvVals = Array(
				$customer['id_customer'],
				$customer['email'],
				$customer['firstname'],
				$customer['lastname']
			);

			fputcsv($FH, $csvVals, $separator);
		}
	}

	private static function _csv_export_manufacturers() {
		$separator = ';';

		$manufacturers = Manufacturer::getManufacturers(false, PS_CLI_UTILS::$LANG, false);

		$FH = fopen('php://output', 'w');

		$csvVals = Array(
			'id_manufacturer',
			'name',
			'date_add',
			'date_upd',
			'active',
			'description',
			'short_description',
			'link_rewrite'
		);

		fputcsv($FH, $csvVals, $separator);

		foreach($manufacturers as $manufacturer) {

			$csvVals = Array(
				$manufacturer['id_manufacturer'],
				self::_csv_filter($manufacturer['name']),
				$manufacturer['date_add'],
				$manufacturer['date_upd'],
				$manufacturer['active'],
				self::_csv_filter($manufacturer['description']),
				self::_csv_filter($manufacturer['short_description']),
				self::_csv_filter($manufacturer['link_rewrite'])
			);

			fputcsv($FH, $csvVals, $separator);
		}

		fclose($FH);
	}

	private static function _csv_export_suppliers() {
		$separator = ';';

		$suppliers = Supplier::getSuppliers(false, PS_CLI_UTILS::$LANG, false);

		$FH = fopen('php://output', 'w');

		$csvVals = Array(
			'id_supplier',
			'name',
			'date_add',
			'date_upd',
			'active',
			'description',
			'link_rewrite'
		);

		fputcsv($FH, $csvVals, $separator);

		foreach ($suppliers as $supplier) {

			$csvVals = Array(
				$supplier['id_supplier'],
				self::_csv_filter($supplier['name']),
				$supplier['date_add'],
				$supplier['date_upd'],
				$supplier['active'],
				self::_csv_filter($supplier['description']),
				self::_csv_filter($supplier['link_rewrite'])
			);

			fputcsv($FH, $csvVals, $separator);
		}
	}

	private static function _csv_export_orders() {
		$separator = ';';

		$FH = fopen('php://output', 'w');

		$csvVals = Array(
			'id_order',
			'reference',
			'customer_firstname',
			'customer_lastname',
			'customer_email',
			'payment',
			'module',
			'current_state',
			'state_name',
			'total_paid',
			'siret',
			'ape',
			'company',
			'invoice_date',
			'delivery_date',
			'total_products',
			'total_products_wt',
			'total_discounts',
			'total_discounts_tax_incl',
			'total_discounts_tax_excl',
			'total_discounts',
			'total_shipping',
			'total_shipping_tax_incl',
			'total_shipping_tax_excl',
			'total_paid_tax_incl',
			'total_paid_tax_excl',
			'total_paid_real',
			'active',
			'is_guest'
		);
			

		fputcsv($FH, $csvVals, $separator);

		$orders = Order::getOrdersWithInformations();

		

		foreach ($orders as $order) {
			//print_r($order);

			$csvVals = Array(
				$order['id_order'],
				$order['reference'],
				self::_csv_filter($order['firstname']),
				self::_csv_filter($order['lastname']),
				$order['email'],
				$order['payment'],
				$order['module'],
				$order['current_state'],
				$order['state_name'],
				$order['total_paid'],
				$order['siret'],
				$order['ape'],
				self::_csv_filter($order['company']),
				$order['invoice_date'],
				$order['delivery_date'],
				$order['total_products'],
				$order['total_products_wt'],
				$order['total_discounts'],
				$order['total_discounts_tax_incl'],
				$order['total_discounts_tax_excl'],
				$order['total_discounts'],
				$order['total_shipping'],
				$order['total_shipping_tax_incl'],
				$order['total_shipping_tax_excl'],
				$order['total_paid_tax_incl'],
				$order['total_paid_tax_excl'],
				$order['total_paid_real'],
				$order['active'],
				$order['is_guest']
			);

			fputcsv($FH, $csvVals, $separator);

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
