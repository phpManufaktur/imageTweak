<?php

/**
 * imageTweak
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2008-2013
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
  if (defined('LEPTON_VERSION'))
    include(WB_PATH.'/framework/class.secure.php');
}
else {
  $oneback = "../";
  $root = $oneback;
  $level = 1;
  while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
    $root .= $oneback;
    $level += 1;
  }
  if (file_exists($root.'/framework/class.secure.php')) {
    include($root.'/framework/class.secure.php');
  }
  else {
    trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
  }
}
// end include class.secure.php

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tweak.php');

// include GENERAL language file
if(!file_exists(WB_PATH .'/modules/kit_tools/languages/' . LANGUAGE .'.php')) {
    // default language is DE !!!
    require_once(WB_PATH .'/modules/kit_tools/languages/DE.php');
}
else {
    require_once(WB_PATH .'/modules/kit_tools/languages/' . LANGUAGE .'.php');
}

// Sprachdateien einbinden
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php');
}
else {
	require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
}

// Installation fuer das Droplet
require_once(WB_PATH.'/modules/kit_tools/class.droplets.php');

global $admin;

$error = '';
$message = '';

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
		if (!function_exists('register_output_filter'))
		  include_once(WB_PATH .'/modules/output_interface/output_interface.php');
		register_output_filter('image_tweak', 'imageTweak');
	}
} // LEPTON
elseif(defined('CAT_VERSION')) {
    // register imageTweak at BlackCat Filter
	if (!file_exists(WB_PATH .'/modules/blackcatFilter/filter.php')) {
		$error .= '<p>Missing BlackCat Filter, can\'t register imageTweak - installation is not complete!</p>';
	}
	else {
		if (!function_exists('register_filter'))
		  include_once(WB_PATH .'/modules/blackcatFilter/filter.php');
		register_filter('imageTweak','image_tweak','optimize images on the fly while delivering pages of your website to the browser');
	}
} // BlackCat
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

	function doPatch($filename, $wb_283=false) {
	  $search = $wb_283 ? "define('OUTPUT_FILTER_DOT_REPLACEMENT'" : 'function filter_frontend_output($content)';
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
				if (strpos($line, $search) > 0) {
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
	if (version_compare(WB_VERSION, '2.8.3', '>=')) {
	  // WebsiteBaker 2.8.3
	  $wb_283 = true;
	  $filter_name = WB_PATH.'/modules/output_filter/index.php';
	}
	else {
	  // all other WebsiteBaker versions
	  $wb_283 = false;
	  $filter_name = WB_PATH .'/modules/output_filter/filter-routines.php';
	}
	if (file_exists($filter_name)) {
	  if (!isPatched($filter_name)) {
			if (doPatch($filter_name, $wb_283)) {
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

// Install Droplets
$droplets = new checkDroplets();
$droplets->droplet_path = WB_PATH.'/modules/image_tweak/droplets/';

if ($droplets->insertDropletsIntoTable()) {
    $message = sprintf(tool_msg_install_droplets_success, 'imageTweak');
}
else {
    $message = sprintf(tool_msg_install_droplets_failed, 'tsGallery', $droplets->getError());
}
if ($message != "") {
    echo '<script language="javascript">alert ("'.$message.'");</script>';
}

// Prompt Errors
if (!empty($error)) {
	$admin->print_error($error);
}

?>