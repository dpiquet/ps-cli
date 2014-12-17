<?php
#
# Core functions
#
#
#

function core_check_version() {
	$upgrader = new UpgraderCore;
	$latest = $upgrader->checkPSVersion(true);

	if ($latest) {
		foreach ( $latest as $newVersion ) {	
			echo $newVersion['name'] . ' ' . $newVersion['version'];
		}

	}
	else { echo 'Prestashop is up to date'; }
}

function core_list_changed_files() {
	$upgrader = new UpgraderCore;
	$files = $upgrader->getChangedFilesList();

	if($files) {
		foreach ( $files as $changedFileKey => $changedFileVal ) {
			echo "$changedFileKey\n";
			foreach ( $changedFileVal as $modifiedFiles ) {
				echo "  $modifiedFiles\n";
			}
		}
	}
	
	return;
}

?>
