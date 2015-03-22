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

class PS_CLI_Seo extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('seo', 'Manage SEO & URL');
		$command->AddOpt('list-metas', 'List metas tags', false)
			->addOpt('show-status', 'Show configuration', false)
			->addOpt('base-uri', 'Set shop base URI', false, 'string');

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($opt = $arguments->getOpt('list-metas', false)) {
			$this->list_metas();
		}
		elseif($arguments->getOpt('show-status', false)) {
			$this->show_status();
		}
		elseif($baseUri = $arguments->getOpt('base-uri', null)) {
			if(!Validate::isUrl($baseUri)) {
				echo "Error: '$baseUri' is not a valid URI\n";
				exit(1);
			}
			$status = $this->update_base_uri($baseUri);
		}
		else {
			$arguments->show_command_usage('seo');
			exit(1);
		}

		exit(0);
	}

	public static function list_metas() {

		$context = Context::getContext();

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'id',
			'page',
			'title',
			'description',
			'url_rewrite',
			'keywords'
			)
		);

		$metas = Meta::getMetasByIdLang($context->lang->id);

		foreach($metas as $meta) {

			$table->addRow(Array(
				$meta['id_meta'],
				$meta['page'],
				$meta['title'],
				$meta['description'],
				$meta['url_rewrite'],
				$meta['keywords']
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

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table,
			'PS_REWRITING_SETTINGS',
			'Support Url rewriting');

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table,
			'PS_ALLOW_ACCENTED_CHARS_URL',
			'Allow accented in characters in URL');

		PS_CLI_UTILS::add_configuration_value(
			$table,
			'PS_CANONICAL_REDIRECT', 
			'Redirect to canonical URL (0= no redirection, 1= 302, 2 = 301)');

		PS_CLI_UTILS::add_boolean_configuration_status($table, 
			'PS_HTACCESS_DISABLE_MULTIVIEWS', 
			'Disable Apache mutliview');

		PS_CLI_UTILS::add_boolean_configuration_status(
			$table, 
			'PS_HTACCESS_DISABLE_MODSEC', 
			'Disable Apache mod security');

		PS_CLI_UTILS::add_configuration_value(
			$table, 
			'PS_SHOP_DOMAIN', 
			'Shop domain');

		PS_CLI_UTILS::add_configuration_value(
			$table, 
			'PS_SHOP_DOMAIN_SSL', 
			'Shop domain SSL');

		$context = Context::getContext();

		$line = Array('-', 'Default URI');
		$url = ShopUrl::getShopUrls($context->shop->id)->where('main', '=', 1)->getFirst();

		array_push($line, $url->physical_uri);
		$table->addRow($line);

		$table->display();

		return;
    }

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;
        $updateUri = false;

        switch($key) {

            case 'PS_CANONICAL_REDIRECT':
                $validValue = (Validate::isUnsignedInt($value) &&
                    $value <= 2);
                break;

            case 'PS_SHOP_DOMAIN_SSL':
            case 'PS_SHOP_DOMAIN':
                $updateUri = true;

                $validValue = Validate::isCleanHtml($value);
                break;

			case 'PS_HTACCESS_DISABLE_MODSEC':
			case 'PS_HTACCESS_DISABLE_MULTIVIEWS':
			case 'PS_ALLOW_ACCENTED_CHARS_URL':
            case 'PS_REWRITING_SETTINGS':
                $validValue = Validate::isBool($value);
                break;

        }

        if(!$validValue) {
            $interface->error("'$value' is not a valid value for configuration key '$key'");
        }

        if(PS_CLI_Utils::update_configuration_value($key, $value)) {
            if($updateUri) {
                $this->post_update_uri();
            }

            $interface->success("Successfully updated configuration key '$key'");
        }
        else {
            $interface->error("Could not update configuration key '$key'");
        }

    }

	public static function update_base_uri($uri) {
		$context = Context::getContext();

		$url = ShopUrl::getShopUrls($context->shop->id)->where('main', '=', 1)->getFirst();

		if($url->physical_uri == $uri) {
			echo "Base uri is already $uri\n";
			return true;
		}

		$url->physical_uri = $uri;
		if($url->update()) {
			echo "Successfully updated base URI\n";
			return true;
		}
		else {
			echo "Error, could not update base URI\n";
			return false;
		}
	}

	public static function post_update_uri() {
		$context = Context::getContext();

		$htaccess = _PS_ROOT_DIR_.'/.htaccess';
		if(!is_writable($htaccess)) {
			echo "Error, htaccess file not writable\n";
			return false;
		}

		$disableMultiviews = Configuration::get('PS_HTACCESS_DISABLE_MULTIVIEWS');
		$htaccessModSec = Configuration::get('PS_HTACCESS_MODSEC');

		if (Tools::generateHtaccess($htaccess, null, null, '', $disableMultiviews, false, $htaccessModSec)) {
			Tools::enableCache();
			Tools::clearCache($context->smarty);
			Tools::restoreCacheSettings();

			echo "Htaccess file regenerated\n";
			return true;
		}
		else {
			echo "Error, could not update htaccess file\n";
			return false;
		}
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Seo');

?>
