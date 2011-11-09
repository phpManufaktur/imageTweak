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

// try to include LEPTON class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {	
	if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php'); 
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) { 
			include($dir.'/framework/class.secure.php'); $inc = true;	break; 
		} 
	}
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' . LANGUAGE .'.php')) {
    // default language is DE !!!
    require_once(WB_PATH .'/modules/kit_tools/languages/DE.php');
}
else {
    require_once(WB_PATH .'/modules/kit_tools/languages/' . LANGUAGE .'.php');
}

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tweak.php');
require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');

global $admin;

$error = '';

// generally initialize the imageTweak Configuration
$tweakCfg = new dbImageTweakCfg();
if ($tweakCfg->isError()) $error .= sprintf('Initialize dbImageTweakCfg: %s', $tweakCfg->getError());

// 0.44 - class.tools.php is no longer used
if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php')) {
	unlink(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php');
}

// remove Droplets
$dbDroplets = new dbDroplets();
$droplets = array('it_gallery');
foreach ($droplets as $droplet) {
    $where = array(dbDroplets::field_name => $droplet);
    if (!$dbDroplets->sqlDeleteRecord($where)) {
        $message = sprintf('[UPGRADE] Error uninstalling Droplet: %s', $dbDroplets->getError());
    }
}

// Install Droplets
$droplets = new checkDroplets();
$droplets->droplet_path = WB_PATH.'/modules/image_tweak/droplets/';

if ($droplets->insertDropletsIntoTable()) {
    $message .= sprintf(tool_msg_install_droplets_success, 'imageTweak');
}
else {
    $message .= sprintf(tool_msg_install_droplets_failed, 'tsGallery', $droplets->getError());
}
if ($message != "") {
    echo '<script language="javascript">alert ("'.$message.'");</script>';
}


if (!empty($error)) {
	$admin->print_error($error);
}

?>