<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Upgrade code for mod_fileca
 *
 * @package    mod_fileca
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_fileca_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025010201) {
        // Define field enableprinting to be added to fileca.
        $table = new xmldb_table('fileca');
        $field = new xmldb_field('enableprinting', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'enablecopying');

        // Conditionally launch add field enableprinting.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Fileca savepoint reached.
        upgrade_mod_savepoint(true, 2025010201, 'fileca');
    }

    return true;
}
