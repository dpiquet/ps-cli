<?php
#
# DATABASE
#
#  -backup
#

function database_create_backup() {

	$backupCore = new PrestaShopBackup();

	$backupCore->psBackupAll = true;
	$backupCore->psBackupDropTable = true;

	$backupCore->add();

	return $backupCore->id;
}

?>
