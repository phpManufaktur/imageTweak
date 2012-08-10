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

// Sprachdateien einbinden
if (! file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php')) {
    require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/DE.php');
} else {
    require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/languages/' . LANGUAGE . '.php');
}

require_once (WB_PATH . '/framework/functions.php');

function tweakImages($content) {
    $tweak = new processContent();
    return $tweak->exec($content);
} // tweakImages()


class processContent {

    private $content;
    private $tweak_path;
    private $tweak_url;
    private $media_url;
    private $error;
    private $memory_limit;
    private $memory_max;

    const cfgTweakExec = 'cfgTweakExec';
    const cfgTweakImageDir = 'cfgTweakImageDir';
    const cfgClassNoTweak = 'cfgClassNoTweak';
    const cfgExtensions = 'cfgExtensions';
    const cfgCheckAltTags = 'cfgCheckAltTags';
    const cfgDefaultAltTag = 'cfgDefaultAltTag';
    const cfgSetTitleTag = 'cfgSetTitleTag';
    const cfgIgnorePageIDs = 'cfgIgnorePageIDs';
    const cfgIgnoreTopicIDs = 'cfgIgnoreTopicIDs';
    const cfgClassFancybox = 'cfgClassFancybox';
    const cfgFancyboxRel = 'cfgFancyboxRel';
    const cfgFancyboxGrp = 'cfgFancyboxGrp';
    const cfgMemoryLimit = 'cfgMemoryLimit';
    const cfgMemoryBuffer = 'cfgMemoryBuffer';
    const cfgJPEGquality = 'cfgJPEGquality';
    const cfgChangeURL2WB_URL = 'cfgChangeURL2WB_URL';

    private $settings = array(
            self::cfgTweakExec => 'cfgTweakExec',
            self::cfgTweakImageDir => 'cfgTweakImageDir',
            self::cfgClassNoTweak => 'cfgClassNoTweak',
            self::cfgExtensions => 'cfgExtensions',
            self::cfgCheckAltTags => 'cfgCheckAltTags',
            self::cfgDefaultAltTag => 'cfgDefaultAltTag',
            self::cfgSetTitleTag => 'cfgSetTitleTag',
            self::cfgIgnorePageIDs => 'cfgIgnorePageIDs',
            self::cfgIgnoreTopicIDs => 'cfgIgnoreTopicIDs',
            self::cfgClassFancybox => 'cfgClassFancybox',
            self::cfgFancyboxRel => 'cfgFancyboxRel',
            self::cfgFancyboxGrp => 'cfgFancyboxGrp',
            self::cfgMemoryLimit => 'cfgMemoryLimit',
            self::cfgMemoryBuffer => 'cfgMemoryBuffer',
            self::cfgJPEGquality => 'cfgJPEGquality',
            self::cfgChangeURL2WB_URL => 'cfgChangeURL2WB_URL'
            );

    const classCrop = 'crop';
    const classTop = 'top';
    const classBottom = 'bottom';
    const classLeft = 'left';
    const classRight = 'right';
    const classZoom = 'zoom';
    const classNoCache = 'no-cache';

    protected static $config_file = 'config.json';
    protected static $table_prefix = TABLE_PREFIX;

  /**
   * Constructor
   */
  public function __construct() {
    // use another table prefix?
    if (file_exists(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/config.json')) {
      $config = json_decode(file_get_contents(WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/config.json'), true);
      if (isset($config['table_prefix'])) self::$table_prefix = $config['table_prefix'];
    }

    if ($this->getSettings()) {
      $tweaked = $this->settings[self::cfgTweakImageDir];
      $tweaked = $this->removeLeadingSlash($this->addSlash($tweaked));
      // check if old tweak directories exists
      if (file_exists(WB_PATH . MEDIA_DIRECTORY . '/' . $tweaked.'pages')) {
        self::rrmdir(WB_PATH . MEDIA_DIRECTORY . '/' . $tweaked.'pages');
        self::rrmdir(WB_PATH . MEDIA_DIRECTORY . '/' . $tweaked.'topics');
      }
      $url_dir = substr(WB_URL, strpos(WB_URL, '://')+3);
      $url_dir = $this->addSlash($url_dir);
      $this->tweak_path = WB_PATH . MEDIA_DIRECTORY . '/' . $tweaked .$url_dir;
      $this->tweak_path .= (defined('TOPIC_ID')) ? 'topics/' . TOPIC_ID . '/' : 'pages/' . PAGE_ID . '/';
      if (!file_exists($this->tweak_path)) {
        if (!mkdir($this->tweak_path, 0755, true)) {
          $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_mkdir, $this->tweak_path)));
        }
        else {
          $this->writeLog(sprintf(tweak_log_mkdir, $this->tweak_path), 'info');
        }
      }
      $this->tweak_url = str_replace(WB_PATH, WB_URL, $this->tweak_path);
      $this->media_url = WB_URL . MEDIA_DIRECTORY . '/';

      // Memory Limit in MB aus der Konfiguration
      $limit = $this->settings[self::cfgMemoryLimit];
      // Umrechnung in Bytes
      $this->memory_limit = $limit * 1024 * 1024;
      if (($this->memory_limit > 0) && (false === (ini_set("memory_limit", sprintf("%sM", $limit))))) {
        // Fehler beim Setzen des neuen Memory Limits
        $this->setError(sprintf(tweak_error_set_memory_limit, $this->memory_limit));
      }
      else {
        $limit = ini_get('memory_limit');
        $this->memory_limit = $this->iniReturnBytes($limit);
      }
      // maximale Speichernutzung festlegen
      $buffer = $this->settings[self::cfgMemoryBuffer] * 1024 * 1024;
      $this->memory_max = $this->memory_limit - $buffer;
    }
  } // __construct()


  protected static function rrmdir($dir) {
    foreach(glob($dir . '/*') as $file) {
      if(is_dir($file))
        self::rrmdir($file);
      else
        unlink($file);
    }
    rmdir($dir);
  } // rrmdir()

    public function setError($error) {
        $this->error = $error;
        $this->writeLog($error, 'error');
    } // setError()


    public function getError() {
        return $this->error;
    } // getError()


    public function isError() {
        return (bool) ! empty($this->error);
    } // isError()


    private function writeLog($message, $message_type) {
        global $database;
        $SQL = sprintf("INSERT INTO %smod_img_tweak_log (log_category, log_page_id, log_text) VALUES ('%s','%s','%s')", self::$table_prefix, $message_type, PAGE_ID, $message);
        // just write to LOG - here is no chance to trigger any errors
        $database->query($SQL);
    } // writeLog()


    private function initializeSettings() {
        require_once (WB_PATH . '/modules/' . basename(dirname(__FILE__)) . '/class.tweak.php');
        $tweakCfg = new dbImageTweakCfg();
        if ($tweakCfg->isError()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $tweakCfg->getError()));
            return false;
        }
        $this->writeLog(tweak_log_initialize_cfg, 'info');
        return true;
    }

    private function getSettings() {
        global $database;
        $SQL = "SELECT cfg_name, cfg_value FROM " . self::$table_prefix . "mod_img_tweak_config WHERE cfg_status = '1'";
        $result = $database->query($SQL);
        if ($database->is_error()) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $database->get_error()));
            return false;
        }
        while (false !== ($data = $result->fetchRow(MYSQL_ASSOC))) {
            switch ($data['cfg_name']) :
                case self::cfgSetTitleTag:
                case self::cfgTweakExec:
                case self::cfgCheckAltTags:
                    // boolean field
                    $this->settings[$data['cfg_name']] = (bool) $data['cfg_value'];
                    break;
                case self::cfgFancyboxRel:
                case self::cfgFancyboxGrp:
                case self::cfgDefaultAltTag:
                case self::cfgTweakImageDir:
                case self::cfgClassNoTweak:
                case self::cfgClassFancybox:
                    // string field
                    $this->settings[$data['cfg_name']] = $data['cfg_value'];
                    break;
                case self::cfgIgnoreTopicIDs:
                case self::cfgIgnorePageIDs:
                case self::cfgExtensions:
                case self::cfgChangeURL2WB_URL:
                    // array field
                    $this->settings[$data['cfg_name']] = explode(",", $data['cfg_value']);
                    break;
                case self::cfgMemoryBuffer:
                case self::cfgMemoryLimit:
                case self::cfgJPEGquality:
                    // integer field
                    $this->settings[$data['cfg_name']] = (integer) $data['cfg_value'];
                    break;
            endswitch
            ;
        }
        // pruefen ob alle Felder initialisiert wurden
        foreach ($this->settings as $key => $value) {
            if ($key == (string) $value) {
                if ($this->initializeSettings()) {
                    // Konfiguration neu initialisiert
                    $this->setError(sprintf(tweak_error_skip_initialize, PAGE_ID));
                    return false;
                } else {
                    // Fehler bei der Initialisierung
                    return false;
                }
            }
        }
        if (($this->settings[self::cfgJPEGquality] < 15) || ($this->settings[self::cfgJPEGquality] > 100)) {
            $this->settings[self::cfgJPEGquality] = 75;
        }
        return true;
    } // getSettings()


    public function setContent($content) {
        $this->content = $content;
    } // setContent()


    public function getContent() {
        return $this->content;
    } // getContent()


    public function iniReturnBytes($size_str) {
        switch (substr($size_str, - 1)) :
            case 'M':
            case 'm':
                return (int) $size_str * 1048576;
            case 'K':
            case 'k':
                return (int) $size_str * 1024;
            case 'G':
            case 'g':
                return (int) $size_str * 1073741824;
            default:
                return $size_str;
        endswitch
        ;
    } // ini_return_bytes()


    public function removeLeadingSlash($path) {
        $path = substr($path, 0, 1) == "/" ? substr($path, 1, strlen($path)) : $path;
        return $path;
    } // removeLeadingSlash()


    public function addSlash($path) {
        $path = substr($path, strlen($path) - 1, 1) == "/" ? $path : $path . "/";
        return $path;
    }

    public function exec($content) {
        // bei Fehler sofort raus
        if ($this->isError()) return $content;
        // sofort wieder raus, wenn imageTweak ausgeschaltet ist
        if (! $this->settings[self::cfgTweakExec]) return $content;
        // pruefen ob die PAGE_ID ignoriere werden soll
        if (in_array(PAGE_ID, $this->settings[self::cfgIgnorePageIDs])) return $content;
        // pruefen ob die TOPIC_ID ignoriert werden soll
        if (defined('TOPIC_ID') && (in_array(TOPIC_ID, $this->settings[self::cfgIgnoreTopicIDs]))) return $content;
        // replace URLs?
        if (!empty($this->settings[self::cfgChangeURL2WB_URL]) && in_array(WB_URL, $this->settings[self::cfgChangeURL2WB_URL])) {
          //$search = array('https://phpmanufaktur.de','https://addons.phpmanufaktur.de/','https://blog.phpmanufaktur.de/','https://media.phpmanufaktur.de/','https://usergroup.phpmanufaktur.de/');
          $content = str_replace($this->settings[self::cfgChangeURL2WB_URL], WB_URL, $content);
        }
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
            if (is_file($this->tweak_path . $item)) $old_tweak_files[$item] = true;
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
                            list ($key, $value) = explode("=", $attribut);
                            $value = trim($value);
                            $value = substr($value, 1, strlen($value) - 2);
                            $img[strtolower(trim($key))] = trim($value);
                        }
                    }
                    if (($x = memory_get_usage()) >= $this->memory_max) {
                        // es steht nicht genuegend Speicher zur Verfuegung
                        $limit = (int) $this->memory_limit / 1024 / 1024;
                        $this->setError(sprintf(tweak_error_memory_max, $limit, $limit + 8));
                        return $this->content;
                    }
                    // nur Bilder pruefen, die sich im /MEDIA Verzeichnis befinden
                    if (! empty($img) && isset($img['src']) && ((strpos($img['src'], $this->media_url) !== false) && (strpos($img['src'], $this->media_url) == 0))) {
                        $org_src = $img['src'];
                        $classes = array();
                        // Bild pruefen, bei Fehler abbrechen
                        if ($this->checkImage($img, $classes)) {
                            // aus dem Array der alten Dateien entfernen
                            if (isset($old_tweak_files[basename($img['src'])])) unset($old_tweak_files[basename($img['src'])]);
                            // <img> tag schreiben
                            $new_tag = '';
                            foreach ($img as $key => $value) {
                                $new_tag .= sprintf(' %s="%s"', $key, $value);
                            }
                            $new_tag = sprintf('<img%s />', $new_tag);
                            // ggf. Fancybox setzen
                            if (in_array($this->settings[self::cfgClassFancybox], $classes)) {
                                $class = (! empty($this->settings[self::cfgFancyboxGrp])) ? sprintf(' class="%s"', $this->settings[self::cfgFancyboxGrp]) : '';
                                $title = (! empty($img['title'])) ? sprintf(' title="%s"', $img['title']) : '';
                                $new_tag = sprintf('<a%s href="%s" rel="%s"%s>%s</a>', $class, $org_src, $this->settings[self::cfgFancyboxRel], $title, $new_tag);
                            } elseif (in_array('tweak-gallery', $classes)) {
                                $class = (! empty($this->settings[self::cfgFancyboxGrp])) ? sprintf(' class="%s"', $this->settings[self::cfgFancyboxGrp]) : '';
                                $title = (! empty($img['title'])) ? sprintf(' title="%s"', $img['title']) : '';
                                $new_tag = sprintf('<div class="tweak-gallery-item-image"><a%s href="%s" rel="%s"%s>%s</a><div class="tweak-gallery-item-title">%s</div></div>', $class, $org_src, $this->settings[self::cfgFancyboxRel], $title, $new_tag, $img['title']);
                            }
                            $this->content = str_replace($img_tag, $new_tag, $this->content);
                        }
                    }
                }
            }
        }
        // Verzeichnis aufraeumen und nicht mehr benoetigte Dateien entfernen
        foreach ($old_tweak_files as $key => $value) {
            if (! unlink($this->tweak_path . $key)) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_unlink, $this->tweak_path . $key)));
            }
        }
        return $this->content;
    } // checkContent()


    private function createFileName($filename, $extension, $width, $height) {
        $filename = page_filename($filename);
        return sprintf('%s_%d_%d.%s', $filename, $width, $height, $extension);
    } //


    public function correctPathSeparator($path) {
        return (DIRECTORY_SEPARATOR == '/') ? trim(str_replace("\\", "/", $path)) : trim(str_replace("/", "\\", $path));
    } // correctSlashes()


    private function createTweakedFile($filename, $extension, $file_path, $new_width, $new_height, $origin_width, $origin_height, $origin_filemtime, $classes = array()) {
        switch ($extension) :
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
        endswitch
        ;

        // create new image of $new_width and $new_height
        $new_image = imagecreatetruecolor($new_width, $new_height);
        // Check if this image is PNG or GIF, then set if Transparent
        if (($extension == 'gif') or ($extension == 'png')) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $new_width, $new_height, $transparent);
        }
        if (in_array(self::classCrop, $classes)) {
            // don't change image size...
            $zoom = 100;
            foreach ($classes as $class) {
                if (stripos($class, self::classZoom . '[') !== false) {
                    $x = substr($class, strpos($class, '[') + 1, (strpos($class, ']') - (strpos($class, '[') + 1)));
                    $zoom = (int) $x;
                    if ($zoom < 1) $zoom = 1;
                    if ($zoom > 100) $zoom = 100;
                }
            }
            // crop image
            if (in_array(self::classLeft, $classes)) {
                $x_pos = 0;
            } elseif (in_array(self::classRight, $classes)) {
                $x_pos = $origin_width - $new_width;
            } else {
                $x_pos = ((int) $origin_width / 2) - ((int) $new_width / 2);
            }
            if (in_array(self::classTop, $classes)) {
                $y_pos = 0;
            } elseif (in_array(self::classBottom, $classes)) {
                $y_pos = $origin_height - $new_height;
            } else {
                $y_pos = ((int) $origin_height / 2) - ((int) $new_height / 2);
            }
            if ($zoom !== 100) {
                // change image size and crop image
                $faktor = $zoom / 100;
                $zoom_width = (int) ($origin_width * $faktor);
                $zoom_height = (int) ($origin_height * $faktor);
                imagecopyresampled($new_image, $origin_image, 0, 0, $x_pos, $y_pos, $new_width, $new_height, $zoom_width, $zoom_height);
            } else {
                // only crop image
                imagecopy($new_image, $origin_image, 0, 0, $x_pos, $y_pos, $new_width, $new_height);
            }
        } else {
            // resample image
            imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);
        }

        $new_file = $this->createFileName($filename, $extension, $new_width, $new_height);
        $new_file = $this->tweak_path . $new_file;

        //Generate the file, and rename it to $newfilename
        switch ($extension) :
            case 'gif':
                imagegif($new_image, $new_file);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($new_image, $new_file, $this->settings[self::cfgJPEGquality]);
                break;
            case 'png':
                imagepng($new_image, $new_file);
                break;
            default:
                // unsupported image type
                return false;
        endswitch
        ;
        if (! chmod($new_file, 0755)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_chmod, basename($new_file))));
            return false;
        }
        if (($origin_filemtime !== false) && (touch($new_file, $origin_filemtime) === false)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_touch, basename($new_file))));
            return false;
        }
        return $new_file;
    } // createTweakedFile()


    private function checkImage(&$image, &$classes = array()) {
        if (! isset($image['src'])) return false; // nothing to do...
        // CSS Klassen in ein Array einlesen
        $classes = isset($image['class']) ? explode(" ", $image['class']) : array();
        // pruefen ob dieses Bild ignoriert werden soll
        if (in_array($this->settings[self::cfgClassNoTweak], $classes)) return false;
        $image['src'] = urldecode($image['src']);
        $img_path = str_replace(WB_URL, WB_PATH, $image['src']);
        $img_path = $this->correctPathSeparator($img_path);
        // pruefen, ob es sich um einen gueltigen Dateityp handelt
        $path_parts = pathinfo($img_path);
        // strtolower extension
        if (! isset($path_parts['extension'])) return false;
        $path_parts['extension'] = strtolower($path_parts['extension']);
        // ungueltige Dateinendung?
        if (! in_array($path_parts['extension'], $this->settings[self::cfgExtensions])) return false;
        // existiert die Datei?
        if (! file_exists($img_path)) return false;
        // ALT und TITLE Tags pruefen
        if ($this->settings[self::cfgCheckAltTags]) {
            if (! isset($image['alt']) || (empty($image['alt']))) {
                $image['alt'] = $this->settings[self::cfgDefaultAltTag];
            }
            if ($this->settings[self::cfgSetTitleTag] && (! isset($image['title']) || (empty($image['title'])))) {
                $image['title'] = $image['alt'];
            }
        }
        // Pruefen ob Hoehe und Breite ueber CSS Parameter gesetzt sind
        if (isset($image['style']) && (! empty($image['style']))) {
            $style_array = explode(';', $image['style']);
            foreach ($style_array as $style) {
                if (empty($style)) continue;
                if (strpos($style, ':') == false) continue;
                list ($name, $value) = explode(':', $style);
                $name = strtolower(trim($name));
                if (($name == 'width') || ($name == 'height')) {
                    $value = (strpos($value, '%') !== false) ? sprintf('%d%%', intval($value)) : intval($value);
                    $image[$name] = $value;
                }
            }
        }
        $show_width = (isset($image['width']) && ! empty($image['width'])) ? $image['width'] : 0;
        $show_height = (isset($image['height']) && ! empty($image['width'])) ? $image['height'] : 0;
        // Originial Abmessungen ermitteln
        list ($origin_width, $origin_height) = getimagesize($img_path);
        $origin_filemtime = filemtime($img_path);

        if ((is_numeric($show_height) && ($show_height > 0)) && (is_numeric($show_width) && ($show_width > 0))) {
            // Hoehe und Breite sind mit numerischen Werten gesetzt
            if (($origin_width == $show_width) && ($origin_height == $show_height)) {
                // kein Optimierungsbedarf
                return true;
            }
            if (($show_width > $origin_width) || ($show_height > $origin_height)) {
                // Bild ist hochgezoomt
                return true;
            }
            $tweaked_file = $this->createFileName($path_parts['filename'], $path_parts['extension'], $show_width, $show_height);
            if (file_exists($this->tweak_path . $tweaked_file)) {
                // optimierte Datei existiert bereits
                $tweaked_filemtime = filemtime($this->tweak_path . $tweaked_file);
                if ((! in_array(self::classNoCache, $classes) && ($origin_filemtime == $tweaked_filemtime)) && (($origin_filemtime !== false) && ($tweaked_filemtime !== false))) {
                    $image['src'] = $this->tweak_url . $tweaked_file;
                    return true;
                } else {
                    // Datei hat sich geaendert, loeschen...
                    if (! unlink($this->tweak_path . $tweaked_file)) {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_unlink, $tweaked_file)));
                    }
                }
            }
            if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), $show_width, $show_height, $origin_width, $origin_height, $origin_filemtime, $classes))) return false;
            $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
            return true;
        } elseif (is_numeric($show_height) && ($show_height > 0)) {
            // es ist nur die Hoehe angegeben
            if ($origin_height == $show_height) {
                // Breitenangabe wurde nur vergessen, kein Optimierungsbedarf
                $image['width'] = $origin_width;
                return true;
            }
            if ($show_height > $origin_height) return true; // Bild ist gezoomt
            // relative Breite berechnen
            $percent = (int) ($show_height / ($origin_height / 100));
            $image['width'] = (int) (($origin_width / 100) * $percent);
            if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), $image['width'], $show_height, $origin_width, $origin_height, $origin_filemtime, $classes))) return false;
            $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
            return true;
        } elseif (is_numeric($show_width) && ($show_width > 0)) {
            // es ist nur die Breite angegeben
            if ($origin_width == $show_width) {
                // Hoehenangabe wurde nur vergessen...
                $image['height'] = $origin_height;
                return true;
            }
            if ($show_width > $origin_width) return true; // Bild ist gezoomt
            // relative Hoehe berechnen
            $percent = (int) ($show_width / ($origin_width / 100));
            $image['height'] = (int) (($origin_height / 100) * $percent);
            if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), $show_width, $image['height'], $origin_width, $origin_height, $origin_filemtime, $classes))) return false;
            $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
            return true;
        } elseif (($show_height == 0) && ($show_width == 0)) {
            // Hoehe und Breite setzen und zurueck...
            $image['height'] = $origin_height;
            $image['width'] = $origin_width;
            return true;
        } elseif ((strpos($show_width, '%') !== false) && (strpos($show_height, '%') !== false)) {
            // Prozentangaben fuer Breite und Hoehe
            $h_percent = intval($show_height);
            $w_percent = intval($show_width);
            if (($h_percent > 100) || ($w_percent > 100)) return true; // Bild is gezoomt
            $image['height'] = (int) (($origin_height / 100) * $h_percent);
            $image['width'] = (int) (($origin_width / 100) * $w_percent);
            if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), $image['width'], $image['height'], $origin_width, $origin_height, $origin_filemtime, $classes))) return false;
            $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
            return true;
        } elseif (strpos($show_width, '%') !== false) {
            // Breite prozentual gesetzt
            $percent = intval($show_width);
            if ($percent > 100) return true; // Bild ist gezoomt
            $image['width'] = (int) (($origin_width / 100) * $percent);
            $image['height'] = (int) (($origin_height / 100) * $percent);
            if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), $image['width'], $image['height'], $origin_width, $origin_height, $origin_filemtime, $classes))) return false;
            $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
            return true;
        } elseif (strpos($show_height, '%') !== false) {
            // Hoehe prozentual gesetzt
            $percent = intval($show_height);
            if ($percent > 100) return true; // Bild ist gezoomt
            $image['width'] = (int) (($origin_width / 100) * $percent);
            $image['height'] = (int) (($origin_height / 100) * $percent);
            if (false === ($tweaked_file = $this->createTweakedFile($path_parts['filename'], $path_parts['extension'], str_replace(WB_URL, WB_PATH, $image['src']), $image['width'], $image['height'], $origin_width, $origin_height, $origin_filemtime, $classes))) return false;
            $image['src'] = str_replace(WB_PATH, WB_URL, $tweaked_file);
            return true;
        }

        // keine Ahnung, was optimiert werden koennte...
        return false;
    } // checkImage()


} // class processContent