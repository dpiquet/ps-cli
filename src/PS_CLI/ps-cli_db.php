<?php
#
# DATABASE
#
#  -backup
#

class PS_CLI_DB {

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

			echo "$file\n";

			$filename = $file;
			$size = number_format(filesize(PrestaShopBackup::getBackupPath($file)) / 1000, 2) . ' Kb';
			$date = date('Y-m-d H:i:s', (int)$matches[1]);

			$table->addRow(Array($filename,$size,$date));
		}

		$table->display();

		return true;
	}
}
?>
