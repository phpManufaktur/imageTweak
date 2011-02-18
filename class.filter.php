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
require_once(WB_PATH.'/modules/'.basename(dirname(__FILE__)).'/class.tools.php');

function tweakImages($content) {
	$tweak = new processContent();
	return $tweak->exec($content);
} // tweakImages()

global $tweakCfg;
global $tweakLog;
global $tweakTools;

if (!is_object($tweakCfg)) $tweakCfg = new dbImageTweakCfg(true);
if (!is_object($tweakLog)) $tweakLog = new dbImageTweakLog(true);
if (!is_object($tweakTools)) $tweakTools = new tweakTools();

class processContent {
	
	private $content;
	private $tweak_path;
	private $tweak_url;
	private $media_url;
	private $error;
	private $class_no_tweak;
	private $file_types;
	private $check_alt_tags;
	private $default_alt_tag;
	private $set_title_tag;
	private $tweak_exec;
	private $ignore_page_ids;
	private $ignore_topic_ids;
	
	public function __construct() {
		global $tweakCfg;
		global $tweakTools;
		$tweaked = $tweakCfg->getValue(dbImageTweakCfg::cfgTweakImageDir);
		$tweaked = $tweakTools->removeLeadingSlash($tweakTools->addSlash($tweaked));		
		$this->tweak_path = WB_PATH.MEDIA_DIRECTORY.'/'.$tweaked;
		$this->tweak_path .= (defined('TOPIC_ID')) ? 'topics/'.TOPIC_ID.'/' : 'pages/'.PAGE_ID.'/';
		if (!file_exists($this->tweak_path)) {
			if (!mkdir($this->tweak_path, 0755, true)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_mkdir, $this->tweak_path)));
			}
			else {
				$this->writeLog(sprintf(tweak_log_mkdir, $this->tweak_path), dbImageTweakLog::category_info);
			}
		}
		$this->tweak_url = str_replace(WB_PATH, WB_URL, $this->tweak_path);
		$this->media_url = WB_URL.MEDIA_DIRECTORY.'/';
		$this->class_no_tweak = $tweakCfg->getValue(dbImageTweakCfg::cfgClassNoTweak);
		$this->file_types = $tweakCfg->getValue(dbImageTweakCfg::cfgExtensions);
		$this->check_alt_tags = $tweakCfg->getValue(dbImageTweakCfg::cfgCheckAltTags);
		$this->default_alt_tag = $tweakCfg->getValue(dbImageTweakCfg::cfgDefaultAltTag);
		$this->set_title_tag = $tweakCfg->getValue(dbImageTweakCfg::cfgSetTitleTag);
		$this->tweak_exec = $tweakCfg->getValue(dbImageTweakCfg::cfgTweakExec);
		$this->ignore_page_ids = $tweakCfg->getValue(dbImageTweakCfg::cfgIgnorePageIDs);
		$this->ignore_topic_ids = $tweakCfg->getValue(dbImageTweakCfg::cfgIgnoreTopicIDs);
	} // __construct()
	
	public function setError($error) {
		$this->error = $error;
		$this->writeLog($error, dbImageTweakLog::category_error);
	} // setError()
	
	public function getError() {
		return $this->error;	
	} // getError()
	
	public function isError() {
		return (bool) !empty($this->error);
	} // isError()
	
	private function writeLog($message, $message_type) {
		global $tweakLog;
		$data = array(
			dbImageTweakLog::field_category => $message_type,
			dbImageTweakLog::field_page_id	=> PAGE_ID,
			dbImageTweakLog::field_text			=> $message
		);
		// just write to LOG - here is no chance to trigger additional errors
		$tweakLog->sqlInsertRecord($data);
	} // writeLog()
	
	public function setContent($content) {
		$this->content = $content;
	} // setContent()
	
	public function getContent() {
		return $this->content;
	} // getContent()
	
	public function exec($content) {
		// sofort wieder raus, wenn imageTweak ausgeschaltet ist
		if (!$this->tweak_exec) return $content;
		// pruefen ob die PAGE_ID ignoriere werden soll
		if (in_array(PAGE_ID, $this->ignore_page_ids)) return $content;
		// pruefen ob die TOPIC_ID ignoriert werden soll
		if (defined('TOPIC_ID') && (in_array(TOPIC_ID, $this->ignore_topic_ids))) return $content;
		// Inhalt uebernehmen
		$this->setContent($content);
		// Inhalt pruefen und zurueckgeben
		return $this->checkContent();
	} // exec()
	
	
	private function checkContent() {
		// optimierte Dateien auslesen
		$complete = scandir($this->tweak_path);
		$old_tweak_files = array();
		// separate directories from files...
		foreach ($complete as $item) {
			if (is_file($this->tweak_path.$item)) $old_tweak_files[$item] = true;
		}
		
		preg_match_all('/<img[^>]*>/', $this->content, $matches);
		foreach ($matches as $match) {
			foreach ($match as $img_tag) {
				// <img ...> zerlegen
				preg_match_all('/([a-zA-Z]*[a-zA-Z])\s{0,3}[=]\s{0,3}("[^"\r\n]*)"/', $img_tag, $attr);
				foreach ($attr as $attributes) {
					$img = array();
					foreach ($attributes as $attribut) {
						if (strpos($attribut, "=") !== false) {
							list($key, $value) = explode("=", $attribut);
							$value = trim($value);
							$value = substr($value, 1, strlen($value)-2);
							$img[strtolower(trim($key))] = trim($value);
						}
					}
					// nur Bilder pruefen, die sich im /MEDIA Verzeichnis befinden
					if (!empty($img) && isset($img['src']) && ((strpos($img['src'], $this->media_url) !== false) && (strpos($img['src'], $this->media_url) == 0))) {
						// Bild pruefen, bei Fehler abbrechen
						if ($this->checkImage($img)) {
							// aus dem Array der alten Dateien entfernen
							if (isset($old_tweak_files[basename($img['src'])])) unset($old_tweak_files[basename($img['src'])]);
							// <img> tag schreiben
							$new_tag = '';
							foreach ($img as $key => $value) {
								$new_tag .= sprintf(' %s="%s"', $key, $value);
							}
							$new_tag = sprintf('<img%s />', $new_tag);
							$this->content = str_replace($img_tag, $new_tag, $this->content);
						}
					}
				}	
			}
		}
		// Verzeichnis aufraeumen und nicht mehr benoetigte Dateien entfernen
		foreach ($old_tweak_files as $key => $value) {
			if (!unlink($this->tweak_path.$key)) {
				$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_unlink, $this->tweak_path.$key)));
			}
		}
		return $this->content;
	} // checkContent()
	
	private function createFileName($filename, $extension, $width, $height) {
		global $tweakTools;
		$filename = $tweakTools->cleanFileName($filename);
		return sprintf('%s_%d_%d.%s', $filename, $width, $height, $extension);
	} // 
	
	private function createTweakedFile($filename, $extension, $file_path, $new_width, $new_height, $origin_width, $origin_height, $origin_filemtime) {
		switch ($extension):
	  	case 'gif':
	  		$origin_image = imagecreatefromgif($file_path);
	      break;
	    case 'jpeg':
	    case 'jpg':
      	$origin_image = imagecreatefromjpeg($file_path);
	      break;
	    case 'png':
	      $origin_image = imagecreatefrompng($file_path);
	      break;
	    default: 
	      // unsupported image type
	      return false;
	  	endswitch;
	  	
	  // create new image of $new_width and $new_height
    $new_image = imagecreatetruecolor($new_width, $new_height);
    // Check if this image is PNG or GIF, then set if Transparent  
    if (($extension == 'gif') OR ($extension == 'png')) {
      imagealphablending($new_image, false);
      imagesavealpha($new_image,true);
      $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
      imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
    }
    imagecopyresampled(	$new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);

    
    $new_file = $this->createFileName($filename, $extension, $new_width, $new_height);
    $new_file = $this->tweak_path.$new_file;

    //Generate the file, and rename it to $newfilename
    switch ($extension): 
      case 'gif': 
      	imagegif($new_image, $new_file); 
       	break;
      case 'jpg':
      case 'jpeg': 
       	imagejpeg($new_image, $new_file); 
       	break;
      case 'png': 
       	imagepng($new_image, $new_file); 
       	break;
      default:  
       	// unsupported image type
       	return false;
    endswitch;
    if (!chmod($new_file, 0755)) {
    	$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_chmod, basename($new_file))));
    }
    if (($origin_filemtime !== false) && (touch($new_file, $origin_filemtime) === false)) {
    	$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_touch, basename($new_file))));
    }
    return $new_file;	  
	} // createTweakedFile()
	
	private function checkImage(&$image) {
		global $tweakTools;
		if (!isset($image['src'])) return false; // nothing to do...
		// CSS Klassen in ein Array einlesen
  	$classes = isset($image['class']) ? explode(" ", $image['class']) : array();
  	// pruefen ob dieses Bild ignoriert werden soll
  	if (in_array($this->class_no_tweak, $classes)) return false;
  	
  	$image['src'] = urldecode($image['src']);
  	
  	$img_path = str_replace(WB_URL, WB_PATH, $image['src']);
  	$img_path = $tweakTools->correctPathSeparator($img_path);
  	// pruefen, ob es sich um einen gueltigen Dateityp handelt
  	$path_parts = pathinfo($img_path);
  	// strtolower extension
  	$path_parts['extension'] = strtolower($path_parts['extension']);
  	// ungueltige Dateinendung?
  	if (!in_array($path_parts['extension'], $this->file_types)) return false;
  	// existiert die Datei?
  	if (!file_exists($img_path)) return false;
  	// ALT und TITLE Tags pruefen
  	if ($this->check_alt_tags) {
  		if (!isset($image['alt']) || (empty($image['alt']))) {
  			$image['alt'] = utf8_encode($this->default_alt_tag);
  		}
  		if ($this->set_title_tag && (!isset($image['title']) || (empty($image['title'])))) {
  			$image['title'] = $image['alt'];
  		}
  	}
  	
  	$show_width = (isset($image['width']) && !empty($image['width'])) ? $image['width'] : 0;
  	$show_height = (isset($image['height']) && !empty($image['width'])) ? $image['height'] : 0;
  	
  	// Originial Abmessungen ermitteln  	
  	list($origin_width, $origin_height) = getimagesize($img_path);
  	$origin_filemtime = filemtime($img_path);
  	
  	if ((is_numeric($show_height) && ($show_height > 0)) && (is_numeric($show_width) && ($show_width > 0))) {
  		// Hoehe und Breite sind mit numerischen Werten gesetzt
  		if (($origin_width == $show_width) && ($origin_height == $show_height)) {
  			// kein Optimierungsbedarf
  			return true;
  		}
  		$tweaked_file = $this->createFileName($path_parts['filename'], $path_parts['extension'], $show_width, $show_height);
  		if (file_exists($this->tweak_path.$tweaked_file)) {
  			// optimierte Datei existiert bereits
  			$tweaked_filemtime = filemtime($this->tweak_path.$tweaked_file);
  			if (($origin_filemtime == $tweaked_filemtime) && (($origin_filemtime !== false) && ($tweaked_filemtime !== false))) {
  				$image['src'] = $this->tweak_url.$tweaked_file;
  				return true;
  			}
  			else {
  				// Datei hat sich geaendert, loeschen...
  				if (!unlink($this->tweak_path.$tweaked_file)) {
  					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_unlink, $tweaked_file)));
  				}
  			}
  		}
  		if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), 
  	  																												$show_width, $show_height, $origin_width, $origin_height, $origin_filemtime))) return false;
  	  $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
  	  return true;
  	}
  	elseif (is_numeric($show_height) && ($show_height > 0)) {
  		// es ist nur die Hoehe angegeben
  		if ($origin_height == $show_height) {
  			// Breitenangabe wurde nur vergessen, kein Optimierungsbedarf
  			$image['width'] = $origin_width;
  			return true;
  		}
  		// relative Breite berechnen
  		$percent = (int) ($show_height/($origin_height/100));
  		$image['width'] = (int) ($origin_width/100)*$percent;
  		if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), 
  	  																												$image['width'], $show_height, $origin_width, $origin_height, $origin_filemtime))) return false;
  	  $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
  	  return true;
  	}
  	elseif (is_numeric($show_width) && ($show_width > 0)) {
  		// es ist nur die Breite angegeben
  		if ($origin_width == $show_width) {
  			// Hoehenangabe wurde nur vergessen...
  			$image['height'] = $origin_height;
  			return true;
  		}	
  		// relative Hoehe berechnen
  		$percent = (int) ($show_width/($origin_width/100));
  		$image['height'] = (int) ($origin_height/100)*$percent;
  		if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), 
  	  																												$show_width, $image['height'], $origin_width, $origin_height, $origin_filemtime))) return false;
  	  $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
  	  return true;
  	}
  	elseif (($show_height == 0) && ($show_width == 0)) {
  		// Hoehe und Breite setzen und zurueck...
  		$image['height'] = $origin_height;
  		$image['width'] = $origin_width;
  		return true;
  	}
  	elseif ((strpos($show_width, '%') !== false) && (strpos($show_height, '%') !== false)) {
  		// Prozentangaben fuer Breite und Hoehe
  		$percent = $tweakTools->str2int($show_height);
  		$image['height'] = (int) ($origin_height/100)*$percent;
  		$percent = $tweakTools->str2int($show_width);
  		$image['width'] = (int) ($origin_width/100)*$percent;
  		if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), 
  	  																												$image['width'], $image['height'], $origin_width, $origin_height, $origin_filemtime))) return false;
  	  $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
  	  return true;		
  	}
  	elseif (strpos($show_width, '%') !== false) {
  		// Breite prozentual gesetzt
  		$percent = $tweakTools->str2int($show_width);
  		$image['width'] = (int) ($origin_width/100)*$percent;
  		$image['height'] = (int) ($origin_height/100)*$percent;
  		if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), 
  	  																												$image['width'], $image['height'], $origin_width, $origin_height, $origin_filemtime))) return false;
  	  $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
  	  return true;
  	}
  	elseif (strpos($show_height, '%') !== false) {
  		// Hoehe prozentual gesetzt
  		$percent = $tweakTools->str2int($show_height);
  		$image['width'] = (int) ($origin_width/100)*$percent;
  		$image['height'] = (int) ($origin_height/100)*$percent;
  		if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), 
  	  																												$image['width'], $image['height'], $origin_width, $origin_height, $origin_filemtime))) return false;
  	  $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
  	  return true;
  	}
  	
 		// keine Ahnung, was optimiert werden koennte...
 		return false;
	} // checkImage()
	
} // class processContent