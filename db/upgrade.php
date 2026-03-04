<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Upgrade code for mod_docviewer
 *
 * @package    mod_docviewer
 * @copyright  2025 CentricApp LTD
 * @author     Dev Team <dev@centricapp.co.il>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_docviewer_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Migrate from legacy mod_fileca: if table fileca exists, rename to docviewer.
    if ($oldversion < 2025010202) {
        $tablefileca = new xmldb_table('fileca');
        $tabledocviewer = new xmldb_table('docviewer');
        if ($dbman->table_exists($tablefileca)) {
            if ($dbman->table_exists($tabledocviewer)) {
                $dbman->drop_table($tabledocviewer);
            }
            $dbman->rename_table($tablefileca, 'docviewer');
        }
        upgrade_mod_savepoint(true, 2025010202, 'docviewer');
    }

    if ($oldversion < 2025010203) {
        // Define field enableprinting to be added to docviewer.
        $table = new xmldb_table('docviewer');
        $field = new xmldb_field('enableprinting', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'enablecopying');

        // Conditionally launch add field enableprinting.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2025010203, 'docviewer');
    }

    return true;
}
