<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Prints a particular instance of fileca
 *
 * @package    mod_fileca
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course module ID.

if ($id) {
    $cm         = get_coursemodule_from_id('fileca', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $fileca     = $DB->get_record('fileca', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('missingparameter');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/fileca:view', $context);

// Completion and log.
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Print the page header.
$PAGE->set_url('/mod/fileca/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($fileca->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Get the file.
$fs = get_file_storage();
$files = $fs->get_area_files($context->id, 'mod_fileca', 'content', 0, 'sortorder DESC, id ASC', false);

debugging('[fileca view] Looking for files in context: ' . $context->id . ', component: mod_fileca, filearea: content, itemid: 0', DEBUG_DEVELOPER);
debugging('[fileca view] Found ' . count($files) . ' files', DEBUG_DEVELOPER);

if (empty($files)) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(format_string($fileca->name));
    echo $OUTPUT->box(get_string('nofile', 'fileca'));
    echo $OUTPUT->footer();
    exit;
}

$file = reset($files);
$filename = $file->get_filename();
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

$convertible_extensions = array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
$needs_conversion = in_array($extension, $convertible_extensions);

$filearea = 'content';
$ispdf = ($extension === 'pdf');

$displayfile = $file; // Start with original file
if ($extension !== 'pdf' && in_array($extension, array('doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'))) {
    debugging('[fileca view] File needs conversion from ' . $extension . ' to PDF', DEBUG_DEVELOPER);
    
    // Check if already converted
    $convertedfilename = pathinfo($filename, PATHINFO_FILENAME) . '.pdf';
    $converted = $fs->get_file($context->id, 'mod_fileca', 'converted', 0, '/', $convertedfilename);
    
    if ($converted && !$converted->is_directory()) {
        debugging('[fileca view] Found existing converted PDF: ' . $convertedfilename, DEBUG_DEVELOPER);
        $displayfile = $converted; // Use the converted file object
        $filename = $convertedfilename;
        $filearea = 'converted';
        $ispdf = true;
    } else {
        debugging('[fileca view] No existing conversion, attempting conversion', DEBUG_DEVELOPER);
        // Attempt conversion
        $convertedfile = fileca_convert_to_pdf($file, $context);
        if ($convertedfile) {
            debugging('[fileca view] Conversion successful', DEBUG_DEVELOPER);
            $displayfile = $convertedfile; // Use the newly converted file object
            $filename = $convertedfile->get_filename();
            $filearea = 'converted';
            $ispdf = true;
        } else {
            debugging('[fileca view] Conversion failed or in progress, showing original file', DEBUG_DEVELOPER);
            // Conversion failed or in progress, show download link instead
            $ispdf = false;
        }
    }
}

debugging('[fileca view] Final file to display: ' . $filename . ', filearea: ' . $filearea . ', ispdf: ' . ($ispdf ? 'yes' : 'no'), DEBUG_DEVELOPER);

error_log('[fileca] About to generate URL for file: ' . $filename . ' in filearea: ' . $filearea);
error_log('[fileca] Display file path: ' . $displayfile->get_filepath());
error_log('[fileca] Display file name: ' . $displayfile->get_filename());

// Output starts here.
echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($fileca->name));

if (!empty($fileca->intro)) {
    echo $OUTPUT->box(format_module_intro('fileca', $fileca, $cm->id), 'generalbox mod_introbox', 'filecaintro');
}

$fileurl = moodle_url::make_pluginfile_url(
    $context->id,
    'mod_fileca',
    $filearea,
    0,
    $displayfile->get_filepath(),
    $displayfile->get_filename()
);

error_log('[fileca] Generated URL: ' . $fileurl->out(false));

debugging('[fileca view] Generated file URL: ' . $fileurl->out(false), DEBUG_DEVELOPER);

if ($ispdf) {
    echo '<div class="pdf-viewer-container">';
    
    // Build protection classes
    $copyclass = empty($fileca->enablecopying) ? 'no-copy' : '';
    // If download is disabled, also disable printing regardless of print setting
    $printclass = (empty($fileca->enabledownload) || empty($fileca->enableprinting)) ? 'no-print' : '';
    $classes = trim($copyclass . ' ' . $printclass);
    
    // Build iframe URL with parameters to hide toolbar buttons when download is disabled
    $iframeurl = $fileurl->out(false);
    if (empty($fileca->enabledownload)) {
        // Add parameters to hide download/print buttons in PDF viewer
        $iframeurl .= '#toolbar=0&navpanes=0&scrollbar=1';
    }
    
    echo '<div class="pdf-wrapper ' . $classes . '" id="pdf-viewer-wrapper">';
    echo '<!-- PDF URL: ' . $fileurl->out(false) . ' -->';
    echo '<!-- File component: mod_fileca, area: content -->';
    echo '<iframe src="' . $iframeurl . '" width="100%" height="800px" style="border: 1px solid #ccc;" id="pdf-iframe"></iframe>';
    echo '</div>';
    
    // Download button if enabled
    if (!empty($fileca->enabledownload)) {
        echo '<div class="pdf-download" style="margin-top: 10px;">';
        echo '<a href="'.$fileurl->out(true).'" class="btn btn-primary" download>'.get_string('download', 'fileca').'</a>';
        echo '</div>';
    }
    
    // Summarize button if enabled
    if (!empty($fileca->enablesummarize)) {
        echo '<div class="fileca-summarize" style="margin-top: 20px;">';
        echo '<button id="summarize-btn" class="btn btn-secondary">'.get_string('summarize', 'fileca').'</button>';
        echo '<div id="summary-result" style="display:none; margin-top: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;"></div>';
        echo '</div>';
        
        // Add summarize JavaScript
        echo '<script>
        document.getElementById("summarize-btn").addEventListener("click", function() {
            var btn = this;
            var resultDiv = document.getElementById("summary-result");
            
            btn.disabled = true;
            btn.textContent = "' . get_string('summarizing', 'fileca') . '...";
            
            fetch("' . new moodle_url('/mod/fileca/summarize.php') . '", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    sesskey: "' . sesskey() . '",
                    contextid: ' . $context->id . ',
                    filecaid: ' . $fileca->id . '
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = "<h4>' . get_string('summary', 'fileca') . '</h4>" + data.summary;
                    resultDiv.style.display = "block";
                } else {
                    resultDiv.innerHTML = "<div class=\"alert alert-danger\">" + data.error + "</div>";
                    resultDiv.style.display = "block";
                }
                btn.disabled = false;
                btn.textContent = "' . get_string('summarize', 'fileca') . '";
            })
            .catch(error => {
                resultDiv.innerHTML = "<div class=\"alert alert-danger\">' . get_string('summarizeerror', 'fileca') . '</div>";
                resultDiv.style.display = "block";
                btn.disabled = false;
                btn.textContent = "' . get_string('summarize', 'fileca') . '";
            });
        });
        </script>';
    }
    
    echo '</div>';
    
    echo '<style>';
    
    // Copy protection - prevents text selection but keeps buttons clickable
    if (empty($fileca->enablecopying)) {
        echo '
        .no-copy {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        /* Allow buttons and links to remain clickable */
        .no-copy button,
        .no-copy a,
        .no-copy input,
        .no-copy select {
            pointer-events: auto !important;
            -webkit-user-select: auto;
            -moz-user-select: auto;
            -ms-user-select: auto;
            user-select: auto;
        }';
    }
    
    // Print protection
    if (empty($fileca->enabledownload) || empty($fileca->enableprinting)) {
        echo '
        @media print {
            .no-print,
            .no-print * {
                display: none !important;
            }
        }';
    }
    
    echo '</style>';
    
    if (empty($fileca->enabledownload)) {
        echo '<script>
        (function() {
            // Prevent keyboard shortcuts for download/save
            document.addEventListener("keydown", function(e) {
                // Prevent Ctrl+S (Save)
                if ((e.ctrlKey || e.metaKey) && e.key === "s") {
                    e.preventDefault();
                    return false;
                }
                // Prevent Ctrl+P (Print) if download is disabled
                if ((e.ctrlKey || e.metaKey) && e.key === "p") {
                    e.preventDefault();
                    return false;
                }
            }, true);
            
            // Prevent right-click context menu on PDF viewer
            var pdfWrapper = document.getElementById("pdf-viewer-wrapper");
            if (pdfWrapper) {
                pdfWrapper.addEventListener("contextmenu", function(e) {
                    e.preventDefault();
                    return false;
                });
            }
            
            // Prevent print via window.print()
            var originalPrint = window.print;
            window.print = function() {
                console.log("Printing is disabled for this document");
                return false;
            };
        })();
        </script>';
    }
    
    if (empty($fileca->enablecopying)) {
        echo '<script>
        (function() {
            var pdfWrapper = document.getElementById("pdf-viewer-wrapper");
            if (pdfWrapper) {
                // Prevent copy event
                pdfWrapper.addEventListener("copy", function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Prevent cut event
                pdfWrapper.addEventListener("cut", function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Prevent text selection via mouse
                pdfWrapper.addEventListener("selectstart", function(e) {
                    e.preventDefault();
                    return false;
                });
            }
        })();
        </script>';
    }

} else {
    // For other file types, just provide download link if enabled.
    if (!empty($fileca->enabledownload)) {
        echo '<a href="'.$fileurl->out(true).'" class="btn btn-primary" download>'.get_string('download', 'fileca').'</a>';
    } else {
        echo $OUTPUT->notification(get_string('downloadnotenabled', 'fileca'), 'info');
    }
}

echo '</div>';

echo $OUTPUT->footer();
?>
