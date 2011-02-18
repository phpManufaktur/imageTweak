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

// Sprachdateien einbinden
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); 
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php'); 
}

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
}

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
}

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

// Prompt Errors
if (!empty($error)) {
	global $admin;
	$admin->print_error($error);
}
