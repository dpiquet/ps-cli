<?php

function ps_cli_initialize() {
	ps_cli_init_admin_context();
}

function ps_cli_init_admin_context() {
	$context = Context::getContext();

	// todo: load admin list and pick from it instead of assuming there's a user '1
	$context->employee = new Employee(1);
}

?>
