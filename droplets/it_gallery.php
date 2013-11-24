//:create a image gallery using imageTweak
//:Usage: [[it_gallery?folder=FOLDER_IN_MEDIA_DIR&width=PIXEL]] - still select a directory which should be used to create a folder gallery and tell imageTweak the width of the title image (default width is 250px), that's all!
/**
 * imageTweak
 *
 * @author Ralf Hertsch <ralf.hertsch@phpmanufaktur.de>
 * @link http://phpmanufaktur.de
 * @copyright 2008-2013
 * @license MIT License (MIT) http://www.opensource.org/licenses/MIT
 */
// load the image_tweak.jquery preset with LibraryAdmin
include_once WB_PATH.'/modules/libraryadmin/include.php';
$new_page = includePreset( $wb_page_data, 'lib_jquery', 'it_gallery', 'image_tweak', NULL, false, NULL, NULL );
if ( !empty($new_page) ) {
    $wb_page_data = $new_page;
}

if (file_exists(WB_PATH.'/modules/image_tweak/class.gallery.php')) {
  require_once(WB_PATH.'/modules/image_tweak/class.gallery.php');
  $gallery = new imageTweakGallery();
  $params = $gallery->getParams();
  $params[imageTweakGallery::PARAM_CSS] = (isset($css) && strtolower($css) == 'false') ? false : true;
  $params[imageTweakGallery::PARAM_FOLDER] = (isset($folder)) ? $folder : '';
  $params[imageTweakGallery::PARAM_RECURSIVE] = (isset($recursive) && strtolower($recursive) == 'false') ? false : true;
  $params[imageTweakGallery::PARAM_WIDTH] = (isset($width)) ? (int) $width : 200;
  $gallery->setParams($params);
  return $gallery->action();
}
else {
  return "imageTweak ist not installed!";
}