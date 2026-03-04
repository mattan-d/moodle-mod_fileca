<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * List of all fileca instances in course
 *
 * @package    mod_fileca
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

require_course_login($course);

$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/fileca/index.php', array('id' => $id));
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->navbar->add(get_string('modulenameplural', 'fileca'));

echo $OUTPUT->header();

if (!$filecas = get_all_instances_in_course('fileca', $course)) {
    notice(get_string('nofilecas', 'fileca'), new moodle_url('/course/view.php', array('id' => $course->id)));
}

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

$table->head = array(
    get_string('name'),
    get_string('intro', 'fileca')
);

foreach ($filecas as $fileca) {
    $link = html_writer::link(
        new moodle_url('/mod/fileca/view.php', array('id' => $fileca->coursemodule)),
        format_string($fileca->name)
    );

    $table->data[] = array($link, format_module_intro('fileca', $fileca, $fileca->coursemodule));
}

echo html_writer::table($table);
echo $OUTPUT->footer();
