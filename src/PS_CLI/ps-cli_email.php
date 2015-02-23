<?php

class PS_CLI_EMAIL {

	const MAIL_PHP = 1;
	const MAIL_SMTP = 2;
	const MAIL_DISABLED = 3;

	public static function show_status() {
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

?>
