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
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']));
}
// end include LEPTON class.secure.php

require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tweak.php');

if (!class_exists('Dwoo')) require_once(WB_PATH.'/modules/dwoo/include.php');
global $parser;
if (!is_object($parser)) $parser = new Dwoo();

global $tweakCfg;
global $tweakLog;

if (!is_object($tweakCfg)) $tweakCfg = new dbImageTweakCfg(true);
if (!is_object($tweakLog)) $tweakLog = new dbImageTweakLog(true);

$backend = new tweakBackend();
$backend->action();

class tweakBackend {
	
	const request_action 						= 'act';
	const request_items							= 'its';
	
	const action_default						= 'def';
	const action_info								= 'info';
	const action_config							= 'cfg';
	const action_config_check				= 'chk';
	const action_log								= 'log';
	
	private $tab_navigation_array = array(
		self::action_info							=> tweak_tab_info,
		self::action_config						=> tweak_tab_config,
		self::action_log							=> tweak_tab_log
	);
	
	private $page_link 					= '';
	private $img_url						= '';
	private $template_path			= '';
	private $error							= '';
	private $message						= '';
	
	public function __construct() {
		$this->page_link = ADMIN_URL.'/admintools/tool.php?tool=image_tweak';
		$this->template_path = WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/htt/' ;
		$this->img_url = WB_URL. '/modules/'.basename(dirname(__FILE__)).'/img/';
	} // __construct()
	
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  public function setError($error) {
    $this->error = $error;
  } // setError()

  /**
    * Get Error from $this->error;
    * 
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    * 
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError

  /**
   * Reset Error to empty String
   */
  public function clearError() {
  	$this->error = '';
  }

  /** Set $this->message to $message
    * 
    * @param STR $message
    */
  public function setMessage($message) {
    $this->message = $message;
  } // setMessage()

  /**
    * Get Message from $this->message;
    * 
    * @return STR $this->message
    */
  public function getMessage() {
    return $this->message;
  } // getMessage()

  /**
    * Check if $this->message is empty
    * 
    * @return BOOL
    */
  public function isMessage() {
    return (bool) !empty($this->message);
  } // isMessage
  
  /**
   * Return Version of Module
   *
   * @return FLOAT
   */
  public function getVersion() {
    // read info.php into array
    $info_text = file(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/info.php');
    if ($info_text == false) {
      return -1; 
    }
    // walk through array
    foreach ($info_text as $item) {
      if (strpos($item, '$module_version') !== false) {
        // split string $module_version
        $value = explode('=', $item);
        // return floatval
        return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
      } 
    }
    return -1;
  } // getVersion()
  
  /**
   * Verhindert XSS Cross Site Scripting
   * 
   * @param REFERENCE $_REQUEST Array
   * @return $request
   */
	public function xssPrevent(&$request) {
  	if (is_string($request)) {
	    $request = html_entity_decode($request);
	    $request = strip_tags($request);
	    $request = trim($request);
	    $request = stripslashes($request);
  	}
	  return $request;
  } // xssPrevent()
	
  public function action() {
  	$html_allowed = array();
  	foreach ($_REQUEST as $key => $value) {
  		if (!in_array($key, $html_allowed)) {
   			$_REQUEST[$key] = $this->xssPrevent($value);
  		} 
  	}
    isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_info;
  	switch ($action):
  	case self::action_config:
  		$this->show(self::action_config, $this->dlgConfig());
  		break;
  	case self::action_config_check:
  		$this->show(self::action_config, $this->checkConfig());
  		break;
  	case self::action_log:
  		$this->show(self::action_log, $this->dlgLog());
  		break;
  	case self::action_info:
  	default:
  		$this->show(self::action_info, $this->dlgInfo());
  		break;
  	endswitch;
  } // action
	
  	
  /**
   * Erstellt eine Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @return STR Navigationsleiste
   */
  public function getNavigation($action) {
  	$result = '';
  	foreach ($this->tab_navigation_array as $key => $value) {
   		($key == $action) ? $selected = ' class="selected"' : $selected = ''; 
	 		$result .= sprintf(	'<li%s><a href="%s">%s</a></li>', 
	 												$selected,
	 												sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
	 												$value
	 												);
  	}
  	$result = sprintf('<ul class="nav_tab">%s</ul>', $result);
  	return $result;
  } // getNavigation()
  
  /**
   * Ausgabe des formatierten Ergebnis mit Navigationsleiste
   * 
   * @param $action - aktives Navigationselement
   * @param $content - Inhalt
   * 
   * @return ECHO RESULT
   */
  public function show($action, $content) {
  	global $parser;
  	if ($this->isError()) {
  		$content = $this->getError();
  		$class = ' class="error"';
  	}
  	else {
  		$class = '';
  	}
  	$data = array(
  		'navigation'			=> $this->getNavigation($action),
  		'class'						=> $class,
  		'content'					=> $content
  	);
  	$parser->output($this->template_path.'backend.body.htt', $data);
  } // show()
	
	public function dlgInfo() {
		global $parser;
		$data = array(
			'version'		=> sprintf('%01.2f', $this->getVersion()),
			'logo'			=> sprintf('<img src="%s" width="350" height="260" alt="imageTweak" />', $this->img_url.'imageTweak_Logo_350.jpg')
		);
		return $parser->get($this->template_path.'backend.info.htt', $data);
	} // dlgInfo()
	
	public function dlgConfig() {
		global $parser;
  	global $tweakCfg;
		$SQL = sprintf(	"SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s",
										$tweakCfg->getTableName(),
										dbImageTweakCfg::field_status,
										dbImageTweakCfg::status_deleted,
										dbImageTweakCfg::field_name);
		$config = array();
		if (!$tweakCfg->sqlExec($SQL, $config)) {
			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakCfg->getError()));
			return false;
		}
		$count = array();
		$items = sprintf(	'<tr><th>%s</th><th>%s</th><th>%s</th></tr>',
											tweak_header_cfg_identifier,
											tweak_header_cfg_value,
											tweak_header_cfg_description );
		$row = '<tr><td>%s</td><td>%s</td><td>%s</td></tr>';
		// bestehende Eintraege auflisten
		foreach ($config as $entry) {
			$id = $entry[dbImageTweakCfg::field_id];
			$count[] = $id;
			$label = constant($entry[dbImageTweakCfg::field_label]);
			(isset($_REQUEST[dbImageTweakCfg::field_value.'_'.$id])) ? 
				$val = $_REQUEST[dbImageTweakCfg::field_value.'_'.$id] : 
				$val = $entry[dbImageTweakCfg::field_value];
				// Hochkommas maskieren 
				$val = str_replace('"', '&quot;', stripslashes($val));
			$value = sprintf(	'<input type="text" name="%s_%s" value="%s" />', dbImageTweakCfg::field_value, $id,	$val);
			$desc = constant($entry[dbImageTweakCfg::field_description]);
			$items .= sprintf($row, $label, $value, $desc);
		}
		$items_value = implode(",", $count);
		// Mitteilungen anzeigen
		if ($this->isMessage()) {
			$intro = sprintf('<div class="message">%s</div>', $this->getMessage());
		}
		else {
			$intro = sprintf('<div class="intro">%s</div>', tweak_intro_cfg);
		}		
		$data = array(
			'form_name'						=> 'tweak_cfg',
			'form_action'					=> $this->page_link,
			'action_name'					=> self::request_action,
			'action_value'				=> self::action_config_check,
			'items_name'					=> self::request_items,
			'items_value'					=> $items_value,
			'header'							=> tweak_header_cfg,
			'intro'								=> $intro,
			'items'								=> $items,
			'btn_ok'							=> tweak_btn_ok,
			'btn_abort'						=> tweak_btn_abort,
			'abort_location'			=> $this->page_link
		);
		return $parser->get($this->template_path.'backend.cfg.htt', $data);
	} // dlgConfig()
	
	/**
	 * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
	 * und aktualisiert die entsprechenden Datensaetze.
	 * Fuegt neue Datensaetze ein.
	 * 
	 * @return STR DIALOG dlgConfig()
	 */
	public function checkConfig() {
		global $tweakTools;
		global $tweakCfg;
		$message = '';
		// ueberpruefen, ob ein Eintrag geaendert wurde
		if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items]))) {
			$ids = explode(",", $_REQUEST[self::request_items]);
			foreach ($ids as $id) {
				if (isset($_REQUEST[dbImageTweakCfg::field_value.'_'.$id])) {
					$value = $_REQUEST[dbImageTweakCfg::field_value.'_'.$id];
					$where = array();
					$where[dbImageTweakCfg::field_id] = $id; 
					$config = array();
					if (!$tweakCfg->sqlSelectRecord($where, $config)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakCfg->getError()));
						return false;
					}
					if (sizeof($config) < 1) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_cfg_id, $id)));
						return false;
					}
					$config = $config[0];
					if ($config[dbImageTweakCfg::field_value] != $value) {
						// Wert wurde geaendert
							if (!$tweakCfg->setValue($value, $id) && $tweakCfg->isError()) {
								$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakCfg->getError()));
								return false;
							}
							elseif ($tweakCfg->isMessage()) {
								$message .= $tweakCfg->getMessage();
							}
							else {
								// Datensatz wurde aktualisiert
								$message .= sprintf(tweak_msg_cfg_id_updated, $id, $config[dbImageTweakCfg::field_name]);
							}
					}
				}
			}		
		}		
		// ueberpruefen, ob ein neuer Eintrag hinzugefuegt wurde
		if ((isset($_REQUEST[dbImageTweakCfg::field_name])) && (!empty($_REQUEST[dbImageTweakCfg::field_name]))) {
			// pruefen ob dieser Konfigurationseintrag bereits existiert
			$where = array();
			$where[dbImageTweakCfg::field_name] = $_REQUEST[dbImageTweakCfg::field_name];
			$where[dbImageTweakCfg::field_status] = dbImageTweakCfg::status_active;
			$result = array();
			if (!$tweakCfg->sqlSelectRecord($where, $result)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakCfg->getError()));
				return false;
			}
			if (sizeof($result) > 0) {
				// Eintrag existiert bereits
				$message .= sprintf(tweak_msg_cfg_add_exists, $where[dbImageTweakCfg::field_name]);
			}
			else {
				// Eintrag kann hinzugefuegt werden
				$data = array();
				$data[dbImageTweakCfg::field_name] = $_REQUEST[dbImageTweakCfg::field_name];
				if (((isset($_REQUEST[dbImageTweakCfg::field_type])) && ($_REQUEST[dbImageTweakCfg::field_type] != dbImageTweakCfg::type_undefined)) &&
						((isset($_REQUEST[dbImageTweakCfg::field_value])) && (!empty($_REQUEST[dbImageTweakCfg::field_value]))) &&
						((isset($_REQUEST[dbImageTweakCfg::field_label])) && (!empty($_REQUEST[dbImageTweakCfg::field_label]))) &&
						((isset($_REQUEST[dbImageTweakCfg::field_description])) && (!empty($_REQUEST[dbImageTweakCfg::field_description])))) {
					// Alle Daten vorhanden
					unset($_REQUEST[dbImageTweakCfg::field_name]);
					$data[dbImageTweakCfg::field_type] = $_REQUEST[dbImageTweakCfg::field_type];
					unset($_REQUEST[dbImageTweakCfg::field_type]);
					$data[dbImageTweakCfg::field_value] = stripslashes(str_replace('&quot;', '"', $_REQUEST[dbImageTweakCfg::field_value]));
					unset($_REQUEST[dbImageTweakCfg::field_value]);
					$data[dbImageTweakCfg::field_label] = $_REQUEST[dbImageTweakCfg::field_label];
					unset($_REQUEST[dbImageTweakCfg::field_label]);
					$data[dbImageTweakCfg::field_description] = $_REQUEST[dbImageTweakCfg::field_description];
					unset($_REQUEST[dbImageTweakCfg::field_description]);
					$data[dbImageTweakCfg::field_status] = dbImageTweakCfg::status_active;
					$data[dbImageTweakCfg::field_update_by] = $tweakTools->getDisplayName();
					$data[dbImageTweakCfg::field_update_when] = date('Y-m-d H:i:s');
					$id = -1;
					if (!$tweakCfg->sqlInsertRecord($data, $id)) {
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakCfg->getError()));
						return false; 
					}
					$message .= sprintf(tweak_msg_cfg_add_success, $id, $data[dbImageTweakCfg::field_name]);		
				}
				else {
					// Daten unvollstaendig
					$message .= tweak_msg_cfg_add_incomplete;
				}
			}
		}
		if (!empty($message)) $this->setMessage($message);
		return $this->dlgConfig();
	} // checkConfig()
  
	
	public function dlgLog() {
		global $tweakLog;
  	global $parser;
  	global $tweakCfg;
		
  	// Anzahl der Datensaetze auf das angegebene Limit begrenzen  	
  	$limit = $tweakCfg->getValue(dbImageTweakCfg::cfgLimitLogEntries);
  	$SQL = sprintf( "SELECT %s FROM %s ORDER BY %s DESC LIMIT 1",
  									dbImageTweakLog::field_id,
  									$tweakLog->getTableName(),
  									dbImageTweakLog::field_id);
  	$lim = array();
  	if (!$tweakLog->sqlExec($SQL, $lim)) {
 			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakLog->getError()));
 			return false;
 		}
 		if (count($lim) > 0) {
 			$count = $lim[0][dbImageTweakLog::field_id];
 			if (($start = ($count - $limit)) > 0) {
 				$SQL = sprintf(	"DELETE FROM %s WHERE %s <= '%s'",
 												$tweakLog->getTableName(),
 												dbImageTweakLog::field_id,
 												$start);
 				if (!$tweakLog->sqlExec($SQL, $lim)) {
 					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakLog->getError()));
 					return false;
 				}
 			}
 		}
 		
 		// LOG anzeigen
  	$SQL = sprintf(	"SELECT * FROM %s ORDER BY %s DESC",
  									$tweakLog->getTableName(),
  									dbImageTweakLog::field_timestamp 
  								);
  	$logs = array();
 		if (!$tweakLog->sqlExec($SQL, $logs)) {
 			$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakLog->getError()));
 			return false;
 		}
 		
 		$row = new Dwoo_Template_File($this->template_path.'backend.log.row.htt');
 		$data = array(
 			'date'			=> tweak_header_date,
 			'category'	=> tweak_header_category,
 			'page_id'		=> tweak_header_page_id,
 			'text'			=> tweak_header_text
 		);
  	$items = $parser->get($this->template_path.'backend.log.head.htt', $data);
  	
  	$flipflop = true;
		foreach ($logs as $log) {
			$flipflop ? $flipper = 'flip' : $flipper = 'flop';
  		$flipflop ? $flipflop = false : $flipflop = true;
			$data = array(
				'flipflop'		=> $flipper,
				'timestamp'		=> date(tweak_cfg_date_time, strtotime($log[dbImageTweakLog::field_timestamp])),
				'category'		=> $tweakLog->category_array[$log[dbImageTweakLog::field_category]],
				'page_id'			=> $log[dbImageTweakLog::field_page_id],
				'description'	=> $log[dbImageTweakLog::field_text]
 			);
 			$items .= $parser->get($row, $data);
		}
		
		if (empty($items)) {
			// es liegen keine Fehlermeldungen vor
			$intro = sprintf('<div class="intro">%s</div>', tweak_intro_log_no_entries);
		}
		else {
			$intro = sprintf('<div class="intro">%s</div>', tweak_intro_log);
		}
		$data = array(
			'header'		=> tweak_header_log,
			'intro'			=> $intro,
			'items'			=> $items
		);
		return $parser->get($this->template_path.'backend.log.htt', $data);
	} // dlgLog()
  
} // class tweakBackend
?>