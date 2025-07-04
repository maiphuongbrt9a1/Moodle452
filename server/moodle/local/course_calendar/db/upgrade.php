<?php

function xmldb_local_course_calendar_upgrade($oldversion): bool {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 20250606010) {

        // Define table local_course_calendar_holiday to be created.
        $table = new xmldb_table('local_course_calendar_holiday');

        // Adding fields to table local_course_calendar_holiday.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('created_user_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modified_user_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('createdtime', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modifiedtime', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('holiday', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_course_calendar_holiday.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('created_user_fk', XMLDB_KEY_FOREIGN, ['created_user_id'], 'user', ['id']);
        $table->add_key('modified_user_fk', XMLDB_KEY_FOREIGN, ['modified_user_id'], 'user', ['id']);

        // Adding indexes to table local_course_calendar_holiday.
        $table->add_index('holiday_idx', XMLDB_INDEX_UNIQUE, ['holiday']);

        // Conditionally launch create table for local_course_calendar_holiday.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
    }

    // Course_calendar savepoint reached.
    upgrade_plugin_savepoint(true, 20250606010, 'local', 'course_calendar');
    }

    // Everything has succeeded to here. Return true.
    return true;
}