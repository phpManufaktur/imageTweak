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

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {    
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php'); 
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) { 
        include($root.'/framework/class.secure.php'); 
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tweak.php');

// Sprachdateien einbinden
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); 
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php'); 
}

require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');

$error = '';

$dbCfg = new dbImageTweakCfg();
if ($dbCfg->sqlTableExists()) {
	if (!$dbCfg->sqlDeleteTable()) {
		$error .= sprintf('[UNINSTALL %s] %s', $dbCfg->getTableName(), $dbCfg->getError());		
	}
}

$dbLog = new dbImageTweakLog();
if ($dbLog->sqlTableExists()) {
	if (!$dbLog->sqlDeleteTable()) {
		$error .= sprintf('[UNINSTALL %s] %s', $dbLog->getTableName(), $dbLog->getError());		
	}
}

if (defined('LEPTON_VERSION')) {
	// unregister imageTweak from LEPTON outputInterface
	if (!file_exists(WB_PATH .'/modules/output_interface/output_interface.php')) {
		$error .= '<p>Missing LEPTON outputInterface, can\'t unregister imageTweak!</p>';
	}
	else {
		if (!function_exists('register_output_filter')) include_once(WB_PATH .'/modules/output_interface/output_interface.php');
		unregister_output_filter('image_tweak');
	}
} // LEPTON
else {
	// Try to unpatch output filter of WebsiteBaker
	function isPatched($filename) {
		if (file_exists($filename)) {	
			$lines = file($filename);
			foreach ($lines as $line) {
				if (strpos($line , "tweakImages" ) > 0)
					return true;
			}
			return false;
		}
		return false;
	} // isPatched()
	
	function unPatch() {
		$original = WB_PATH .'/modules/output_filter/filter-routines.php';
		$tmp 			= WB_PATH .'/modules/output_filter/filter-routines.backup.php';
		$backup 	= WB_PATH .'/modules/output_filter/original-image-tweak-filter-routines.php';
		if (!file_exists($backup) )
			return false;  // No backup, can't do anything
		if (file_exists($tmp))
			unlink($tmp);
		if (rename($original, $tmp)) {
			if (rename($backup, $original)) {
				unlink($tmp);
				return true;
			} 
			else { 
				return false;
			}
		} 
		else {
			return false;
		}
	} // unPatch()
	
	// Try to remove hook from output filter
	if (file_exists(WB_PATH .'/modules/output_filter/filter-routines.php')) {
		if (isPatched(WB_PATH .'/modules/output_filter/filter-routines.php')) {
			if (!unPatch()) {
				$message = tweak_error_patch_uninstall;
			} 
			else {
				$message = tweak_msg_patch_uninstall_success;
			}
			echo '<script language="javascript">alert ("'.$message.'");</script>';
		}
	}
} // WebsiteBaker

// remove Droplets
$dbDroplets = new dbDroplets();
$droplets = array('it_gallery');
foreach ($droplets as $droplet) {
    $where = array(dbDroplets::field_name => $droplet);
    if (!$dbDroplets->sqlDeleteRecord($where)) {
        $message = sprintf('[UPGRADE] Error uninstalling Droplet: %s', $dbDroplets->getError());
    }
}

// Prompt Errors
if (!empty($error)) {
	global $admin;
	$admin->print_error($error);
}
