<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * AJAX endpoint for file summarization
 *
 * @package    mod_fileca
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

require_login();
require_sesskey();

$json = file_get_contents('php://input');
$data = json_decode($json);

$contextid = required_param('contextid', PARAM_INT);
$filecaid = required_param('filecaid', PARAM_INT);

$context = context::instance_by_id($contextid);
require_capability('mod/fileca:view', $context);

$fileca = $DB->get_record('fileca', array('id' => $filecaid), '*', MUST_EXIST);

if (empty($fileca->enablesummarize)) {
    echo json_encode(['success' => false, 'error' => get_string('summarizenotenabled', 'fileca')]);
    exit;
}

// Get the file content
$fs = get_file_storage();
$files = $fs->get_area_files($contextid, 'mod_fileca', 'content', 0, 'sortorder DESC, id ASC', false);

if (empty($files)) {
    echo json_encode(['success' => false, 'error' => get_string('nofile', 'fileca')]);
    exit;
}

$file = reset($files);

// Generate summary
$summary = fileca_generate_summary($file);

if ($summary) {
    echo json_encode(['success' => true, 'summary' => $summary]);
} else {
    echo json_encode(['success' => false, 'error' => get_string('summarizeerror', 'fileca')]);
}
