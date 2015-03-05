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
				($alias['active'] ? 'yes' : 'no')
				)
			);	
		}

		$table->display();
	}

	public static function add_alias($alias, $search) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		if(!Validate::isValidSearch($alias)) {
			echo "Error, $alias is not a valid search string\n";
			return false;
		}

		if(!Validate::isValidSearch($search)) {
			echo "Error, $search is not a valid search string\n";
			return false;
		}

		$obj = new Alias(NULL, trim($alias), trim($search));
		if($obj->save()) {
			if($configuration->porcelain) {
				echo "$obj->id\n";
			}
			else {
				echo "Successfully added alias $alias => $search\n";
			}
			return true;
		}
		else {
			echo "Error, could not add alias $alias => $search !\n";
			return false;
		}
	}

	public static function delete_alias($aliasId) {
		if(!Validate::isUnsignedId($aliasId)) {
			echo "Error, $aliasId is not a valid alias ID\n";
			return false;
		}

		$alias = new Alias($aliasId);

		if(!Validate::isLoadedObject($alias)) {
			echo "No alias found with id $aliasId\n";
			return false;
		}

		if($alias->delete()) {
			echo "Sucessfully deleted alias $alias->alias => $alias->search\n";
			return true;
		}
		else {
			echo "Error, could not delete alias $alias->alias => $alias->search\n";
			return false;
		}
	}

	public static function enable_alias($aliasId) {
		if(!Validate::isUnsignedId($aliasId)) {
			echo "Error, $aliasId is not a valid alias ID\n";
			return false;
		}

		$alias = new Alias($aliasId);

		if($alias->active) {
			echo "alias $aliasId is already enabled\n";
			return true;
		}

		$alias->active = true;

		if($alias->save()) {
			echo "Sucessfully enabled alias $aliasId\n";
			return true;
		}
		else {
			echo "Could not enable alias $aliasId\n";
			return false;
		}
	}

	public static function disable_alias($aliasId) {
		if(!Validate::isUnsignedId($aliasId)) {
			echo "Error, $aliasId is not a valid alias ID\n";
			return false;
		}

		$alias = new Alias($aliasId);

		if(!$alias->active) {
			echo "alias $aliasId is already disabled\n";
			return true;
		}

		$alias->active = false;

		if($alias->save()) {
			echo "Sucessfully disabled alias $aliasId\n";
			return true;
		}
		else {
			echo "Could not disable alias $aliasId\n";
			return false;
		}
	}
}
