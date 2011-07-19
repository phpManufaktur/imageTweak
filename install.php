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

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tweak.php');

// Sprachdateien einbinden
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php'); 
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php'); 
}

global $admin;
	
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

if (defined('LEPTON_VERSION')) {
	// register imageTweak at LEPTON outputInterface
	if (!file_exists(WB_PATH .'/modules/output_interface/output_interface.php')) {
		$error .= '<p>Missing LEPTON outputInterface, can\'t register imageTweak - installation is not complete!</p>';
	}
	else {
		if (!function_exists('register_output_filter')) include_once(WB_PATH .'/modules/output_interface/output_interface.php');
		register_output_filter('image_tweak', 'imageTweak');
	}
} // LEPTON
else {
	// WebsiteBaker - must patch output filter to get imageTweak working...
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
	} // isPatched()
	
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
	} // doPatch()
	
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
} // WebsiteBaker		

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>