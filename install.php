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
if (!$dbCfg->sqlTableExists()) {
	if (!$dbCfg->sqlCreateTable()) {
		$error .= sprintf('<p>[INSTALL %s] %s</p>', $dbCfg->getTableName(), $dbCfg->getError());
	}
}

$dbLog = new dbImageTweakLog();
if (!$dbLog->sqlTableExists()) {
	if (!$dbLog->sqlCreateTable()) {
		$error .= sprintf('<p>[INSTALL %s] %s</p>', $dbLog->getTableName(), $dbLog->getError());
	}
}


function isPatched($filename) {
	if (file_exists($filename)) {	
		$lines = file($filename);
		foreach ($lines as $line) {
			if (strpos($line, "tweakImages") > 0)
				return true;
		}
		return false;
	}
	return false;
}

function doPatch($filename) {
	$returnvalue = false;
	$tempfile = WB_PATH .'/modules/output_filter/new_filter.php';
	$backup = WB_PATH .'/modules/output_filter/original-image-tweak-filter-routines.php';
	
	$addline = "\n\n\t\t// exec imageTweak filter";
	$addline .= "\n\t\tif(file_exists(WB_PATH .'/modules/image_tweak/class.filter.php')) { ";
	$addline .= "\n\t\t\trequire_once (WB_PATH .'/modules/image_tweak/class.filter.php'); ";
	$addline .= "\n\t\t\t".'$content = tweakImages($content); ';
	$addline .= "\n\t\t}\n\n ";
	if(file_exists($filename)) {	
		$lines = file ($filename);
		$handle = fopen ($tempfile, 'w');
		foreach ($lines as $line) {
			fwrite ($handle, $line);
			if (strpos($line, 'function filter_frontend_output($content)' ) > 0) {
				$returnvalue = true;
				fwrite($handle, $addline);
			}	
		}
		fclose ($handle);
		if (rename($filename, $backup)) {
			if (rename($tempfile, $filename)) {
				return $returnvalue;
			} 
			else { 
				return false;
			}
		}
	}
	return false;
}

// Patch Output Filter
$message = "";
if (file_exists(WB_PATH .'/modules/output_filter/filter-routines.php')) {
  if (!isPatched(WB_PATH .'/modules/output_filter/filter-routines.php')) {
		if (doPatch(WB_PATH .'/modules/output_filter/filter-routines.php')) {
			$message = tweak_msg_patch_success;
		} 
		else {
			$message = tweak_error_patch_failed;
		}
	} 
	else {
		$message = tweak_msg_already_patched;
	}
} 
else {
	$message = tweak_error_patch_failed_unknown;
}
if ($message != "") {
	echo '<script language="javascript">alert ("'.$message.'");</script>';
}
		

// Prompt Errors
if (!empty($error)) {
	global $admin;
	$admin->print_error($error);
}

?>