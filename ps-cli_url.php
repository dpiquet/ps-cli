<?php

class PS_CLI_URL {

	public static function list_rewritings() {

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'id',
			'page',
			'title',
			'url_rewrite'
			)
		);

		$pages = Meta::getMetasByIdLang(PS_CLI_UTILS::$LANG);

		foreach($pages as $page) {
			$table->addRow(Array(
				$page['id_meta'],
				$page['page'],
				$page['title'],
				$page['url_rewrite']
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

		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_REWRITING_SETTINGS', 'Support Url rewriting');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_ALLOW_ACCENTED_CHARS_URL', 'Allow accented in characters in URL');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_CANONICAL_REDIRECT', 'Redirect to canonical URL (0= no redirection, 1= 302, 2 = 301)');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_HTACCESS_DISABLE_MULTIVIEWS', 'Disable Apache mutliview');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_HTACCESS_DISABLE_MODSEC', 'Disable Apache mod security');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_DOMAIN', 'Shop domain');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_DOMAIN_SSL', 'Shop domain SSL');

		$context = Context::getContext();

		$line = Array('-', 'Default URI');
		$url = ShopUrl::getShopUrls($context->shop->id)->where('main', '=', 1)->getFirst();

		array_push($line, $url->physical_uri);
		$table->addRow($line);

		$table->display();

		return;
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
