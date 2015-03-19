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

class PS_CLI_Employee extends PS_CLI_Plugin {

	protected function __construct() {
		$command = new PS_CLI_Command('employee', 'Manage PrestaShop employees');
		$command->addOpt('list', 'List employees', false, 'boolean')
			->addOpt('delete', 'Delete an employee', false, 'string')
			->addOpt('disable', 'Disable an employee', false, 'string')
			->addOpt('enable', 'Enable an employee', false, 'string')
			->addOpt('create', 'Create an employee', false, 'string')
			->addArg('<email address>', 'Employee email address', false)
			->addOpt('password', 'Employee password', false, 'string')
			->addOpt('profile', 'Employee profile', false, 'integer')
			->addOpt('first-name', 'Employee first name', false, 'string')
			->addOpt('last-name', 'Employee last name', false, 'string')
			->addOpt('show-status', 'Show employee configuration', false, 'boolean');
		$this->register_command($command);
	}

	// TODO: refactor in plugin
	public function run() {
		$arguments = PS_CLI_Arguments::getArgumentsInstance();
		$interface = PS_CLI_Interface::getInterface();

		$status = null;

		if ($arguments->getOpt('list', false)) {
			$status = $this->list_employees();
		}
		elseif($arguments->getOpt('show-status', false)) {
			$this->print_employee_options();
			$status = true;
		}
		elseif ($opt = $arguments->getOpt('delete', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('employee');
				exit(1);
			}
			$status = $this->delete_employee($opt);
		}

		elseif ($opt = $arguments->getOpt('disable', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('employee');
				exit(1);
			}
			$status = $this->disable_employee($opt);
		}

		elseif ($opt = $arguments->getOpt('enable', false)) {
			if ($opt === "1") {
				$arguments->show_command_usage('employee');
				exit(1);
			}
			$status = $this->enable_employee($opt);
		}

		// todo: support for all options (optin, active, defaultTab, ...)
		elseif ($email = $arguments->getOpt('create', false)) {

			if(!Validate::isEmail($email)) {
				echo "Error, $email is not a valid email address\n";
				exit(1);
			}

			$pwdError = 'You must provide a password for the employee';
			if ($password = $arguments->getOpt('password', false)) {
				if ($password === "1") {
					$arguments->show_command_usage('employee', $pwdError);
					exit(1);
				}
			}
			else {
				$arguments->show_command_usage('employee', $pwdError);
				exit(1);
			}

			$profileError = 'You must provide a profile for the Employee';
			if ($profile = $arguments->getOpt('profile', false)) {
				if(!Validate::isUnsignedInt($profile)) {
					$arguments->show_command_usage('employee', $profileError);
					exit(1);
				}
			}
			else {
				$arguments->show_command_usage('employee', $profileError);
				exit(1);
			}

			$firstnameError = 'You must specify a name with --first-name option';
			if ($firstname = $arguments->getOpt('first-name', false)) {
				if($firstname == '') {
					$arguments->show_command_usage('employee', $firstnameError);
					exit(1);
				}
			}
			else {
				$arguments->show_command_usage('employee', $firstnameError);
				exit(1);
			}
			
			$lastnameError = 'You must specify a last name with --last-name option';
			if($lastname = $arguments->getOpt('last-name', false)) {
				if($lastname == '') {
					$arguments->show_command_usage('employee', $lastnameError);
					exit(1);
				}
			}
			else {
				$arguments->show_command_usage('employee', $lastnameError);
				exit(1);
			}

			$status = $this->add_employee(
				$email,
				$password,
				$profile,
				$firstname,
				$lastname
			);	
		}
		elseif ($email = $arguments->getOpt('edit', false)) {
	
			if($email === "1") {
				echo "You must specify an email address!\n";
				$arguments->show_command_usage();
				exit(1);
			}

			if ($password = $arguments->getOpt('password', false)) {
				if($password === "1") {
					echo "You must specify a password with --password option\n";
					exit(1);
				}	
			}
			else {
				$password = NULL;
			}

			if ($profile = $arguments->getOpt('profile', false)) {
				//usual check === 1 cannot work with int values
				if (!Validate::isUnsignedInt($profile)) {
					echo "$profile is not a valid profile id\n";
					exit(1);
				}
			}
			else {
				$profile = NULL;
			}

			if ($firstname = $arguments->getOpt('firstname', false)) {
				if ($firstname === "1") {
					echo "You must specify a name with --firstname option\n";
					exit(1);
				}
			}
			else {	
				$firstname = NULL;
			}

			if ($lastname = $arguments->getOpt('lastname', false)) {
				if ($firstname === "1") {
					echo "You must specify a name with --lastname option\n";
					exit(1);
				}
			}
			else {	
				$lastname = NULL;
			}

			$res = $this->edit_employee($email, $password, $profile, $firstname, $lastname);

			if ($res) {
				echo "Employee $email successfully updated\n";
				exit(0);
			}
			else {
				echo "Error, could not update employee $email\n";
				exit(1);
			}

		}
		else {
			$arguments->show_command_usage('employee');
			exit(1);
		}

		if ($status === false) {
			exit(1);
		}

		exit(0);

	}

	public static function list_employees($lang = NULL) {

		// TODO: check if lang exists before using it
		if ( $lang === NULL ) {
			$lang = Configuration::get('PS_LANG_DEFAULT');
		}

		$profiles = Profile::getProfiles($lang);

		$table = new cli\Table();
		$table->setHeaders( Array(
			'ID',
			'email',
			'profile',
			'First name',
			'Last name',
			'Active'
			)
		);

		foreach ( $profiles as $profile ) {

			$employees = Employee::getEmployeesByProfile($profile['id_profile']);
			if (! $employees ) {
				continue;
			}

			foreach ( $employees as $employee ) {

				//print_r($employee);

				$enabled = ($employee['active'] == 1 ? 'Active' : 'Inactive');

				$table->addRow( Array(
					$employee['id_employee'],
					$employee['email'],
					$profile['name'],
					$employee['firstname'],
					$employee['lastname'],
					$enabled
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

		//getByEmail get only active employees so we have to load all employees and loop'em to find our ID
		$employeeInfos =  Db::getInstance()->getRow('
			                SELECT id_employee
					FROM `'._DB_PREFIX_.'employee`
					WHERE `email` = \''.pSQL($employeeEmail).'\'');

		$employee = new Employee($employeeInfos['id_employee']);
		
		if(!Validate::isLoadedObject($employee)) {
			echo "Could not load employee !\n";
			exit(1);
		}

		if ( ! $force && $employee->isLastAdmin() ) {
			echo "You cannot delete the last super admin\n";
			return false;
		}

		$res = $employee->delete();

		if ( $res ) {
			echo "Successfully deleted user $employeeEmail\n";
			return true;
		}
		else {
			echo "Could not delete user $employeeEmail\n";
			return false;
		}
	}

	public static function disable_employee($employeeEmail) {
		if ( !Validate::isEmail($employeeEmail) ) {
			echo "$employeeEmail is not a valid email address\n";
			return false;
		}

		$employee = new Employee();
		if (! $employee->getByEmail($employeeEmail) ) {
			echo "Could not find user with email $employeeEmail\n";
			return false;
		}

		if ( !$employee->active ) {
			echo "Employee $employeeEmail is already inactive\n";
			return true;
		}

		$employee->active = false;

		$res = $employee->update();
		if ( $res ) {
			echo "Employee $employeeEmail successfully deactivated\n";
			return true;
		}
		else {
			echo "Error while deactivating $employeeEmail\n";
			return false;
		}
	}

	public static function enable_employee($employeeEmail) {
		if ( !Validate::isEmail($employeeEmail) ) {
			echo "$employeeEmail is not a valid email address\n";
			return false;
		}

		//getByEmail get only active employees on ps < 1.6.0.12
		// so we have to load all employees and loop'em to find our ID

		$employeeInfos =  Db::getInstance()->getRow('
			                SELECT id_employee
					FROM `'._DB_PREFIX_.'employee`
					WHERE `email` = \''.pSQL($employeeEmail).'\'');

		$employee = new Employee($employeeInfos['id_employee']);
		
		if(!Validate::isLoadedObject($employee)) {
			echo "Could not load employee !\n";
			exit(1);
		}

		if ( $employee->active ) {
			echo "Employee $employeeEmail is already active\n";
			return true;
		}

		$employee->active = true;

		$res = $employee->update();
		if ( $res ) {
			echo "Employee $employeeEmail successfully activated\n";
			return true;
		}
		else {
			echo "Error while activating $employeeEmail\n";
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

	public static function print_employee_options() {
		$table = new Cli\Table();

		$table->setHeaders(Array(
			'Key',
			'Configuration',
			'Value'
			)
		);

		PS_CLI_UTILS::add_configuration_value($table, 'PS_PASSWD_TIME_BACK', 'Minimum delay for password regeneration');
		PS_CLI_UTILS::add_configuration_value($table, 'PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 'Memorize last used language in forms');

		$table->display();

		return;
	}

}

PS_CLI_Configure::register_plugin('PS_CLI_Employee');
?>
