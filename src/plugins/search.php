<?php

class PS_CLI_Search extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('search-preferences', 'PrestaShop search preferences');
		$command->addOpt('show-status', 'Show current search configuration', false)
			->addOpt('list-aliases', 'List search aliases', false)
			->addOpt('add-alias', 'Add a search alias', false)
			->addOpt('alias', 'Alias to define', false, 'string')
			->addOpt('search', 'Search keyword', false, 'string')
			->addOpt('delete-alias', 'Delete an alias', false, 'integer')
			->addOpt('enable-alias', 'Enable an alias', false, 'integer')
            ->addOpt('disable-alias', 'Disable an alias', false, 'integer')
            ->addOpt('update', 'Update a configuration value', false, 'boolean')
            ->addOpt('key', 'Configuration key to update', false, 'string')
            ->addOpt('value', 'Value to assign to the configuration key', false, 'string');

		$this->register_command($command);
	}

	public function run() {
        $arguments = PS_CLI_Arguments::getArgumentsInstance();
        $interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('show-status', false)) {
			$this->show_status();
			$status = true;
        }
        elseif($arguments->getOpt('update', false)) {
            $key = $arguments->getOpt('key', null);
            $value = $arguments->getOpt('value', null);

            if(is_null($key)) {
                $interface->error("You must provide --key with --update");
            }

            if(is_null($value)) {
                $interface->error("You must provide --value with --update");
            }

            $this->_update_configuration($key, $value);
        }
		elseif($arguments->getOpt('list-aliases', false)) {
			$this->list_aliases();
			$status = true;
		}
		elseif($arguments->getOpt('add-alias', false)) {
			$alias = $arguments->getOpt('alias', NULL);
			$search = $arguments->getOpt('search', NULL);

			if(is_null($alias)) {
				echo "Error, you must specify --alias with a value\n";
				$arguments->show_command_usage('search-preferences');
				$interface->error();
			}

			if(is_null($search)) {
				echo "Error, you must specify --search with a value\n";
				$arguments->show_command_usage('search-preferences');
				$interface->error();
			}

			$status = $this->add_alias($alias, $search);
		}
		elseif($aliasId = $arguments->getOpt('delete-alias', false)) {
			$status = $this->delete_alias($aliasId);
		}
		elseif($aliasId = $arguments->getOpt('enable-alias', false)) {
			$status = $this->enable_alias($aliasId);
		}
		elseif($aliasId = $arguments->getOpt('disable-alias', false)) {
			$status = $this->disable_alias($aliasId);
		}
		else {
			$arguments->show_command_usage('search-preferences');
			$interface->error();
		}

		if($status) {
			return true;
		}
		else {
			return false;
		}
	}

	public static function show_status() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_SEARCH_INDEXIATION', 'Automatic indexing of products');
		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_SEARCH_AJAX', 'Enable ajax search');
		PS_CLI_Utils::add_boolean_configuration_status($table, 'PS_INSTANT_SEARCH', 'Enable instant search');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_MINWORDLEN', 'Minimum word length to index');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_BLACKLIST', 'Blacklisted words (separated by |)');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_PNAME', 'Product name weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_REF', 'Reference weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_SHORTDESC', 'Short description weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_DESC', 'Description weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_CNAME', 'Category weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_MNAME', 'Manufacturer weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_TAG', 'Tags weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_ATTRIBUTE', 'Attribute weight');
		PS_CLI_Utils::add_configuration_value($table, 'PS_SEARCH_WEIGHT_FEATURE', 'Features weight');

		$table->display();
    }

    protected function _update_configuration($key, $value) {
        $interface = PS_CLI_Interface::getInterface();

        $validValue = false;

        switch($key) {
            case 'PS_SEARCH_INDEXIATION':
            case 'PS_SEARCH_AJAX':
            case 'PS_INSTANCE_SEARCH':
                $validValue = Validate::isBool($value);
                break;

            case 'PS_SEARCH_WEIGHT_PNAME':
            case 'PS_SEARCH_WEIGHT_REF':
            case 'PS_SEARCH_WEIGHT_SHORTDESC':
            case 'PS_SEARCH_WEIGHT_DESC':
            case 'PS_SEARCH_WEIGHT_CNAME':
            case 'PS_SEARCH_WEIGHT_MNAME':
            case 'PS_SEARCH_WEIGHT_TAG':
            case 'PS_SEARCH_WEIGHT_ATTRIBUTE':
            case 'PS_SEARCH_WEIGHT_FEATURE':
            case 'PS_SEARCH_MINWORDLEN':
                $validValue = Validate::isUnsignedInt($value);
                break;

            case 'PS_SEARCH_BLACKLIST':
                $validValue = Validate::isString($value);
                break;

            default:
                $interface->error("Configuration key '$key' is not handled by this command");
                break;
        }

        if(!$validValue) {
            $interface->error("'$value' is not a valid value for configuration key '$key'");
        }

        if(PS_CLI_Utils::update_configuration_value($key, $value)) {
            $interface->success("Successfully updated configuration key '$key'");
        }
        else {
            $interface->error("Could not update configuration key '$key'");
        }
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

PS_CLI_Configure::register_plugin('PS_CLI_Search');
