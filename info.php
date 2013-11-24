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

$module_directory     = 'image_tweak';
$module_name          = 'imageTweak';
$module_function      = 'tool';
$module_version       = '0.55';
$module_status        = 'Stable';
$module_platform      = '2.8';
$module_author        = 'Ralf Hertsch, Berlin (Germany)';
$module_license       = 'MIT License (MIT)';
$module_description   = 'Check images on the fly and reduce image size to a tweaked level';
$module_home          = 'http://phpmanufaktur.de/image_tweak';
$module_guid          = '3324A352-5244-4FCD-AC45-EB5E216A1F48';
