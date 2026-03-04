<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * List of all docviewer instances in course
 *
 * @package    mod_docviewer
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/docviewer/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('modulenameplural', 'docviewer'));

echo $OUTPUT->header();

if (!$docviewers = get_all_instances_in_course('docviewer', $course)) {
    notice(get_string('nodocviewers', 'docviewer'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

$table->head = array(
    get_string('name'),
    get_string('intro', 'docviewer')
);

foreach ($docviewers as $docviewer) {
    $link = html_writer::link(
        new moodle_url('/mod/docviewer/view.php', array('id' => $docviewer->coursemodule)),
        format_string($docviewer->name)
    );

    $table->data[] = array($link, format_module_intro('docviewer', $docviewer, $docviewer->coursemodule));
}

echo html_writer::table($table);
echo $OUTPUT->footer();
