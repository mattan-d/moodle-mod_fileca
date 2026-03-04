<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * The main fileca configuration form
 *
 * @package    mod_fileca
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_fileca_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;

        $mform = $this->_form;

        // General section.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // File upload.
        $filemanager_options = array();
        $filemanager_options['accepted_types'] = '*';
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = 1;
        $filemanager_options['subdirs'] = 0;

        $mform->addElement('filemanager', 'files', get_string('selectfiles', 'fileca'), null, $filemanager_options);
        $mform->addRule('files', null, 'required', null, 'client');
        $mform->addHelpButton('files', 'selectfiles', 'fileca');

        // Behaviour section.
        $mform->addElement('header', 'behaviour', get_string('behaviour', 'fileca'));

        // Enable file download.
        $mform->addElement('advcheckbox', 'enabledownload', get_string('enabledownload', 'fileca'));
        $mform->addHelpButton('enabledownload', 'enabledownload', 'fileca');
        $mform->setDefault('enabledownload', 0);

        // Enable printing.
        $mform->addElement('advcheckbox', 'enableprinting', get_string('enableprinting', 'fileca'));
        $mform->addHelpButton('enableprinting', 'enableprinting', 'fileca');
        $mform->setDefault('enableprinting', 1);

        // Enable copying.
        $mform->addElement('advcheckbox', 'enablecopying', get_string('enablecopying', 'fileca'));
        $mform->addHelpButton('enablecopying', 'enablecopying', 'fileca');
        $mform->setDefault('enablecopying', 1);

        // Enable summarize.
        $mform->addElement('advcheckbox', 'enablesummarize', get_string('enablesummarize', 'fileca'));
        $mform->addHelpButton('enablesummarize', 'enablesummarize', 'fileca');
        $mform->setDefault('enablesummarize', 1);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('files');
            file_prepare_draft_area($draftitemid, $this->context->id, 'mod_fileca', 'content', 0,
                                    array('subdirs' => 0, 'maxfiles' => 1));
            $default_values['files'] = $draftitemid;
        }
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
