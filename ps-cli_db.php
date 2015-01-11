<?php
#
# DATABASE
#
#  -backup
#

class PS_CLI_DB {

	public static function database_create_backup() {

		$backupCore = new PrestaShopBackup();

		$backupCore->psBackupAll = true;
		$backupCore->psBackupDropTable = true;

		if (! $backupCore->add() ) {
			echo "Error, could not backup database\n";
			return false;
		}

		return $backupCore->id;
	}
}
?>
