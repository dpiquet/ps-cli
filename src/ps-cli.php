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


#
# Prestashop cli tool
#
# Load Ps-cli code and start execution
#

/*====================================
|
|	 Load ps-cli functions
|
\=====================================*/

require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_utils.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_arguments.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_configure.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_interface.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_plugins.php');
require_once(PS_CLI_ROOT . '/PS_CLI/ps-cli_command.php');

/*
 * Load 3d party librairies
 */
//php-cli-tools
require_once(PS_CLI_ROOT . '/lib/php-cli-tools/load-php-cli-tools.php');

//garden-cli argument parser
require_once(PS_CLI_ROOT . '/lib/garden-cli/Args.php');
require_once(PS_CLI_ROOT . '/lib/garden-cli/Cli.php');
require_once(PS_CLI_ROOT . '/lib/garden-cli/Table.php');

$conf = PS_CLI_CONFIGURE::getConfigurationInstance();
$conf->preload_configure();

$arguments = PS_CLI_ARGUMENTS::getArgumentsInstance();

$arguments->parse_arguments();

//load ps core
PS_CLI_UTILS::ps_cli_load_ps_core();

$conf->postload_configure();

$interface = PS_CLI_INTERFACE::getInterface();

// todo: create a runner and export this code in it; we'll need to create an interface instance before
//find what to run
try {
	$arguments->runArgCommand();
}
catch (Exception $e) {
	$interface->add_exception($e);
}
catch (PrestaShopException $e) {
	$interface->add_exception($e);
}
catch (PrestaShopDatabaseException $e) {
	$interface->add_exception($e);
}
catch (PrestaShopModuleException $e) {
	$interface->add_exception($e);
}
catch (PrestaShopPaymentException $e) {
	$interface->add_exception($e);
}

$interface->display();

// add plugin hook there

$interface->exit_program();

?>
