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


#
# DATABASE
#
#  -backup
#

class PS_CLI_Db extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('db', 'Perform database operations');
		$command->addOpt('backup', 'Create a backup', false, 'boolean')
			->addOpt('skip-stats', 'Skip stats tables on backup', false, 'boolean')
			->addOpt('list', 'List backups', false, 'boolean');

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if($arguments->getOpt('backup', false)) {
			$skipStats = $arguments->getOpt('skip-stats', false);

			$status = PS_CLI_DB::database_create_backup($skipStats);
		}
		elseif($arguments->getOpt('list', false)) {
			$status = PS_CLI_DB::list_database_backups();
		}
		else {
			$arguments->show_command_usage('db');
			exit(1);
		}

		if($status === false) {
			exit(1);
		}
		else {
			exit(0);
		}
	}

	public static function database_create_backup($skipStats = false) {

		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		$backupCore = new PrestaShopBackup();

		$skipStats ? $backupCore->psBackupAll = false : $backupCore->psBackupAll = false;
		$backupCore->psBackupDropTable = true;

		if (! $backupCore->add() ) {
			echo "Error, could not backup database\n";
			return false;
		}

		if($configuration->porcelain) {
			echo "$backupCore->id\n";
		}
		else {
			echo "Successfully created database backup at $backupCore->id\n";
		}

		return true;
	}

	public static function list_database_backups() {

		$dh = @opendir(PrestaShopBackup::getBackupPath());
		if($dh === false) {
			echo "Error, cannot read database backup directory $dh\n";
			return false;
		}

		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Filename',
			'Size',
			'Date'
			)
		);

		while($file = readdir($dh)) {
			if (preg_match('/^([_a-zA-Z0-9\-]*[\d]+-[a-z\d]+)\.sql(\.gz|\.bz2)?$/', $file, $matches) == 0)
				continue;

			$filename = $file;
			$size = number_format(filesize(PrestaShopBackup::getBackupPath($file)) / 1000, 2) . ' Kb';
			$date = date('Y-m-d H:i:s', (int)$matches[1]);

			$table->addRow(Array($filename,$size,$date));
		}

		$table->display();

		return true;
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Db');

?>
