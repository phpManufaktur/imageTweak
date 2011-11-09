//:create a image gallery using imageTweak
//:Please visit http://phpManufaktur.de for informations about imageTweak!
/**
 * imageTweak
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */
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