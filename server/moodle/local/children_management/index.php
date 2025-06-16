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
 * TODO describe file index
 *
 * @package    local_children_management
 * @copyright  2025 Võ Mai Phương <vomaiphuonghhvt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/children_management/lib.php');
require_once($CFG->dirroot . '/local/dlog/lib.php');

try {
    require_login();

    $url = new moodle_url('/local/children_management/index.php', []);
    $PAGE->set_url($url);
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title(get_string('children_management_title', 'local_children_management'));
    $PAGE->set_heading(get_string('children_management_heading', 'local_children_management'));
    echo $OUTPUT->header();

    $parentid = $USER->id;
    $sql = "SELECT children.childrenid,
                    children.parentid,
                    user.firstname,
                    user.lastname,
                    user.email,
                    user.phone1        
            FROM {children_and_parent_information} as children
            JOIN {user} as user on user.id = children.childrenid
            WHERE children.parentid = :parentid";
    $students = $DB->get_records_sql($sql, ['parentid' => $parentid]);

    if (!$students) {
        echo $OUTPUT->notification(get_string('no_children_found', 'local_children_management'), 'info');
    } else {
        echo html_writer::start_tag('div', ['class' => 'children-list']);
        echo html_writer::tag('h3', get_string('children_list', 'local_children_management'));

        $table = new html_table();
        $table->head = [
            get_string('id', 'local_children_management'),
            get_string('fullname', 'local_children_management'),
            get_string('email', 'local_children_management'),
            get_string('phone1', 'local_children_management'),
            get_string('registed_course_number', 'local_children_management'),
            get_string('finished_course_number', 'local_children_management'),
            get_string('actions', 'local_children_management'),
        ];
        $table->align = ['center', 'left', 'left', 'left', 'left' , 'left', 'center'];
        foreach ($students as $student) {
            $profileurl = new moodle_url('/user/profile.php', ['id' => $student->childrenid]);
            // You might want to add a link to student's course overview etc.
            $actions = html_writer::link($profileurl, get_string('view_profile', 'local_children_management'));
            // Add more links here, e.g., link to student's courses (more complex)

            $table->data[] = [
                $student->childrenid,
                format_string($student->firstname) . format_string($student->lastname),
                format_string($student->email),
                format_string($student->phone1),
                "count of registered courses",
                "count of finished course",
                $actions,
            ];
        }
        echo html_writer::table($table);
        echo html_writer::end_tag('div');
    }

    // Add a button to add a new child.
    $addchildurl = new moodle_url('/local/children_management/add_child.php');
    echo $OUTPUT->single_button($addchildurl, get_string('add_child', 'local_children_management'), 'get', ['class' => 'btn btn-primary mt-3']);

    echo $OUTPUT->footer();
} catch (Exception $e) {
    dlog($e->getTrace());
    throw new \moodle_exception('error', 'local_children_management', '', null, $e->getMessage());
}
