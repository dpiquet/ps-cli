<?php

class PS_CLI_PROFILE {

	const _SUPERADMIN_PROFILE_ID_ = 1;

	public static function print_profile_list() {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		$profiles = Profile::getProfiles($configuration->lang);

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

	public static function delete_profile($profileId) {
		if(!Validate::isUnsignedInt($profileId)) {
			echo "Error, $profileId is not a valid profile ID !\n";
			return false;
		}

		if($profileId == _SUPERADMIN_PROFILE_ID_) {
			echo "Error, you must not delete SuperAdmin profile !\n";
			return false;
		}

		$profile = new Profile($profileId);
		if(!Validate::isLoadedObject($profile)) {
			echo "Error, could not find a profile with ID: $profileId\n";
			return false;
		}

		if($profile->delete()) {
			echo "Successfully deleted profile ID: $profileId\n";
			return true;
		}
		else {
			echo "Error, could not delete profile ID: $profileId\n";
			return false;
		}
	}

	public static function list_permissions($profileId) {
		$configuration = PS_CLI_CONFIGURE::getConfigurationInstance();

		if(!Validate::isUnsignedInt($profileId)) {
			echo "Error, $profileId is not a valid profile ID\n";
			return false;
		}
		
		$profile = new Profile($profileId);
		if(!Validate::IsLoadedObject($profile)) {
			echo "Error, could not find a profile with ID: $profileId\n";
			return false;
		}

		$accesses = Profile::getProfileAccesses($profileId, 'id_tab');

		echo "Access rights for profile $profile->name ($profileId)\n";

		$table = new Cli\Table();
		$table->setHeaders( Array(
			'Tab',
			'View',
			'Add',
			'Edit',
			'Delete'
			)
		);

		$allowedStr = 'X';
		$deniedStr = '';

		foreach ($accesses as $access) {
			$tab = new Tab($access['id_tab'], $configuration->lang);

			$table->addRow(Array(
				$tab->name,
				($access['view'] == 1 ? $allowedStr : $deniedStr),
				($access['add'] == 1 ? $allowedStr : $deniedStr),
				($access['edit'] == 1 ? $allowedStr : $deniedStr),
				($access['delete'] == 1 ? $allowedStr : $deniedStr)
				)
			);
		}

		$table->display();

		print_r($profile->name);
	}
}
