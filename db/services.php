<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Web service definitions for mod_fileca
 *
 * @package    mod_fileca
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'mod_fileca_generate_summary' => array(
        'classname'   => 'mod_fileca\external',
        'methodname'  => 'generate_summary',
        'classpath'   => '',
        'description' => 'Generate summary of file content',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'mod/fileca:view',
        'loginrequired' => true,
    ),
);
