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

function list_employees() {

	// TODO: load language instead of assuming language 1 exists (config ?)
	$profiles = Profile::getProfiles(1);
	$fieldSeparator = ' ';
	$lineSeparator = "\n";

	foreach ( $profiles as $profile ) {

		$employees = Employee::getEmployeesByProfile($profile['id_profile']);
		if (! $employees ) {
			continue;
		}

		foreach ( $employees as $employee ) {

			echo $employee['id_employee'] . $fieldSeparator .
			     $employee['firstname'] . $fieldSeparator .
			     $employee['lastname'] . $fieldSeparator .
			     $employee['email'] . $fieldSeparator .
			     $profile['name'] . $fieldSeparator .
			     $lineSeparator;
		}
	}
}

function delete_employee( $employeeEmail, $force = false ) {

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

function disable_employee($employeeEmail) {
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

function enable_employee($employeeEmail) {
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

function add_employee( $email, $password, $profile, $firstName, $lastName, $active=true, $optin=false, $defaultTab=1, $boTheme='default', $boMenu=1 ) {

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


?>
