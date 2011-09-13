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
 * 
 * IMPORTANT NOTE:
 * 
 * If you are editing this file or creating a new language file
 * you must ensure that you SAVE THIS FILE UTF-8 ENCODED.
 * Otherwise all special chars will be destroyed and displayed improper!
 * It is NOT NECESSARY to mask special chars as HTML entities!
 * 
 * Translated to German (Original Source) by Ralf Hertsch  
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
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}
// end include LEPTON class.secure.php

// Module description
$module_description = 'ImageTweak optimizes graphics automatically during output and accelerates the display of the site.';

// name of the person(s) who translated and edited this language file
$module_translation_by = '(sky writer)';

define ('tweak_btn_abort', 'Cancel');
define ('tweak_btn_edit', 'Edit');
define ('tweak_btn_export', 'Export');
define ('tweak_btn_import', 'Import');
define ('tweak_btn_ok', 'Save');
define ('tweak_btn_save', 'Save');

define ('tweak_category_error', 'Error');
define ('tweak_category_hint', 'Warning');
define ('tweak_category_info', 'Info');
define ('tweak_category_warning', 'Warning');

define ('tweak_cfg_thousand_separator',',');
define ('tweak_cfg_date', 'd.m.Y');
define ('tweak_cfg_date_separator', '.');
define ('tweak_cfg_date_time', 'Y - H: i');
define ('tweak_cfg_decimal_separator', '.');

define ('tweak_desc_cfg_check_alt_tags','Check if ALT attributes are set (1 = yes, 0 = No ).');
define ('tweak_desc_cfg_class_fancybox', 'The CSS class prompted to set a LINK for Fancybox. The preview (thumbnail) is optimized, which links to display the original Fancybox image. Fancybox must be installed for this purpose.');
define ('tweak_desc_cfg_class_no_tweak', 'CSS class to tell ImageTweak to ignore the picture, and no optimization is done.');
define ('tweak_desc_cfg_default_alt_tag', 'Alternative text to be set if the ALT attribute is missing or empty.');
define ('tweak_desc_cfg_exec', 'Do you want to run ImageTweak (0 = no, 1 = YES)');
define ('tweak_desc_cfg_extensions','File types that will be optimized by ImageTweak.');
define ('tweak_desc_cfg_fancybox_grp', 'The group name to be used by the Fancybox. Standard is: "grouped_elements" no group name is set (blank), is set in the link a class. ');
define ('tweak_desc_cfg_fancybox_rel', 'The REL (relation) attribute for calling the Fancybox, the designation is arbitrary. Standard "fancybox ".');
define ('tweak_desc_cfg_ignore_page_ids', 'Pages with the listed page_id will be ignored and not optimized by ImageTweak. Separate multiple IDs with a comma.');
define ('tweak_desc_cfg_ignore_topic_ids', 'TOPICS with the listed TOPIC_ID will be ignored and not optimized by ImageTweak. Separate multiple IDs with a comma.');
define ('tweak_desc_cfg_image_dir', 'Directory in the /Media folder where ImageTweak will store optimized images.');
define ('tweak_desc_cfg_jpeg_quality', 'ImageTweak JPEG compression quality. Standard is 90% - this corresponds to a compression of 10%.');
define ('tweak_desc_cfg_limit_log_entries', 'Number of entries to be stored in the ImageTweak log file.');
define ('tweak_desc_cfg_memory_buffer', 'Reserve memory in megabytes that is not touched by ImageTweak, to prevent a buffer overflow. If the memory limit is 32 MB and memory buffer is set to 4 MB, then ImageTweak will occupy a max 28 MB of available memory.');
define ('tweak_desc_cfg_memory_limit', '0 = SYSTEM. If you read in the log that ImageTweak does not ave enough memory available, set the memory limit to the proposed value and check the log again after you have visited a few pages.') ;
define ('tweak_desc_cfg_set_title_tag', 'If no TITLE attribute is set, use the ALT attribute (1 = yes, 0 = no) - requires ALT attribute to be set.');

define ('tweak_error_cfg_id', 'The configuration record with ID %05d could not be read');
define ('tweak_error_cfg_name', 'No configuration record was found to identify %s.');
define ('tweak_error_mkdir', 'The directory %s could not be created');
define ('tweak_error_memory_max', 'Memory is not currently available for ImageTweak. %d MB is available, increase the "Memory Limit" in the settings to %d. ');
define ('tweak_error_unlink', 'File %s could not be deleted');
define ('tweak_error_chmod', 'The permissions for the file %s could not be changed');
define ('tweak_error_touch', 'The modification time for file %s could not be set');
define ('tweak_error_patch_failed', 'The automatic adjustment of the output filter failed. Please consult the online documentation (http://phpManufaktur.de/image_tweak) about the possibility of manual adjustment.');
define ('tweak_error_patch_failed_unknown', 'The output filter was not found. The installation could not be completed. Please contact http://phpManufaktur.de.');
define ('tweak_error_patch_uninstall', 'The output filter could not be written back to the original state. You need to install the filter output again.');
define ('tweak_error_set_memory_limit', 'Memory_limit could not be set to %d MB.');
define ('tweak_error_skip_initialize', 'The page with ID %d was skipped because ImageTweak had to be re-initialized.');

define ('tweak_header_category', 'Type');
define ('tweak_header_cfg', 'Settings');
define ('tweak_header_cfg_description', 'Description');
define ('tweak_header_cfg_identifier', 'Identifier');
define ('tweak_header_cfg_import', 'Import Data');
define ('tweak_header_cfg_label', 'Label');
define ('tweak_header_cfg_typ', 'Type');
define ('tweak_header_cfg_value', 'Value');
define ('tweak_header_date', 'Date');
define ('tweak_header_log', 'Event and Error Log');
define ('tweak_header_page_id', 'Page');
define ('tweak_header_text', 'Message');

define ('tweak_intro_cfg', '<p>Edit the settings for <b>ImageTweak</ b></ p>.');
define ('tweak_intro_log', '<p>A log of various events and errors that occur during the operation of <b>ImageTweak </b></p>. ');
define ('tweak_intro_log_no_entries', '<p>There are no LOG entries available.</ p>');

define ('tweak_label_cfg_check_alt_tags', 'ALT attributes check');
define ('tweak_label_cfg_class_fancybox', 'Fancybox call');
define ('tweak_label_cfg_class_no_tweak', 'Image Ignore');
define ('tweak_label_cfg_default_alt_tag', 'ALT default');
define ('tweak_label_cfg_exec', 'Run ImageTweak');
define ('tweak_label_cfg_extensions', 'File Types');
define ('tweak_label_cfg_fancybox_grp', 'Fancybox: Group Name');
define ('tweak_label_cfg_fancybox_rel', 'Fancybox: REL attribute');
define ('tweak_label_cfg_ignore_page_ids', 'Ignore page_id');
define ('tweak_label_cfg_ignore_topic_ids', 'Ignore topic_id');
define ('tweak_label_cfg_image_dir', 'Optimized images directory');
define ('tweak_label_cfg_jpeg_quality', 'JPEG quality');
define ('tweak_label_cfg_limit_log_entries', 'LOG entries limit');
define ('tweak_label_cfg_memory_buffer', 'Memory Buffer');
define ('tweak_label_cfg_memory_limit', 'Memory Limit');
define ('tweak_label_cfg_set_title_tag', 'TITLE attribute set');

define ('tweak_log_mkdir', 'The directory %s was created. ');
define ('tweak_log_initialize_cfg', 'The configuration for ImageTweak was re-initialized. ');

define ('tweak_msg_already_patched', 'The output filter has been adjusted, no changes were made. Please use the online ImageTweak documentation (http://phpmanufaktur.de/image_tweak) to learn more.');
define ('tweak_msg_cfg_add_exists', '<p> The configuration data set with the identifier <b>%s</b> already exists and can not be added again. </ p>');
define ('tweak_msg_cfg_add_incomplete', '<p>The new configuration data set is incomplete. Please check your information.</ p>');
define ('tweak_msg_cfg_add_success','<p> The configuration data set with the <b>ID #%05d</b> and the identifier <b>%s</ b> has been added. </ p> ');
define ('tweak_msg_cfg_csv_export', '<p> The configuration data <b>%s</b> has been saved in /MEDIA directory </ p>. ');
define ('tweak_msg_cfg_id_updated', '<p> The configuration data set with the <b>ID #%05d</b> and the identifier <b>%s</b> has been updated. </ p> ');
define ('tweak_msg_invalid_email', 'The e-mail address %s is not valid, please check your input.');
define ('tweak_msg_patch_success','The output filter has been successfully adapted, ImageTweak is now ready for use. Please use the ImageTweak online documentation (http://phpmanufaktur.de/image_tweak) to learn more.');
define ('tweak_msg_patch_uninstall_success','The output filter has been successfully restored to its original state. ');

define ('tweak_tab_info', 'ImageTweak');
define ('tweak_tab_config', 'Settings');
define ('tweak_tab_log', 'Log');

define ('tweak_text_alt_default', '- no image description is available -');
?>