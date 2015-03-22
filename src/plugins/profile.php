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

class PS_CLI_Profile extends PS_CLI_Plugin {

	const _SUPERADMIN_PROFILE_ID_ = 1;

	protected function __construct() {
		$command = new PS_CLI_Command('profile', 'Manage PrestaShop profiles');
		$command->addOpt('list', 'List profiles', false)
			->addOpt('delete', 'Delete a profile', false, 'integer')
			->addOpt('list-permissions', 'List a profile permissions', false, 'integer')
			->addArg('<ID>', 'Profile ID', false);

		$this->register_command($command);
	}

	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		if ($opt = $arguments->getOpt('list', false)) {
			$this->print_profile_list();
		}
		elseif ($id = $arguments->getOpt('delete', false)) {
			$status = $this->delete_profile($id);
		}
		elseif ($id = $arguments->getOpt('list-permissions', false)) {
			$status = $this->list_permissions($id);
		}
		else {
			$arguments->show_command_usage('profile');
			exit(1);
		}

		exit(0);
	}

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

		echo "Access rights for profile ".array_pop($profile->name)." ($profileId)\n";

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
	}
}

PS_CLI_Configure::register_plugin('PS_CLI_Profile');

?>
