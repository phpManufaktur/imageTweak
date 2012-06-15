<?php

/**
 * imageTweak
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2008-2012
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

// Checking Requirements

$PRECHECK['PHP_VERSION'] = array('VERSION' => '5.2.0', 'OPERATOR' => '>=');
$PRECHECK['WB_ADDONS'] = array(
        'dbconnect_le' => array('VERSION' => '0.64', 'OPERATOR' => '>='),
        'dwoo' => array('VERSION' => '0.10', 'OPERATOR' => '>='),
        'kit_tools' => array('VERSION' => '0.16', 'OPERATOR' => '>='),
        'wblib' => array('VERSION' => '0.65', 'OPERATOR' => '>='),
        'libraryadmin' => array('VERSION' => '1.9', 'OPERATOR' => '>='),
        'lib_jquery' => array('VERSION' => '1.25', 'OPERATOR' => '>='),
);

if (!defined('LEPTON_VERSION')) $PRECHECK['WB_ADDONS']['output_filter'] = array('VERSION' => '0.1', 'OPERATOR' => '>=');


global $database;
// check for UTF-8 charset
$charset = 'utf-8';
$sql = "SELECT `value` FROM `".TABLE_PREFIX."settings` WHERE `name`='default_charset'";
$result = $database->query($sql);
if ($result) {
	$data = $result->fetchRow(MYSQL_ASSOC);
	$charset = $data['value'];
}

// check for imageOptimizer
$old_io = (file_exists(WB_PATH.'/modules/img_optimizer/include.php')) ? 'NO' : 'YES';
// check for dbImageOptimizer
$db_io = (file_exists(WB_PATH.'/modules/dbimageoptimizer/tool.php')) ? 'NO' : 'YES';

$PRECHECK['CUSTOM_CHECKS'] = array(
	'Default Charset' => array('REQUIRED' => 'utf-8', 'ACTUAL' => $charset,	'STATUS' => ($charset === 'utf-8')),
	'REMOVED: imageOptimizer' => array('REQUIRED' => 'YES', 'ACTUAL' => $old_io, 'STATUS' => ($old_io === 'YES')),
	'REMOVED: dbImageOptimizer' => array('REQUIRED' => 'YES', 'ACTUAL' => $db_io, 'STATUS' => ($db_io === 'YES'))
);

?>