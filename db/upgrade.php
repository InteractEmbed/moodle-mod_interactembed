<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Upgrade steps for the InteractEmbed activity.
 *
 * @package   mod_interactembed
 * @copyright 2026 Michel Cardinal
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Performs InteractEmbed database upgrades.
 *
 * @param int $oldversion Installed version.
 * @return bool
 */
function xmldb_interactembed_upgrade(int $oldversion): bool {
    global $DB;

    if ($oldversion < 2026090101) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $fields = [
            new xmldb_field('entrypoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'index.html', 'introformat'),
            new xmldb_field('packageitemid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'entrypoint'),
            new xmldb_field('revision', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'packageitemid'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2026090101, 'interactembed');
    }

    if ($oldversion < 2026090102) {
        // This release adds the signed package reader and has no database schema changes.
        upgrade_mod_savepoint(true, 2026090102, 'interactembed');
    }

    if ($oldversion < 2026090103) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $field = new xmldb_field(
            'sourcetype',
            XMLDB_TYPE_CHAR,
            '20',
            null,
            XMLDB_NOTNULL,
            null,
            'package',
            'introformat'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2026090103, 'interactembed');
    }

    if ($oldversion < 2026090104) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $field = new xmldb_field(
            'sourceurl',
            XMLDB_TYPE_TEXT,
            null,
            null,
            null,
            null,
            null,
            'sourcetype'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2026090104, 'interactembed');
    }

    if ($oldversion < 2026090105) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $fields = [
            new xmldb_field('coveralt', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '', 'revision'),
            new xmldb_field('aspectratio', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, '16x9', 'coveralt'),
            new xmldb_field('customheight', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '600', 'aspectratio'),
            new xmldb_field('displaymode', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'inline', 'customheight'),
            new xmldb_field('loadonrequest', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'displaymode'),
            new xmldb_field('showfullscreen', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'loadonrequest'),
            new xmldb_field('lazyload', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'showfullscreen'),
            new xmldb_field(
                'sandboxprofile',
                XMLDB_TYPE_CHAR,
                '20',
                null,
                XMLDB_NOTNULL,
                null,
                'standard',
                'lazyload'
            ),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2026090105, 'interactembed');
    }

    if ($oldversion < 2026090106) {
        // This release adds administration settings and has no database schema changes.
        upgrade_mod_savepoint(true, 2026090106, 'interactembed');
    }

    if ($oldversion < 2026090107) {
        // This release adds activity backup and restore support without schema changes.
        upgrade_mod_savepoint(true, 2026090107, 'interactembed');
    }

    if ($oldversion < 2026090108) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $fields = [
            new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0', 'sandboxprofile'),
            new xmldb_field('completioncontent', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'grade'),
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        $progress = new xmldb_table('interactembed_progress');
        if (!$dbman->table_exists($progress)) {
            $progress->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $progress->add_field('interactembedid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $progress->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $progress->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'inprogress');
            $progress->add_field('score', XMLDB_TYPE_NUMBER, '10, 5');
            $progress->add_field('scoremax', XMLDB_TYPE_NUMBER, '10, 5');
            $progress->add_field('firstinteraction', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $progress->add_field('lastinteraction', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $progress->add_field('timecompleted', XMLDB_TYPE_INTEGER, '10');
            $progress->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $progress->add_key('activity', XMLDB_KEY_FOREIGN, ['interactembedid'], 'interactembed', ['id']);
            $progress->add_key('user', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);
            $progress->add_index('activityuser', XMLDB_INDEX_UNIQUE, ['interactembedid', 'userid']);
            $dbman->create_table($progress);
        }
        upgrade_mod_savepoint(true, 2026090108, 'interactembed');
    }

    if ($oldversion < 2026090109) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $field = new xmldb_field(
            'showembedcode',
            XMLDB_TYPE_INTEGER,
            '1',
            null,
            XMLDB_NOTNULL,
            null,
            '0',
            'completioncontent'
        );
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_mod_savepoint(true, 2026090109, 'interactembed');
    }

    if ($oldversion < 2026090110) {
        // This release aligns privacy declarations and release engineering without schema changes.
        upgrade_mod_savepoint(true, 2026090110, 'interactembed');
    }

    if ($oldversion < 2026090111) {
        // Adds the optional InteractEmbed Library import bridge without schema changes.
        upgrade_mod_savepoint(true, 2026090111, 'interactembed');
    }

    if ($oldversion < 2026090112) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('interactembed');
        $field = new xmldb_field('coveralt', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'revision');
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }
        upgrade_mod_savepoint(true, 2026090112, 'interactembed');
    }

    if ($oldversion < 2026090113) {
        // Strengthens package validation and Creator snapshot integrity without schema changes.
        upgrade_mod_savepoint(true, 2026090113, 'interactembed');
    }

    if ($oldversion < 2026090201) {
        // Aligns accessible full-screen focus behaviour with the companion block without schema changes.
        upgrade_mod_savepoint(true, 2026090201, 'interactembed');
    }

    if ($oldversion < 2026090700) {
        // Records pre-1.0 compatibility and quality corrections without schema changes.
        upgrade_mod_savepoint(true, 2026090700, 'interactembed');
    }

    if ($oldversion < 2026090701) {
        // Marks the validated 1.0 stable release without database schema changes.
        upgrade_mod_savepoint(true, 2026090701, 'interactembed');
    }

    return true;
}
