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
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include LEPTON class.secure.php!", $_SERVER['SCRIPT_NAME']));
}
// end include LEPTON class.secure.php

// Module description
$module_description = 'imageTweak optimiert Grafiken während der Ausgabe automatisch und beschleunigt die Anzeige der Website.';

// name of the person(s) who translated and edited this language file
$module_translation_by = 'Ralf Hertsch (phpManufaktur)';

define('tweak_btn_abort',													'Abbruch');
define('tweak_btn_edit',													'Bearbeiten');
define('tweak_btn_export',												'Exportieren');
define('tweak_btn_import',												'Importieren');
define('tweak_btn_ok',														'Übernehmen');
define('tweak_btn_save',													'Speichern');

define('tweak_category_error',										'Fehler');
define('tweak_category_hint',											'Hinweis');
define('tweak_category_info',											'Info');
define('tweak_category_warning',									'Warnung');

define('tweak_cfg_thousand_separator',						'.');
define('tweak_cfg_date',													'd.m.Y');
define('tweak_cfg_date_separator',								'.');
define('tweak_cfg_date_time',											'd.m.Y - H:i');
define('tweak_cfg_decimal_separator',							',');

define('tweak_desc_cfg_check_alt_tags',						'Prüfen, ob ALT Attribute gesetzt sind (1=JA, 0=Nein).');
define('tweak_desc_cfg_class_fancybox',						'CSS Klasse die imageTweak dazu veranlasst, einen LINK für die Fancybox zu setzen. Das Vorschaubild (Thumbnail) wird optimiert, die Fancybox zeigt das Originalbild an. Die Fancybox muss hierzu installiert sein.');
define('tweak_desc_cfg_class_no_tweak',						'CSS Klasse die imageTweak dazu veranlasst ein Bild zu ignorieren und keine Änderungen durchzuführen');
define('tweak_desc_cfg_default_alt_tag',					'Alternativ Text, der gesetzt werden soll, wenn das ALT Attribut fehlt oder leer ist.');
define('tweak_desc_cfg_exec',											'Legen Sie fest ob imageTweak ausgeführt wird oder nicht (0 = NEIN, 1 = JA)');
define('tweak_desc_cfg_extensions',								'Dateitypen, bei denen imageTweak eine Optimierung durchführen soll. Kleinschreibung beachten!');
define('tweak_desc_cfg_fancybox_grp',							'Der Gruppenname der von der Fancybox verwendet werden soll. Standard ist: "grouped_elements". Wird kein Gruppenname gesetzt (leer), wird im Link keine Klasse gesetzt.');
define('tweak_desc_cfg_fancybox_rel',							'Das REL (relation) Attribut für den Aufruf der Fancybox, die Bezeichnung ist beliebig. Standard ist "fancybox".');
define('tweak_desc_cfg_ignore_page_ids',					'Seiten mit den aufgeführten PAGE_IDs werden von imageTweak ignoriert und nicht optimiert. Trennen Sie mehrere IDs mit einem Komma.');
define('tweak_desc_cfg_ignore_topic_ids',					'TOPICS Artikel mit den aufgeführten TOPIC_IDs werden von imageTweak ignoriert und nicht optimiert. Trennen Sie mehrere IDs mit einem Komma.');
define('tweak_desc_cfg_image_dir',								'Verzeichnis im /MEDIA Ordner, das imageTweak für die Speicherung von optimierten Bildern verwendet.');
define('tweak_desc_cfg_jpeg_quality',							'JPEG Qualität, die von imageTweak bei der Komprimierung angestrebt wird, Standard ist 90% - dies entspricht einer Kompression von 10%');
define('tweak_desc_cfg_limit_log_entries',				'Anzahl der Einträge, die maximal in der LOG Datei von imageTweak gespeichert werden.');
define('tweak_desc_cfg_memory_buffer',						'Speicherreserve in Megabyte, die von imageTweak nicht angefasst wird um einen Speicherüberlauf zu verhindern. Beträgt das Memory Limit 32 MB und Memory Buffer 4 MB, dann belegt imageTweak max. 28 MB des verfügbaren Speicher.');
define('tweak_desc_cfg_memory_limit',							'0=SYSTEM. Falls Sie im Protokoll lesen, dass imageTweak nicht genügend Speicher zur Verfügung steht setzen Sie das Memory Limit auf den vorgeschlagenen Wert und kontrollieren Sie das Protokoll erneut, nachdem Sie einige Seiten aufgerufen haben.');
define('tweak_desc_cfg_set_title_tag',						'Wenn kein TITLE Attribut gesetzt ist, wird das ALT Attribut übernommen (1=JA, 0=NEIN) - setzt Prüfung des ALT Attribut voraus.');

define('tweak_error_cfg_id',							 				'Der Konfigurationsdatensatz mit der ID %05d konnte nicht ausgelesen werden!');
define('tweak_error_cfg_name',						 				'Zu dem Bezeichner %s wurde kein Konfigurationsdatensatz gefunden!');
define('tweak_error_mkdir',												'Das Verzeichnis %s konnte nicht angelegt werden!');
define('tweak_error_memory_max',									'Der Speicher reicht imageTweak nicht aus. Zur Zeit stehen %d MB zur Verfügung, erhöhen Sie das "Memory Limit" in den Einstellungen auf %d.');
define('tweak_error_unlink',											'Die Datei %s konnte nicht gelöscht werden!');
define('tweak_error_chmod',												'Die Zugriffsrechte für die Datei %s konnten nicht geändert werden!');
define('tweak_error_touch',												'Die Modifikationszeit für die Datei %s konnte nicht gesetzt werden!');
define('tweak_error_patch_failed',								'Die automatische Anpassung des Ausgabefilter ist fehlgeschlagen. Bitte informieren Sie sich in der online Dokumentation (http://phpManufaktur.de/image_tweak) über die Möglichkeite der manuellen Anpassung.');
define('tweak_error_patch_failed_unknown',				'Der Ausgabefilter wurde nicht gefunden, die Installation konnte nicht abgeschlossen werden. Bitte nehmen Sie Kontakt mit der http://phpManufaktur.de auf!');
define('tweak_error_patch_uninstall',							'Der Ausgabefilter konnte nicht wieder in den ursprünglichen Zustand zurückgeschrieben werden. Installieren Sie bei Bedarf den Ausgabefilter erneut.');
define('tweak_error_set_memory_limit',						'memory_limit konnte nicht auf %dM gesetzt werden.');
define('tweak_error_skip_initialize',							'Die Seite mit der ID %d wurde übersprungen, da imageTweak neu initialisiert werden musste.');

define('tweak_header_category',										'Typ');
define('tweak_header_cfg',												'Einstellungen');
define('tweak_header_cfg_description',						'Beschreibung');
define('tweak_header_cfg_identifier',							'Bezeichner');
define('tweak_header_cfg_import',									'Daten importieren');
define('tweak_header_cfg_label',									'Label');
define('tweak_header_cfg_typ',										'Typ');
define('tweak_header_cfg_value',									'Wert');
define('tweak_header_date',												'Datum');
define('tweak_header_log',												'Ereignis- und Fehlerprotokoll');
define('tweak_header_page_id',										'Seite');
define('tweak_header_text',												'Meldung');

define('tweak_intro_cfg',													'<p>Bearbeiten Sie die Einstellungen für <b>imageTweak</b>.</p>');
define('tweak_intro_log',													'<p><b>imageTweak</b> protokolliert verschiedene Ereignisse und Fehler, die während des Betriebs auftreten.</p>');
define('tweak_intro_log_no_entries',							'<p>Es liegen keine LOG Einträge vor!</p>');

define('tweak_label_cfg_check_alt_tags',					'ALT Attribute prüfen');
define('tweak_label_cfg_class_fancybox',					'Fancybox aufrufen');
define('tweak_label_cfg_class_no_tweak',					'Bild ignorieren');
define('tweak_label_cfg_default_alt_tag',					'ALT Vorgabe');
define('tweak_label_cfg_exec',										'imageTweak ausführen');
define('tweak_label_cfg_extensions',							'Dateitypen');
define('tweak_label_cfg_fancybox_grp',						'Fancybox: Gruppenname');
define('tweak_label_cfg_fancybox_rel',						'Fancybox: REL Attribut');
define('tweak_label_cfg_ignore_page_ids',					'PAGE_IDs ignorieren');
define('tweak_label_cfg_ignore_topic_ids',				'TOPIC_IDs ignorieren');
define('tweak_label_cfg_image_dir',								'Verzeichnis für optimierte Bilder');
define('tweak_label_cfg_jpeg_quality',						'JPEG Qualität');
define('tweak_label_cfg_limit_log_entries',				'LOG Einträge begrenzen');
define('tweak_label_cfg_memory_buffer',						'Memory Buffer');
define('tweak_label_cfg_memory_limit',						'Memory Limit');
define('tweak_label_cfg_set_title_tag',						'TITLE Attribut setzen'); 

define('tweak_log_mkdir',													'Das Verzeichnis %s wurde angelegt.');
define('tweak_log_initialize_cfg',								'Die Konfiguration für imageTweak wurde neu initialisiert.');

define('tweak_msg_already_patched',								'Der Ausgabefilter ist bereits angepasst, es wurden keine Änderungen vorgenommen. Bitte nutzen Sie die online Dokumentation (http://phpmanufaktur.de/image_tweak) um mehr über imageTweak zu erfahren!');
define('tweak_msg_cfg_add_exists',								'<p>Der Konfigurationsdatensatz mit dem Bezeichner <b>%s</b> existiert bereits und kann nicht noch einmal hinzugefügt werden!</p>');
define('tweak_msg_cfg_add_incomplete',						'<p>Der neu hinzuzufügende Konfigurationsdatensatz ist unvollständig! Bitte prüfen Sie Ihre Angaben!</p>');
define('tweak_msg_cfg_add_success',								'<p>Der Konfigurationsdatensatz mit der <b>ID #%05d</b> und dem Bezeichner <b>%s</b> wurde hinzugefügt.</p>');
define('tweak_msg_cfg_csv_export',								'<p>Die Konfigurationsdaten wurden als <b>%s</b> im /MEDIA Verzeichnis gesichert.</p>');
define('tweak_msg_cfg_id_updated',								'<p>Der Konfigurationsdatensatz mit der <b>ID #%05d</b> und dem Bezeichner <b>%s</b> wurde aktualisiert.</p>');
define('tweak_msg_invalid_email',									'Die E-Mail Adresse %s ist nicht gültig, bitte prüfen Sie Ihre Eingabe.');
define('tweak_msg_patch_success',									'Der Ausgabefilter wurde erfolgreich angepasst, imageTweak ist jetzt einsatzbereit! Bitte nutzen Sie die online Dokumentation (http://phpmanufaktur.de/image_tweak) um mehr über imageTweak zu erfahren!');
define('tweak_msg_patch_uninstall_success',				'Der Ausgabefilter wurde erfolgreich wieder in den ursprünglichen Zustand versetzt.');

define('tweak_tab_info',													'imageTweak');
define('tweak_tab_config',												'Einstellungen');
define('tweak_tab_log',														'Protokoll');

define('tweak_text_alt_default',									'- es ist keine Bildbeschreibung verfügbar -');
?>