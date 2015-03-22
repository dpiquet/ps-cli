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

class PS_CLI_Shop extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('shop', 'Control shop');
		$command->addOpt('enable', 'Turn off maintenance mode on the shop', false)
			->addOpt('disable', 'Turn on maintenance mode on the shop', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();

		$status = NULL;

		if($arguments->getOpt('enable', false)) {
			$successMsg = 'Maintenance mode disabled';
			$errMsg = 'Could not disable maintenance mode';
			$notChanged = 'Maintenance mode was already disabled';

			PS_CLI_UTILS::update_global_value('PS_SHOP_ENABLE', true, $successMsg, $errMsg, $notChanged);
		}
		elseif($opt = $arguments->getOpt('disable', false)) {
			$successMsg = 'Maintenance mode enabled';
			$errMsg = 'Could not enable maintenance mode';
			$notChanged = 'Maintenance mode was already enabled';

			PS_CLI_UTILS::update_global_value('PS_SHOP_ENABLE', false, $successMsg, $errMsg, $notChanged);
		}
		else {
			$arguments->show_command_usage('shop');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		exit(0);
	}

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

	/*
	 * Functions moved to PS_CLI_Configure class
	 *
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
	 */
}

PS_CLI_Configure::register_plugin('PS_CLI_Shop');

?>
