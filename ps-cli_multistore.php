<?php

class PS_CLI_MULTISTORE {

	public static function list_shops() {

		$shops = Shop::getShops(false);

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'id',
			'group',
			'name',
			'theme',
			'category',
			'domain',
			'domain_ssl',
			'uri',
			'active'
			)
		);

		foreach($shops as $shop) {

			$theme = new Theme($shop['id_theme']);
			if(Validate::isLoadedObject($theme)) {
				$themeName = $theme->name;
			}
			else { $themeName = $shop['id_theme']; }

			unset($theme);

			$group = new ShopGroup($shop['id_shop_group']);
			if(Validate::isLoadedObject($group)) {
				$groupName = $group->name;
			}
			else { $groupName = $shop['id_shop_group']; }

			unset($group);

			$category = new Category($shop['id_category']);
			if(Validate::isLoadedObject($category)) {
				$categoryName = array_pop($category->name);
			}
			else { $categoryName = $shop['id_category']; }

			unset($category);

			$table->addRow(Array(
				$shop['id_shop'],
				$groupName,
				$shop['name'],
				$themeName,
				$categoryName,
				$shop['domain'],
				$shop['domain_ssl'],
				$shop['uri'],
				$shop['active']
				)
			);
		}

		$table->display();
	}

	public static function list_groups() {
		$groups = ShopGroup::getShopGroups(false);

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'id',
			'name',
			'share customers',
			'share orders',
			'share stock',
			'active',
			'deleted'
			)
		);

		foreach($groups as $group) {
			$fields = $group->getFields();

			$table->addRow(Array(
				$fields['id_shop_group'],	
				$fields['name'],
				$fields['share_customer'],
				$fields['share_order'],
				$fields['share_stock'],
				$fields['active'],
				$fields['deleted']
				)
			);
		}

		$table->display();

	}

	public static function create_group($name, $shareCustomers, $shareStock, $shareOrders, $active = true) {
		if(!Validate::isBool($shareClient)) {
			return false;
		}
		if (!Validate::isBool($shareQtty)) {
			return false;
		}
		if(!Validate::isBool($shareOrders)) {
			return false;
		}


		$shopGroup = new ShopGroup();

		$shopGroup->name = $name;
		$shopGroup->active = $active;
		$shopGroup->share_customer = $shareCustomers;
		$shopGroup->share_stock = $shareStock;
		$shopGroup->share_order = $shareOrders;

		// todo: echo shopgroupid
		if($shopGroup->add()) {
			echo "Shop group $name successfully created\n";
			return true;
		}
		else {
			echo "Error, could not create shop group $name\n";
			return false;
		}
	}

	public static function enable_multistore() {
		$isEnabled = (bool)Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');

		if ($isEnabled) {
			echo "Multistore feature is already enabled\n";
			return true;
		}

		if (Configuration::updateValue('PS_MULTISHOP_FEATURE_ACTIVE', 1)) {
			echo "Multistore feature enabled\n";
			return true;
		}
		else {
			echo "Error, could not enable multistore feature\n";
			return false;
		}
	}

	public static function disable_multistore() {
		$isEnabled = (bool)Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');

		if (!$isEnabled) {
			echo "Multistore feature is already disabled\n";
			return true;
		}

		if (Configuration::updateValue('PS_MULTISHOP_FEATURE_ACTIVE', 0)) {
			echo "Multistore feature disabled\n";
			return true;
		}
		else {
			echo "Error, could not disable multistore feature\n";
			return false;
		}

	}

}
