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
    $message = sprintf(tool_msg_install_droplets_success, 'imageTweak');
}
else {
    $message = sprintf(tool_msg_install_droplets_failed, 'tsGallery', $droplets->getError());
}
if ($message != "") {
    echo '<script language="javascript">alert ("'.$message.'");</script>';
}

// delete no longer needed files
$delete_files = array(
        'class.tools.php',
        'presets/fancybox.jquery'
);
foreach ($delete_files as $file) {
    if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/'.$file)) {
        @unlink(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/'.$file);
    }
}


if (!empty($error)) {
	$admin->print_error($error);
}

?>