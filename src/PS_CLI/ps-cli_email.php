<?php

class PS_CLI_EMAIL {

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
				Mail::TYPE_HTML." for Html, ".
				Mail::TYPE_TEXT." for text, ".
				Mail::TYPE_BOTH." for both)",
			$type . ' ('.$typeName.')'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_DOMAIN', 'Mail domain name');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_SHOP_EMAIL', 'Shop email');

		
		//todo
		PS_CLI_UTILS::add_configuration_value($table, 'PS_MAIL_METHOD', 'Email method');



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
