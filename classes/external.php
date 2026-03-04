<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * External API for mod_fileca
 *
 * @package    mod_fileca
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_fileca;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

class external extends external_api {

    /**
     * Returns description of generate_summary parameters
     */
    public static function generate_summary_parameters() {
        return new external_function_parameters(
            array(
                'contextid' => new external_value(PARAM_INT, 'Context ID'),
                'filecaid' => new external_value(PARAM_INT, 'File CA ID'),
                'content' => new external_value(PARAM_RAW, 'Content to summarize')
            )
        );
    }

    /**
     * Generate summary of content
     */
    public static function generate_summary($contextid, $filecaid, $content) {
        global $DB, $USER;

        $params = self::validate_parameters(self::generate_summary_parameters(), array(
            'contextid' => $contextid,
            'filecaid' => $filecaid,
            'content' => $content
        ));

        $context = \context::instance_by_id($params['contextid']);
        self::validate_context($context);
        require_capability('mod/fileca:view', $context);

        // Get the fileca instance
        $fileca = $DB->get_record('fileca', array('id' => $params['filecaid']), '*', MUST_EXIST);
        
        if (empty($fileca->enablesummarize)) {
            return array(
                'success' => false,
                'summary' => '',
                'error' => 'Summarize feature is not enabled'
            );
        }

        // Generate summary using a simple algorithm
        // In production, you would integrate with an AI service like OpenAI, etc.
        $summary = self::generate_simple_summary($params['content']);

        return array(
            'success' => true,
            'summary' => $summary,
            'error' => ''
        );
    }

    /**
     * Simple summary generation (placeholder for AI integration)
     */
    private static function generate_simple_summary($content) {
        // This is a simple placeholder. In production, integrate with AI services.
        $sentences = preg_split('/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        $sentences = array_map('trim', $sentences);
        
        // Take first 5 sentences as a basic summary
        $summarysentences = array_slice($sentences, 0, 5);
        $summary = implode('. ', $summarysentences);
        
        if (!empty($summary) && substr($summary, -1) !== '.') {
            $summary .= '.';
        }

        if (empty($summary)) {
            $summary = 'Unable to generate summary. The content may be too short or in an unsupported format.';
        }

        return $summary;
    }

    /**
     * Returns description of generate_summary return value
     */
    public static function generate_summary_returns() {
        return new external_single_structure(
            array(
                'success' => new external_value(PARAM_BOOL, 'Success status'),
                'summary' => new external_value(PARAM_RAW, 'Generated summary'),
                'error' => new external_value(PARAM_TEXT, 'Error message if any')
            )
        );
    }
}
