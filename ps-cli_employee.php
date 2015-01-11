<?php

#
#
#	Employee functions
#	  - add_employee
#	  - delete_employee
#	  - list_employee
#	  - enable_employee
#	  - disable_employee
#

class PS_CLI_EMPLOYEE {

	public static function list_employees($lang = NULL) {

		// TODO: check if lang exists before using it
		if ( $lang === NULL ) {
			$lang = Configuration::get('PS_LANG_DEFAULT');
		}

		$profiles = Profile::getProfiles($lang);
		$fieldSeparator = ' ';
		$lineSeparator = "\n";

		$table = new cli\Table();
		$table->setHeaders( Array(
			'ID',
			'email',
			'profile',
			'First name',
			'Last name'
			)
		);

		foreach ( $profiles as $profile ) {

			$employees = Employee::getEmployeesByProfile($profile['id_profile']);
			if (! $employees ) {
				continue;
			}

			foreach ( $employees as $employee ) {

				$table->addRow( Array(
					$employee['id_employee'],
					$employee['email'],
					$profile['name'],
					$employee['firstname'],
					$employee['lastname']
					)
				);
			}
		}

		$table->display();
	}

	public static function delete_employee( $employeeEmail, $force = false ) {

		if (! Validate::isEmail($employeeEmail) ) {
			echo "$employeeEmail is not a valid email address !\n";
			return false;
		}

		$employee = new Employee();
		if (! $employee->getByEmail($employeeEmail) ) {
			echo "No account found with email $employeeEmail\n";
			return false;
		}

		if ( ! $force && $employee->isLastAdmin() ) {
			echo "You cannot delete the last super admin\n";
			return false;
		}

		$res = $employee->delete();

		if ( $res ) {
			echo "Successfully deleted user\n";
			return true;
		}
		else {
			echo "Could not delete user $employeeEmail\n";
			return false;
		}
	}

	public static function disable_employee($employeeEmail) {
		if ( !Validate::isEmail($employeeEmail) ) {
			echo "$email is not a valid email address\n";
			return false;
		}

		$employee = new Employee();
		if (! $employee->getByEmail($employeeEmail) ) {
			echo "Could not find user with email $employeeEmail\n";
			return false;
		}

		if ( !$employee->active ) {
			echo "Employee $email is already inactive\n";
			return true;
		}

		$employee->active = false;

		$res = $employee->update();
		if ( $res ) {
			echo "Employee $email successfully deactivated\n";
			return true;
		}
		else {
			echo "Error while deactivating $email\n";
			return false;
		}
	}

	public static function enable_employee($employeeEmail) {
		if ( !Validate::isEmail($employeeEmail) ) {
			echo "$email is not a valid email address\n";
			return false;
		}

		$employee = new Employee();
		if (! $employee->getByEmail($employeeEmail) ) {
			echo "Could not find user with email $employeeEmail\n";
			return false;
		}

		if ( $employee->active ) {
			echo "Employee $email is already active\n";
			return true;
		}

		$employee->active = true;

		$res = $employee->update();
		if ( $res ) {
			echo "Employee $email successfully activated\n";
			return true;
		}
		else {
			echo "Error while activating $email\n";
			return false;
		}

	}

	public static function add_employee( $email, $password, $profile, $firstName, $lastName, $active=true, $optin=false, $defaultTab=1, $boTheme='default', $boMenu=1 ) {

		if ( Employee::employeeExists($email) ) {
			echo "Cannot add $email, this email address is already registered !\n";
			return false;
		}

		if ( !Validate::isEmail($email) ) {
			echo "email: $email is not a valid email address\n";
			return false;
		}

		if ( !Validate::isPasswd($password, 1) ) {
			echo "Provided password is not a valid password\n";
			return false;
		}

		if ( $lastName == '' ) {
			echo "Last name cannot be empty !\n";
			return false;
		}

		if ( $firstName == '' ) {
			echo "First name cannot be empty !\n";
			return false;
		}

		if ( !Validate::isName($firstName) ) {
			echo "$firstName is not a valid name\n";
			return false;
		}

		if ( !Validate::isName($lastName) ) {
			echo "$lastName is not a valid name\n";
			return false;
		}

		$employee = new Employee();

		$employee->firstname = $firstName;
		$employee->lastname = $lastName;
		$employee->email = $email;
		$employee->passwd = md5(_COOKIE_KEY_ . $password);
		$employee->last_passwd_gen = date('Y-m-d h:i:s', strtotime('-360 minutes'));
		$employee->bo_theme = $boTheme;
		$employee->default_tab = $defaultTab;
		$employee->active = $active;
		$employee->optin = $optin;
		$employee->id_profile = $profile;
		$employee->id_lang = Configuration::get('PS_LANG_DEFAULT');
		$employee->bo_menu = $boMenu;

		$res = $employee->add(true, true);
		if ( $res ) {
			echo "Successfully added user: $email\n";
			return true;
		}
		else {
			echo "Could not add user: $email\n";
			return false;
		}
	}

	public static function change_employee_password($employeeEmail, $newPassword) {

		if ( !Validate::isEmail($employeeEmail) ) {
			echo "$employeeEmail is not a valid email address\n";
			return false;
		}

		if (! Validate::isPasswd($newPassword, 1) ) {
			echo "Provided password is not a valid password for user $employeeEmail\n";
			return false;
		}

		$employee = new Employee();
		if (! $employee->getByEmail($employeeEmail) ) {
			echo "Could not find user with email $employeeEmail\n";
			return false;
		}

		$employee->passwd = md5(_COOKIE_KEY_ . $newPassword);

		$res = $employee->update();

		if ( $res ) {
			echo "Successfully updated password for user $employeeEmail\n";
			return true;
		}
		else {
			echo "Could not change password for user $employeeEmail\n";
			return false;
		}
	}

	public static function edit_employee($email, $password = NULL, $profile = NULL, $firstname = NULL, $lastname = NULL) {
		if (!Validate::isEmail($email)) {
			echo "$email is not a valid email address\n";
			return false;
		}

		$employee = new Employee();
		if (! $employee->getByEmail($email)) {
			echo "Could not find an employee with email $email\n";
			return false;
		}

		if ($password != NULL) {
			$employee->passwd = md5(_COOKIE_KEY_ . $password);
		}

		if ($profile != NULL) {
			if (!Validate::isInt($profile)) {
				echo "$profile is not a valid profile ID\n";
				return false;
			}

			$employee->id_profile = $profile;
		}

		if($firstname != NULL) {
			$employee->firstname = $firstname;
		}

		if($lastname != NULL) {
			$employee->lastname = $lastname;
		}

		$res = $employee->update();

		if($res) {
			echo "Successfully updated user $email\n";
			return true;
		}
		else {
			echo "Error, could not update user $email\n";
			return false;
		}
	}

	public static function get_any_superadmin_id() {

		$users = Employee::getEmployees();

		$superadminID = NULL;

		foreach ($users as $user) {

			if ($user['id_employee'] == PS_CLI_PROFILE::_SUPERADMIN_PROFILE_ID_) {
				$superadminID = $user['id_employee'];
			}
		}

		return $superadminID;
	}

}

?>
