<?php

class PS_CLI_SEARCH {
	
	public static function show_status() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_SEARCH_INDEXIATION', 'Automatic indexing of products');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_SEARCH_AJAX', 'Enable ajax search');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_INSTANT_SEARCH', 'Enable instant search');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_MINWORDLEN', 'Minimum word length to index');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_BLACKLIST', 'Blacklisted words (separated by |)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_PNAME', 'Product name weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_REF', 'Reference weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_SHORTDESC', 'Short description weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_DESC', 'Description weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_CNAME', 'Category weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_MNAME', 'Manufacturer weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_TAG', 'Tags weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_ATTRIBUTE', 'Attribute weight');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SEARCH_WEIGHT_FEATURE', 'Features weight');

		$table->display();
	}

	//todo: make use of alias object instead of direct access to DB
	public static function list_aliases() {

		$table = new Cli\Table();
		$table->setHeaders(Array(
			'id_alias',
			'alias',
			'search',
			'active'
			)
		);

		$aliases = Db::getInstance()->executeS('
		SELECT a.*
		FROM `'._DB_PREFIX_.'alias` a');
		
		foreach ($aliases as $alias) {
			$table->addRow(Array(
				$alias['id_alias'],
				$alias['alias'],
				$alias['search'],
				$alias['active']
				)
			);	
		}

		$table->display();
	}
}
