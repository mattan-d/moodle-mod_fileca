<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Library of interface functions and constants for module docviewer
 *
 * @package    mod_docviewer
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Supported features
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function docviewer_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

/**
 * Add docviewer instance.
 *
 * @param stdClass $data
 * @param mod_docviewer_mod_form $mform
 * @return int new docviewer instance id
 */
function docviewer_add_instance($data, $mform = null) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $data->timemodified = time();

    $data->id = $DB->insert_record('docviewer', $data);

    // Save the file.
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;
    $context = context_module::instance($cmid);
    
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_docviewer', 'content', 0,
                                    array('subdirs' => 0, 'maxfiles' => 1));
    }

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'docviewer', $data->id, $completiontimeexpected);

    return $data->id;
}

/**
 * Update docviewer instance.
 *
 * @param object $data
 * @param object $mform
 * @return bool true
 */
function docviewer_update_instance($data, $mform) {
    global $CFG, $DB;
    require_once("$CFG->libdir/resourcelib.php");

    $data->timemodified = time();
    $data->id           = $data->instance;

    $DB->update_record('docviewer', $data);

    // Save the file.
    $cmid = $data->coursemodule;
    $draftitemid = $data->files;
    $context = context_module::instance($cmid);
    
    if ($draftitemid) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_docviewer', 'content', 0,
                                    array('subdirs' => 0, 'maxfiles' => 1));
    }

    $completiontimeexpected = !empty($data->completionexpected) ? $data->completionexpected : null;
    \core_completion\api::update_completion_date_event($cmid, 'docviewer', $data->id, $completiontimeexpected);

    return true;
}

/**
 * Delete docviewer instance.
 *
 * @param int $id
 * @return bool true
 */
function docviewer_delete_instance($id) {
    global $DB;

    if (!$docviewer = $DB->get_record('docviewer', array('id' => $id))) {
        return false;
    }

    $cm = get_coursemodule_from_instance('docviewer', $id);
    \core_completion\api::update_completion_date_event($cm->id, 'docviewer', $id, null);

    $DB->delete_records('docviewer', array('id' => $docviewer->id));

    return true;
}

/**
 * List the actions that correspond to a view of this module.
 *
 * @return array
 */
function docviewer_get_view_actions() {
    return array('view', 'view all');
}

/**
 * List the actions that correspond to a post of this module.
 *
 * @return array
 */
function docviewer_get_post_actions() {
    return array();
}

/**
 * Serve the files from the docviewer file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function docviewer_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB;

    error_log('[docviewer pluginfile] ========== START ==========');
    error_log('[docviewer pluginfile] filearea: ' . $filearea);
    error_log('[docviewer pluginfile] args (raw): ' . print_r($args, true));
    error_log('[docviewer pluginfile] forcedownload: ' . ($forcedownload ? 'true' : 'false'));
    debugging('[docviewer pluginfile] ENTRY - filearea: ' . $filearea . ', args: ' . print_r($args, true), DEBUG_DEVELOPER);
    
    if ($context->contextlevel != CONTEXT_MODULE) {
        debugging('[docviewer pluginfile] Context is not module level', DEBUG_DEVELOPER);
        error_log('[docviewer pluginfile] ERROR: Context is not module level');
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea !== 'content' && $filearea !== 'converted') {
        debugging('[docviewer pluginfile] Invalid filearea: ' . $filearea, DEBUG_DEVELOPER);
        error_log('[docviewer pluginfile] ERROR: Invalid filearea: ' . $filearea);
        return false;
    }

    $fs = get_file_storage();
    
    $itemid = array_shift($args);
    error_log('[docviewer pluginfile] Extracted itemid: ' . $itemid);
    
    $filename = array_pop($args);
    error_log('[docviewer pluginfile] Extracted filename: ' . $filename);
    
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }
    error_log('[docviewer pluginfile] Built filepath: ' . $filepath);
    
    error_log('[docviewer pluginfile] Searching for - contextid: ' . $context->id . ', component: mod_docviewer, filearea: ' . $filearea . ', itemid: ' . $itemid . ', filepath: ' . $filepath . ', filename: ' . $filename);
    debugging('[docviewer pluginfile] Looking for file - contextid: ' . $context->id . ', component: mod_docviewer, filearea: ' . $filearea . ', itemid: ' . $itemid . ', filepath: ' . $filepath . ', filename: ' . $filename, DEBUG_DEVELOPER);
    
    $file = $fs->get_file($context->id, 'mod_docviewer', $filearea, $itemid, $filepath, $filename);
    
    if (!$file || $file->is_directory()) {
        error_log('[docviewer pluginfile] ERROR: File not found or is directory');
        debugging('[docviewer pluginfile] File not found or is directory', DEBUG_DEVELOPER);
        
        $all_files = $fs->get_area_files($context->id, 'mod_docviewer', $filearea, $itemid);
        error_log('[docviewer pluginfile] All files in area (' . count($all_files) . ' total):');
        debugging('[docviewer pluginfile] All files in area: ' . count($all_files), DEBUG_DEVELOPER);
        foreach ($all_files as $f) {
            if (!$f->is_directory()) {
                $debug_msg = 'itemid: ' . $f->get_itemid() . ', filepath: ' . $f->get_filepath() . ', filename: ' . $f->get_filename();
                error_log('[docviewer pluginfile]   - ' . $debug_msg);
                debugging('[docviewer pluginfile] Found file: ' . $debug_msg, DEBUG_DEVELOPER);
            }
        }
        error_log('[docviewer pluginfile] ========== END (FAILED) ==========');
        
        return false;
    }

    error_log('[docviewer pluginfile] SUCCESS: File found - ' . $file->get_filename());
    debugging('[docviewer pluginfile] File found successfully', DEBUG_DEVELOPER);
    
    // Get the docviewer instance to check download settings.
    $docviewer = $DB->get_record('docviewer', array('id' => $cm->instance), '*', MUST_EXIST);
    
    if (empty($docviewer->enabledownload)) {
        // Allow inline viewing (not forcing download)
        $forcedownload = false;
    }

    error_log('[docviewer pluginfile] ========== END (SUCCESS) ==========');
    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Convert document to PDF using Moodle's document converter
 *
 * @param stored_file $file The original file to convert
 * @param context $context The module context
 * @return stored_file|bool Converted file stored in our filearea or false on failure
 */
function docviewer_convert_to_pdf($file, $context) {
    global $USER;
    
    debugging('[docviewer] Starting conversion for file: ' . $file->get_filename() . ' in context: ' . $context->id, DEBUG_DEVELOPER);
    
    try {
        // Check if file mimetype is supported for conversion
        $mimetype = $file->get_mimetype();
        $supported_types = array(
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
        
        if (!in_array($mimetype, $supported_types)) {
            debugging('[docviewer] File type not supported for conversion: ' . $mimetype, DEBUG_DEVELOPER);
            return false;
        }
        
        debugging('[docviewer] Initializing converter for mimetype: ' . $mimetype, DEBUG_DEVELOPER);
        
        // Initialize the converter with correct namespace
        $converter = new \core_files\converter();
        
        // Start conversion - returns a conversion object, not a file
        $conversion = $converter->start_conversion($file, 'pdf');
        
        if (!$conversion) {
            debugging('Failed to start conversion - converter may not be configured', DEBUG_DEVELOPER);
            return false;
        }
        
        // Poll for conversion status
        $converter->poll_conversion($conversion);
        
        // Check conversion status
        $status = $conversion->get('status');
        
        if ($status == \core_files\conversion::STATUS_COMPLETE) {
            // Get the converted file from the conversion system
            $converted_file = $conversion->get_destfile();
            
            debugging('[docviewer] Conversion complete, destfile retrieved', DEBUG_DEVELOPER);
            
            if ($converted_file) {
                $fs = get_file_storage();
                
                // Prepare file record for our filearea
                $filerecord = array(
                    'contextid' => $context->id,
                    'component' => 'mod_docviewer',
                    'filearea' => 'converted',
                    'itemid' => 0,
                    'filepath' => '/',
                    'filename' => str_replace(['.' . pathinfo($file->get_filename(), PATHINFO_EXTENSION)], '.pdf', $file->get_filename()),
                    'userid' => $USER->id
                );
                
                debugging('[docviewer] Creating new file with record: ' . print_r($filerecord, true), DEBUG_DEVELOPER);
                
                // Check if a converted file already exists and delete it
                $existing = $fs->get_file(
                    $filerecord['contextid'],
                    $filerecord['component'],
                    $filerecord['filearea'],
                    $filerecord['itemid'],
                    $filerecord['filepath'],
                    $filerecord['filename']
                );
                
                if ($existing) {
                    $existing->delete();
                }
                
                // Create a new file in our filearea from the converted file
                $newfile = $fs->create_file_from_storedfile($filerecord, $converted_file);
                
                debugging('[docviewer] New file created successfully: ' . $newfile->get_filename(), DEBUG_DEVELOPER);
                
                return $newfile;
            }
        } else if ($status == \core_files\conversion::STATUS_FAILED) {
            debugging('[docviewer] File conversion failed', DEBUG_DEVELOPER);
            return false;
        } else {
            // Conversion is pending or in progress
            debugging('[docviewer] File conversion is still in progress (status: ' . $status . ')', DEBUG_DEVELOPER);
            return false;
        }
        
        return false;
    } catch (Exception $e) {
        debugging('[docviewer] Exception during conversion: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 */
function docviewer_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-docviewer-*'=>get_string('page-mod-docviewer-x', 'docviewer'));
    return $module_pagetype;
}

/**
 * Generate a summary of the file content
 *
 * @param stored_file $file
 * @return string|bool Summary text or false on failure
 */
function docviewer_generate_summary($file) {
    try {
        // Get file content
        $content = $file->get_content();
        
        // For PDF files, extract text (this is a basic implementation)
        $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
        
        if ($extension === 'pdf') {
            // Basic PDF text extraction - you can improve this with a PDF parser library
            // For now, return a placeholder
            return get_string('summaryplaceholder', 'docviewer');
        }
        
        // For text-based files, create a simple summary
        // This is a basic implementation - you should integrate with an AI service for production
        $text = '';
        if (in_array($extension, array('txt', 'md'))) {
            $text = $content;
        } else {
            // For other formats, you'd need appropriate parsers
            return get_string('summarizenotsupported', 'docviewer');
        }
        
        // Simple summarization: take first few sentences (basic implementation)
        $sentences = preg_split('/(?<=[.?!])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $summary = implode(' ', array_slice($sentences, 0, 3));
        
        return $summary;
    } catch (Exception $e) {
        debugging('Exception during summarization: ' . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }
}
