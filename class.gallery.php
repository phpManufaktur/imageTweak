<?php

/**
 * imageTweak
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 *
 * FOR VERSION- AND RELEASE NOTES PLEASE LOOK AT INFO.TXT!
 */

// include class.secure.php to protect this file and the whole CMS!
if (defined('WB_PATH')) {
    if (defined('LEPTON_VERSION')) include(WB_PATH.'/framework/class.secure.php');
} else {
    $oneback = "../";
    $root = $oneback;
    $level = 1;
    while (($level < 10) && (!file_exists($root.'/framework/class.secure.php'))) {
        $root .= $oneback;
        $level += 1;
    }
    if (file_exists($root.'/framework/class.secure.php')) {
        include($root.'/framework/class.secure.php');
    } else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
// end include class.secure.php

// include language file for imageTweak
if(!file_exists(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php')) {
    // default language is DE !!!
    require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/DE.php');
}
else {
    require_once(WB_PATH .'/modules/'.basename(dirname(__FILE__)).'/languages/' .LANGUAGE .'.php');
}

global $parser;
if (!class_exists('Dwoo')) {
    // search lib_dwoo (LEPTON only)
    if ( is_dir( WB_PATH.'/modules/lib_dwoo' ) ) {
        // as of version 1.2, LEPTON will autocreate the $parser object; this is
        // for backward compatibility
        if (!is_object($parser)) {
            require_once(WB_PATH.'/modules/lib_dwoo/dwoo/dwooAutoload.php');
            $cache_path = WB_PATH.'/temp/cache';
            if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
            $compiled_path = WB_PATH.'/temp/compiled';
            if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
            $parser = new Dwoo($compiled_path,$cache_path);
            set_include_path (
                    implode(
                            PATH_SEPARATOR,
                            array(
                                    realpath(WB_PATH.'/modules/lib_dwoo/dwoo'),
                                    get_include_path(),
                            )
                    )
            );
        }
    }
    // search dwoo module (WB only)
    elseif( is_dir( WB_PATH.'/modules/dwoo' ) ) {
        require_once(WB_PATH.'/modules/dwoo/include.php');
        if (!is_object($parser)) {
            $cache_path = WB_PATH.'/temp/cache';
            if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
            $compiled_path = WB_PATH.'/temp/compiled';
            if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
            $parser = new Dwoo($compiled_path,$cache_path);
            set_include_path (
                    implode(
                            PATH_SEPARATOR,
                            array(
                                    realpath(WB_PATH.'/modules/dwoo'),
                                    get_include_path(),
                            )
                    )
            );
        }
    }
    else {
        trigger_error(sprintf("[ <b>%s</b> ] Can't include Dwoo Template Engine!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
    }
}
if (!is_object($parser)) {
    $cache_path = WB_PATH.'/temp/cache';
    if (!file_exists($cache_path)) mkdir($cache_path, 0755, true);
    $compiled_path = WB_PATH.'/temp/compiled';
    if (!file_exists($compiled_path)) mkdir($compiled_path, 0755, true);
    $parser = new Dwoo($compiled_path,$cache_path);
    set_include_path (
            implode(PATH_SEPARATOR,
                    array(
                            realpath(WB_PATH.'/modules/dwoo'),
                            get_include_path(),
                    ))
    );
}

require_once(WB_PATH.'/framework/functions.php');

/**
 * FilterIterator for parsing images in the /MEDIA directory
 *
 * @author phpManufaktur, ralf.hertsch@phpmanufaktur.de
 */
class imageExtensionFilter extends FilterIterator {

    private $extensions;

    /**
     * Constructor imageExtensionFilter
     * Specify the allowed file extensions in the array $allowed_extensions
     *
     * @param iterator $iterator
     * @param array $allowed_extensions
     */
    public function __construct($iterator, $allowed_extensions = array('jpg','png','gif','jpeg')) {
        $this->setExtensions(implode('|', $allowed_extensions));
        parent::__construct($iterator);
    } // __construct()

    /**
     * @return the $extensions
     */
    protected function getExtensions ()
    {
        return $this->extensions;
    }

    /**
     * @param field_type $extensions
     */
    protected function setExtensions ($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * Set the accepted filter to the desired extension array and to directories
     * to enable recursing through the /MEDIA directory.
     *
     * @see FilterIterator::accept()
     */
    public function accept() {
        $filter = sprintf('%%.(%s)%%si', $this->getExtensions());
        return (($this->current()->isFile() &&
                preg_match($filter, $this->current()->getBasename()) ||
                $this->current()->isDir()));
    } // accept()

} //  imageExtensionFilter


class imageTweakGallery {
    
    const PARAM_CSS = 'css';
    const PARAM_FOLDER = 'folder';
    const PARAM_RECURSIVE = 'recursive';
    const PARAM_WIDTH = 'width';
    
    private $params = array(
            self::PARAM_CSS => true,
            self::PARAM_FOLDER => '',
            self::PARAM_RECURSIVE => true,
            self::PARAM_WIDTH => 200
            );
    
    const ZOOM_MAX_WIDTH = 800;
    
    private $tempPath;
    private $tempURL;
    private $templatePath;
    
    /**
     * classes added for compatibility to imageTweak
     * @see http://phpmanufaktur.de/image_tweak
     */
    const CLASS_CROP		= 'crop';
    const CLASS_TOP			= 'top';
    const CLASS_BOTTOM		= 'bottom';
    const CLASS_LEFT		= 'left';
    const CLASS_RIGHT		= 'right';
    const CLASS_ZOOM	    = 'zoom';
    const CLASS_NO_CACHE	= 'no-cache';
    
    public function __construct() {
        $this->setTemplatePath(WB_PATH.'/modules/image_tweak/htt/');
        $this->setTempPath(WB_PATH.'/temp/image_tweak/gallery/');
        $this->setTempURL(WB_URL.'/temp/image_tweak/gallery/');
        if (!file_exists($this->getTempPath())) {
            try {
                mkdir($this->getTempPath(), 0755, true);
            }
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_mkdir, $this->getTempPath(), $ex->getMessage())));
                return false;
            }
        }        
    } // __construct()

	/**
     * @return the $templatePath
     */
    protected function getTemplatePath ()
    {
        return $this->templatePath;
    }

	/**
     * @param field_type $templatePath
     */
    protected function setTemplatePath ($templatePath)
    {
        $this->templatePath = $templatePath;
    }

	/**
     * @return the $tempURL
     */
    protected function getTempURL ()
    {
        return $this->tempURL;
    }

	/**
     * @param field_type $tempURL
     */
    protected function setTempURL ($tempURL)
    {
        $this->tempURL = $tempURL;
    }

	/**
     * @return the $tempPath
     */
    protected function getTempPath ()
    {
        return $this->tempPath;
    }

	/**
     * @param field_type $tempPath
     */
    protected function setTempPath ($tempPath)
    {
        $this->tempPath = $tempPath;
    }
    
    /**
     * Return the params available for the droplet [[kit_idea]] as array
     *
     * @return ARRAY $params
     */
    public function getParams ()
    {
        return $this->params;
    } // getParams()
    
    /**
     * Set the params for the droplet [[gallery]]
     *
     * @param ARRAY $params
     * @return BOOL
     */
    public function setParams($params = array()) {
        $this->params = $params;
        $this->params[self::PARAM_FOLDER] = $this->removeLeadingSlash($this->params[self::PARAM_FOLDER]);
        return true;
    } // setParams()
    
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
     * Process the desired template and returns the result as string
     *
     * @param STR $template
     * @param ARRAY $template_data
     * @return STR $result
     */
    public function getTemplate($template, $template_data) {
        global $parser;
        try {
            $result = $parser->get($this->getTemplatePath().$template, $template_data);
        } catch (Exception $e) {
            $this->setError(sprintf(TSG_ERROR_TEMPLATE_ERROR, $template, $e->getMessage()));
            return false;
        }
        return $result;
    } // getTemplate()
    
    /**
     * removes a leading backslash from $path
     *
     * @param string $path
     * @return string $path
     */
    public function removeLeadingSlash($path) {
        $path = substr($path, 0, 1) == DIRECTORY_SEPARATOR ? substr($path, 1, strlen($path)) : $path;
        return $path;
    }
    
    /**
     * Haengt einen Slash an das Ende des uebergebenen Strings
     * wenn das letzte Zeichen noch kein Slash ist
     *
     * @param string $path
     * @return string
     */
    public function addSlash($path) {
        $path = substr($path, strlen($path)-1, 1) == DIRECTORY_SEPARATOR ? $path : $path.DIRECTORY_SEPARATOR;
        return $path;
    }
    
    /**
     * Action handler of the class
     * 
     * @return string formatted gallery on success or error prompt
     */
    public function action() {
        if (empty($this->params[self::PARAM_FOLDER])) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, tweak_error_param_folder_missing));
            return $this->show();
        }
        $images_path = WB_PATH.MEDIA_DIRECTORY.DIRECTORY_SEPARATOR.$this->params[self::PARAM_FOLDER];
        $images_path = $this->addSlash($images_path);
        if (!file_exists($images_path)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_param_folder_invalid, $this->params[self::PARAM_FOLDER])));
            return $this->show();
        }
        
        $temp_path_preview = $this->getTempPath().$this->params[self::PARAM_FOLDER].'/preview/';
        $temp_url_preview = $this->getTempURL().$this->params[self::PARAM_FOLDER].'/preview/';
        if (!file_exists($temp_path_preview)) {
            try {
                mkdir($temp_path_preview, 0755, true);
            }
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_mkdir, $temp_path_preview, $ex->getMessage())));
                return $this->show();
            }
        }
        $temp_path_zoom = $this->getTempPath().$this->params[self::PARAM_FOLDER].'/zoom/';
        $temp_url_zoom = $this->getTempURL().$this->params[self::PARAM_FOLDER].'/zoom/';
        if (!file_exists($temp_path_zoom)) {
            try {
                mkdir($temp_path_zoom, 0755, true);
            }
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_mkdir, $temp_path_zoom, $ex->getMessage())));
                return $this->show();
            }
        }
        
        $first = '';
        $links = '';
        $start = true;
        $description = false;
        $description_array = array();
        $start_file = '';
        $items = array();
        
        if (file_exists($images_path.'images.lst')) {
            if (false === ($fa = file($images_path.'images.lst', FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES))) {
                // error reading file
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(tweak_error_reading_file, 'images.lst')));
                return $this->show();
            }
            foreach ($fa as $line) {
                $x = substr_count($line, '|');
                if ($x == 1) {
                    list($file, $title) = explode('|', $line);
                    $description_array[trim($file)] = trim($title);
                }
                elseif ($x == 2) {
                    list($file, $title, $start) = explode('|', $line);
                    $description_array[trim($file)] = trim($title);
                    if ((strtolower(trim($start)) == 'true') && (file_exists($images_path.$file))) {
                        $start_file = trim($file);
                    } 
                }
            }
        }
        $iterator = new imageExtensionFilter(new RecursiveDirectoryIterator($images_path));
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                list($width, $height, $type) = getimagesize($fileinfo->__toString());
                $rewrite = false;
                if (file_exists($temp_path_preview.$fileinfo->getBasename())) {
                    // compare size and date
                    list($previewWidth, $previewHeight) = getimagesize($temp_path_preview.$fileinfo->getBasename());
                    if ($previewWidth != $this->params[self::PARAM_WIDTH]) $rewrite = true; 
                    if (filemtime($temp_path_preview.$fileinfo->getBasename()) != $fileinfo->getMTime()) $rewrite = true;
                }
                // create preview image
                if (!file_exists($temp_path_preview.$fileinfo->getBasename()) || $rewrite) {
                    // create preview image
                    if ($width > $this->params[self::PARAM_WIDTH]) {
                        // calculate size for icon
                        $percent = (int) ($this->params[self::PARAM_WIDTH]/($width/100));
                        $previewWidth = $this->params[self::PARAM_WIDTH];
                        $previewHeight = (int) ($height/(100)*$percent);
                    }
                    else {
                        // use orginal image dimensions
                        $previewWidth = $width;
                        $previewHeight = $height;
                    }
                    if (false == ($tweaked_file = $this->createTweakedFile(
                            $fileinfo->getBasename(),
                            strtolower(substr($fileinfo->getBasename(), strrpos($fileinfo->getBasename(), '.')+1)),
                            $fileinfo->__toString(),
                            $previewWidth,
                            $previewHeight,
                            $width,
                            $height,
                            $fileinfo->getMTime(),
                            $temp_path_preview))) {
                        // error creating the tweaked file
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                        return $this->show();
                    }
                }
                // create zoom image
                
                if (!file_exists($temp_path_zoom.$fileinfo->getBasename()) || $rewrite) {
                    // create preview image
                    if ($width > self::ZOOM_MAX_WIDTH) {
                        // calculate size for icon
                        $percent = (int) (self::ZOOM_MAX_WIDTH/($width/100));
                        $zoomWidth = self::ZOOM_MAX_WIDTH;
                        $zoomHeight = (int) ($height/(100)*$percent);
                    }
                    else {
                        // use orginal image dimensions
                        $zoomWidth = $width;
                        $zoomHeight = $height;
                    }
                    if (false == ($tweaked_file = $this->createTweakedFile(
                            $fileinfo->getBasename(),
                            strtolower(substr($fileinfo->getBasename(), strrpos($fileinfo->getBasename(), '.')+1)),
                            $fileinfo->__toString(),
                            $zoomWidth,
                            $zoomHeight,
                            $width,
                            $height,
                            $fileinfo->getMTime(),
                            $temp_path_zoom))) {
                        // error creating the tweaked file
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $this->getError()));
                        return $this->show();
                    }
                }
                else {
                    // get width and height from zoom image
                    list($zoomWidth, $zoomHeight) = getimagesize($temp_path_zoom.$fileinfo->getBasename());
                }
                
                $items[] = array(
                        'is_first' => ($start && (empty($start_file)) || ($fileinfo->getBasename() == $start_file)) ? 1 : 0,
                        'class' => media_filename($this->params[self::PARAM_FOLDER]),
                        'title' => isset($description_array[$fileinfo->getBasename()]) ? $description_array[$fileinfo->getBasename()] : '',
                        'zoom' => array(
                                'url' => $temp_url_zoom.$fileinfo->getBasename(),
                                'width' => $zoomWidth,
                                'height' => $zoomHeight
                        ),
                        'preview' => array(
                                'url' => $temp_url_preview.$fileinfo->getBasename(),
                                'width' => $previewWidth,
                                'height' => $previewHeight
                        )
                );
                // set start tag to false ...
                if ($start && (empty($start_file))) $start = false;
            }
        }        
        
        return $this->show($items);
    } // action()
    
    /**
     * prompt the formatted result
     *
     * @param STR $content - content to show
     *
     * @return STR dialog
     */
    public function show($data=array()) {
        if ($this->isError() || !is_array($data)) {
            $data = array(
                    'is_error' => 1,
                    'error' => $this->getError()
                    );
        }
        else {
            $data = array(
                    'is_error' => 0,
                    'gallery' => $data
                    );
        }
        // return the result
        return $this->getTemplate('frontend.gallery.lte', $data);
    } // show_main()
    
    /**
     * Master routine from imageTweak to create optimized images.
     * @see http://phpmanufaktur.de/image_tweak
     *
     * @param string $filename - basename of the image
     * @param string $extension - extension of the image
     * @param string $file_path - complete path to the image
     * @param integer $new_width - the new width in pixel
     * @param integer $new_height - the new height in pixel
     * @param integer $origin_width - the original width in pixel
     * @param integer $origin_height - the original height in pixel
     * @param integer $origin_filemtime - the FileMTime of the image
     * @param string $new_path - the path to the tweaked image
     * @param array $classes - optional es to force image operations
     * @return mixed - path to the new file on succes, boolean false on error
     */
    public function createTweakedFile($filename, $extension, $file_path,
            $new_width, $new_height, $origin_width, $origin_height,
            $origin_filemtime, $new_path, $classes=array()) {
        $extension = strtolower($extension);
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
            echo $extension;
        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_TWEAK_INVALID_EXTENSION, $extension)));
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
        if (in_array(self::CLASS_CROP, $classes)) {
            // don't change image size...
            $zoom = 100;
            foreach ($classes as $class) {
                if (stripos($class, self::CLASS_ZOOM.'[') !== false) {
                    $x = substr($class, strpos($class, '[')+1, (strpos($class, ']') - (strpos($class, '[')+1)));
                    $zoom = (int) $x;
                    if ($zoom < 1) $zoom = 1;
                    if ($zoom > 100) $zoom = 100;
                }
            }
            // crop image
            if (in_array(self::CLASS_LEFT, $classes)) {
                $x_pos = 0;
            }
            elseif (in_array(self::CLASS_RIGHT, $classes)) {
                $x_pos = $origin_width-$new_width;
            }
            else {
                $x_pos = ((int) $origin_width/2)-((int) $new_width/2);
            }
            if (in_array(self::CLASS_TOP, $classes)) {
                $y_pos = 0;
            }
            elseif (in_array(self::CLASS_BOTTOM, $classes)) {
                $y_pos = $origin_height-$new_height;
            }
            else {
                $y_pos = ((int) $origin_height/2) - ((int)$new_height/2);
            }
            if ($zoom !== 100) {
                // change image size and crop image
                $faktor = $zoom/100;
                $zoom_width = (int) ($origin_width*$faktor);
                $zoom_height = (int) ($origin_height*$faktor);
                imagecopyresampled($new_image, $origin_image, 0, 0, $x_pos, $y_pos, $new_width, $new_height, $zoom_width, $zoom_height);
            }
            else {
                // only crop image
                imagecopy($new_image, $origin_image, 0, 0, $x_pos, $y_pos, $new_width, $new_height);
            }
        }
        else {
            // resample image
            imagecopyresampled($new_image, $origin_image, 0, 0, 0, 0, $new_width, $new_height, $origin_width, $origin_height);
        }

        if (!file_exists($new_path)) {
            try {
                mkdir($new_path, 0755, true);
            }
            catch(ErrorException $ex) {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_MKDIR, $new_path, $ex->getMessage())));
                return false;
            }
        }
        
        $new_file = $new_path.$filename;
        //Generate the file, and rename it to $newfilename
        switch ($extension):
        case 'gif':
            imagegif($new_image, $new_file);
        break;
        case 'jpg':
        case 'jpeg':
            imagejpeg($new_image, $new_file, 90); // static setting for the JPEG Quality
            break;
        case 'png':
            imagepng($new_image, $new_file);
            break;
        default:
            // unsupported image type
            return false;
        endswitch;

        if (!chmod($new_file, 0644)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_CHMOD, basename($new_file))));
            return false;
        }
        if (($origin_filemtime !== false) && (touch($new_file, $origin_filemtime) === false)) {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(TSG_ERROR_TOUCH, basename($new_file))));
            return false;
        }
        return $new_file;
    } // createTweakedFile()
    
} // class imageTweakGallery