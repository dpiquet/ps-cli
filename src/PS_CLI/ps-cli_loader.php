<?php

class PS_CLI_CONFIG {

	public $adminDir;
	public $rootDir;

	__construct() {
		//empty constructor 
	}

	// find PrestaShop root directory
	public function find_ps_root() {
		$dir = getcwd();

		while( is_readable($dir)) {


			if((is_dir("$dir/config"))&&(file_exists("$dir/init.php"))) {
				return $dir;
			}
		}	
	}
	
}


?>
