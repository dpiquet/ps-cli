<?php

class PS_CLI_SHOPS {

	public static function print_shop_list() {
		//function getShops($active = true, $id_shop_group = null, $get_as_list_id = false)
		$shopList = Shop::getShops();

		print_r($shopList);

		$defaultShop = Configuration::get('PS_SHOP_DEFAULT');
		
		foreach ($shopList as $shop) {
			echo $shop['id_shop'] . ' ' .
				$shop['id_shop_group'] . ' ' .
				$shop['name'] . ' ' .
				$shop['id_theme'] . ' ' .
				$shop['id_category'] . ' ' .
				$shop['domain'] . ' ' .
				$shop['domain_ssl'] . ' ' .
				$shop['uri'] . ' ' .
				$shop['active'];

			if ($shop['id_shop'] == $defaultShop) {
				echo " [Default Shop]";
			}

			echo "\n";
		}
	}

	public static function print_shop_list_tree() {
		$shopList = Shop::getTree();

		print_r($shopList);
	}

	public static function set_current_shop_context($idShop) {

		//used by Tools::getValue
		$_GET['id_shop'] = $idShop;
		$_POST['id_shop'] = $idShop;

		Shop::setContext(Shop::CONTEXT_SHOP, $idShop);

		$context = Context::getContext();
		$context->shop = new Shop($idShop);

		if(Validate::isLoadedObject($context->shop)) {
			return true;
		}
		else {
			echo "Error, could not set current shop ID\n";
			return false;
		}
	}

	//TODO: in progress, must be finished
	public static function set_current_shopgroup_context($idGroup) {
		$context = Context::getContext();

		if (!Validate::isLoadedObject($context->shop)) {
			$context->shop = new Shop();
		}
		
		$context->shop->id_shop_group = $idGroup;

		return true;
	}
}

?>
