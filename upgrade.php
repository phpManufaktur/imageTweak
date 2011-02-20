<?php

/**
 * imageTweak
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2008 - 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 * 
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tweak.php');

global $admin;

$error = '';

// generally initialize the imageTweak Configuration
$tweakCfg = new dbImageTweakCfg();
if ($tweakCfg->isError()) $error .= sprintf('Initialize dbImageTweakCfg: %s', $tweakCfg->getError());

// 0.44 - class.tools.php is no longer used
if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php')) {
	unlink(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php');
}

if (!empty($error)) {
	$admin->print_error($error);
}

?>