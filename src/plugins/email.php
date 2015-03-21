<?php

class PS_CLI_Email extends PS_CLI_Plugin {

	const MAIL_PHP = 1;
	const MAIL_SMTP = 2;
	const MAIL_DISABLED = 3;

	protected function __construct() {
		$command = new PS_CLI_Command('email', 'Manage email configuration');
		$command->addOpt('show-status', 'Show email configuration');
		$command->addOpt('update', 'Update a configuration value')
			->addOpt('option', 'The configuration key to update')
			->addOpt('value', 'Value to give to the configuration key');

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('show-status', false)) {
			$this->show_status();
		}
		elseif($arguments->getOpt('update', false)) {
			$key = $arguments->getOpt('option', NULL);
			$value = $arguments->getOpt('value', NULL);

			if(is_null($key)) {
				$interface->error('--option must be provided with --update');
			}

			if(is_null($value)) {
				$interface->error('--option must be provided with --value');
			}

			$this->update_configuration_value($key, $value);
		}
		else {
			$arguments->show_command_usage('email');
			exit(1);
		}

		exit(0);
	}

	public function update_configuration_value($key, $value) {
		$interface = PS_CLI_Interface::getInterface();

		$validValue = true;

		switch($key) {
			case 'PS_MAIL_EMAIL_MESSAGE':
				$validValue = Validate::isUnsignedInt($value);
				break;

			case 'PS_MAIL_METHOD':
				$validValue = (Validate::isUnsignedInt($value) &&
						$value <= 3);
				break;

			case 'PS_MAIL_DOMAIN':
				$validValue = Validate::isUrl($value);
				break;

			case 'PS_MAIL_SERVER':
			case 'PS_MAIL_USER':
				$validValue = Validate::isGenericName($value);
				break;

			case 'PS_MAIL_PASSWD':
				$validValue = Validate::isAnything($value);
				break;

			case 'PS_MAIL_SMTP_ENCRYPTION':
				switch($value) {
					case 'off':
					case 'tls':
					case 'ssl':
						$validValue = true;
						break;
					default:
						$validValue = false;
						break;
				}
				break;

			case 'PS_MAIL_SMTP_PORT':
				$validValue = Validate::isUnsignedInt($value);
				break;

			case 'PS_MAIL_TYPE':
				switch($value) {
					case Mail::TYPE_HTML:
					case Mail::TYPE_TEXT:
					case Mail::TYPE_BOTH:
						break;
					default:
						$validValue = false;
						break;
				}
				break;

			case 'PS_SHOP_EMAIL':
				$validValue = Validate::isEmail($value);
				break;

			case 'PS_LOG_EMAILS':
				$validValue = Validate::isBool($value);
				break;

			default:
				$interface->error("the configuration key $key is not managed by this plugin !");
				break;
		}

		if(!$validValue) {
			$interface->error("'$value' is not a valid value for '$key'");
		}

		// all seems ok, update configuration
		if(PS_CLI_Utils::update_configuration_value($key, $value)) {
			$interface->success("Successfully updated configuration $key");
		}
		else {
			$interface->error("Could not update configuration $key!");
		}
	}

	public function show_status() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);
		
		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_EMAIL_MESSAGE', 'Email method');


		$type = Configuration::get('PS_MAIL_TYPE');
		switch($type) {
			case Mail::TYPE_HTML:
				$typeName = 'HTML';
				break;
			case Mail::TYPE_TEXT:
				$typeName = 'Text';
				break;
			case Mail::TYPE_BOTH:
				$typeName = 'Both';
				break;
			default:
				$typeName = '';
				break;
		}

		$table->addRow(Array(
			'PS_MAIL_TYPE',
			"Email Type (".
				Mail::TYPE_HTML." for HTML, ".
				Mail::TYPE_TEXT." for text, ".
				Mail::TYPE_BOTH." for both)",
			$type . ' ('.$typeName.')'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_DOMAIN', 'Mail domain name');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_EMAIL', 'Shop email');

		
		//todo
		$mailMethod = Configuration::get('PS_MAIL_METHOD');

		switch($mailMethod) {
			case self::MAIL_PHP:
				$methodName = 'PHP mail()';
				break;
			case self::MAIL_SMTP:
				$methodName = 'SMTP';
				break;
			case self::MAIL_DISABLED:
				$methodName = 'Disabled';
				break;
			default:
				$methodName = '';
				break;
		}

		$table->addRow(Array(
			'PS_MAIL_METHOD',
			'Email method ('.self::MAIL_PHP.' for php mail(), '.
				self::MAIL_SMTP.' for smtp, '.
				self::MAIL_DISABLED.' for disabled)',
			$mailMethod. ' ('.$methodName.')'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_SERVER', 'Email server');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_USER', 'Email user');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_PASSWD', 'Email password');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_SMTP_ENCRYPTION', 'SMTP encryption (off, tls or ssl)');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_SMTP_PORT', 'SMTP port');
		PS_CLI_UTILS::add_boolean_configuration_status($table, 'PS_LOG_EMAILS', 'Logs emails');

		$table->display();
	}

}

PS_CLI_Configure::register_plugin('PS_CLI_Email');

?>
