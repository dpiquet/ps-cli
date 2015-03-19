<?php

class PS_CLI_Multistore extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('multistore', 'Perform Multistore operations');
		$command->addOpt('list-shops', 'List shops', false)
			->addOpt('list-groups', 'List shop groups', false)
			->addOpt('create-group', 'Create a shop group', false)
			->addOpt('enable-multistore', 'Enable multistore feature', false)
			->addOpt('disable-multistore', 'Disable multistore feature', false)
			->addOpt('active', '', false)
			->addOpt('share-customers', 'share customers', false, 'boolean')
			->addOpt('share-orders', 'share orders', false, 'boolean')
			->addOpt('share-stock', 'share stock', false, 'boolean')
			->addOpt('name', 'name', false, 'string');

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($opt = $arguments->getOpt('list-shops', false)) {
			$this->list_shops();
			$status = true;
		}
		elseif($opt = $arguments->getOpt('list-groups', false)) {
			$this->list_groups();
			$status = true;
		}
		elseif($opt = $arguments->getOpt('enable-multistore', false)) {
			$this->enable_multistore();
		}
		elseif($opt = $arguments->getOpt('disable-multistore', false)) {
			$this->disable_multistore();
		}
		elseif($opt = $arguments->getOpt('create-group', false)) {

			$active = $arguments->getOpt('active', false);

			$shareCustomers = $arguments->getOpt('share-customers', false);
			$shareStock = $arguments->getOpt('share-stock', false);
			$shareOrders = $arguments->getOpt('share-orders', false);
			if($name = $arguments->getOpt('name', false)) {
				if($name == "1") {
					echo "You must specify a name with --name option\n";
					exit(1);
				}
			}
			else {
				echo "You must specify group name with --name option\n";
				exit(1);
			}

			$this-> create_group($name, $shareCustomers, $shareStock, $shareOrders, $active = true); 
		}
		else {
			$arguments->show_command_usage('multistore');
			exit(1);
		}

		if ($status) {
			exit(0);
		}
		else exit(1);

	}

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
		if(!Validate::isBool($shareCustomers)) {
			return false;
		}

		if (!Validate::isBool($shareStock)) {
			return false;
		}

		if(!Validate::isBool($shareOrders)) {
			return false;
		}

		if(!Validate::isBool($active)) {
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

PS_CLI_Configure::register_plugin('PS_CLI_Multistore');

?>
