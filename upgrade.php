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

// 0.44 - class.tools.php is no longer used
if (file_exists(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php')) {
	unlink(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php');
}

?>