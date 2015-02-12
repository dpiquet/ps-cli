<?php

class PS_CLI_PROFILE {

	const _SUPERADMIN_PROFILE_ID_ = 1;

	public static function print_profile_list() {
		$profiles = Profile::getProfiles(PS_CLI_UTILS::$LANG);

		$table = new cli\Table();
		$table->setHeaders( Array(
			'ID',
			'Name'
			)
		);

		foreach ($profiles as $profile) {
			$table->addRow(Array( $profile['id_profile'], $profile['name']));
		}

		$table->display();

		return true;
	}

}
