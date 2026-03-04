<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Hebrew strings for docviewer
 *
 * @package    mod_docviewer
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'DocViewer';
$string['modulenameplural'] = 'DocViewers';
$string['modulename_help'] = 'מודול DocViewer מאפשר למורים לספק קובץ כמשאב קורס עם תכונות צפייה ואבטחה מתקדמות.';
$string['docviewer:addinstance'] = 'הוספת משאב DocViewer חדש';
$string['docviewer:view'] = 'צפייה בתוכן DocViewer';
$string['pluginname'] = 'DocViewer';
$string['pluginadministration'] = 'ניהול DocViewer';

// Form strings.
$string['selectfiles'] = 'בחירת קובץ';
$string['selectfiles_help'] = 'העלה קובץ לשיתוף עם תלמידים. פורמטים נתמכים: PDF, מסמכי Word, מצגות PowerPoint וגיליונות Excel.';
$string['behaviour'] = 'התנהגות';
$string['enabledownload'] = 'אפשר הורדת קובץ';
$string['enabledownload_help'] = 'אם מופעל, תלמידים יכולים להוריד את הקובץ. אם כבוי, ניתן רק לצפות בו ברשת.';
$string['enableprinting'] = 'אפשר הדפסה';
$string['enableprinting_help'] = 'אם מופעל, תלמידים יכולים להדפיס את ה-PDF. אם כבוי, הדפסה חסומה.';
$string['enablecopying'] = 'אפשר העתקה';
$string['enablecopying_help'] = 'אם מופעל, תלמידים יכולים לסמן ולהעתיק טקסט מה-PDF. אם כבוי, בחירת טקסט חסומה.';
$string['enablesummarize'] = 'אפשר סיכום';
$string['enablesummarize_help'] = 'אם מופעל, תלמידים יכולים ליצור סיכום של תוכן הקובץ באמצעות בינה מלאכותית.';

// View strings.
$string['nofile'] = 'טרם הועלה קובץ.';
$string['download'] = 'הורדה';
$string['summarize'] = 'סיכום';
$string['downloadnotenabled'] = 'הורדות אינן מופעלות עבור קובץ זה.';
$string['nodocviewers'] = 'לא נמצאו פעילויות DocViewer בקורס זה.';
$string['intro'] = 'תיאור';
$string['page-mod-docviewer-x'] = 'כל דף מודול DocViewer';

// Summarize strings.
$string['summarizing'] = 'מייצר סיכום';
$string['summary'] = 'סיכום';
$string['summarizeerror'] = 'שגיאה ביצירת הסיכום. נא לנסות שוב.';
$string['summarysuccess'] = 'הסיכום נוצר בהצלחה';
$string['summarizenotenabled'] = 'סיכום אינו מופעל עבור קובץ זה.';
$string['summaryplaceholder'] = 'זהו סיכום המסמך. בסביבת ייצור, זה יופעל על ידי שירות בינה מלאכותית שמנתח את תוכן המסמך.';
$string['summarizenotsupported'] = 'סיכום עדיין אינו נתמך לסוג קובץ זה.';
