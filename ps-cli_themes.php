<?php

class PS_CLI_THEMES {

	public static function print_theme_list() {
		$themes = Theme::getThemes();

		print_r($themes);
	}
}

?>
