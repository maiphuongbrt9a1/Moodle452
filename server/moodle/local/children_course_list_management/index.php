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
 * @package    local_children_course_list_management
 * @copyright  2025 Võ Mai Phương <vomaiphuonghhvt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/children_course_list_management/lib.php');
require_once($CFG->dirroot . '/local/dlog/lib.php');
require_once($CFG->libdir . '/gradelib.php');
require_once($CFG->dirroot . '/grade/querylib.php');
use local_children_course_list_management\helper as ChildrenCourseListHelper;
try {
    require_login();
    require_capability('local/children_course_list_management:view', context_system::instance());
    $PAGE->set_url(new moodle_url('/local/children_course_list_management/index.php', []));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('report');
    $PAGE->set_title(get_string('children_course_list_management_title', 'local_children_course_list_management'));
    $PAGE->set_heading(get_string('children_course_list_management_heading', 'local_children_course_list_management'));
    $PAGE->requires->css('/local/children_course_list_management/style/style.css');
    echo $OUTPUT->header();


    // --- Start code to render Search Input ---

    $search_context = new stdClass();
    $search_context->method = 'get'; // Method for the search form
    $search_context->action = $PAGE->url; // Action URL for the search form
    $search_context->inputname = 'searchquery';
    $search_context->searchstring = get_string('searchitems', 'local_children_course_list_management'); // Placeholder text for the search input

    $search_query = optional_param('searchquery', '', PARAM_TEXT); // Get the search query from the URL parameters.

    $search_context->value = $search_query; // Set the value of the search input to the current search query.
    $search_context->extraclasses = 'my-2'; // Additional CSS classes for styling
    $search_context->btnclass = 'btn-primary';

    // Renderer for template core
    $core_renderer = $PAGE->get_renderer('core');

    // Render search input
    echo $core_renderer->render_from_template('core/search_input', $search_context);

    // --- End code to render Search Input ---

    // Set default variable.
    $parentid = $USER->id;
    $stt = 0;
    $students = [];

    $per_page = optional_param('perpage', 20, PARAM_INT);
    $current_page = optional_param('page', 0, PARAM_INT);
    $total_records = 0;
    $offset = $current_page * $per_page;
    $params = [];

    // Khởi tạo dữ liệu và xử lý cho việc sắp xếp dữ liệu trong các cột dữ liệu
    $valid_sort_columns = [
        'course_name',
        'children_firstname',
        'children_lastname',
        'course_total_days',
        'course_study_days',
        'average_score'
    ];

    $sort_directions = ['asc', 'desc'];

    $sort = optional_param('sort', 'course_name', PARAM_ALPHANUMEXT);
    $direction = optional_param('direction', 'asc', PARAM_ALPHA);

    if (!in_array($sort, $valid_sort_columns)) {
        $sort = 'course_name';
    }

    if (!in_array($direction, $sort_directions)) {
        $direction = 'asc';
    }

    // Get all children of current parent account.
    if (empty($search_query)) {
        $params = ['parentid' => $parentid];

        $total_count_sql = "SELECT COUNT(*)
                            FROM {children_and_parent_information} children
                            JOIN {user} user on user.id = children.childrenid
                            join {role_assignments} ra on ra.userid = children.childrenid
                            join {role} r on r.id = ra.roleid
                            join {context} ctx on ctx.id = ra.contextid
                            join {course} c on c.id = ctx.instanceid 
                            WHERE children.parentid = :parentid 
                                and r.shortname = 'student' 
                                and ctx.contextlevel = 50";
        $total_records = $DB->count_records_sql($total_count_sql, $params);

        $sql = "SELECT CONCAT(children.childrenid, children.parentid, c.id) id,
                        children.childrenid,
                        children.parentid,
                        user.firstname children_firstname,
                        user.lastname children_lastname,
                        r.shortname role_name,
                        c.id courseid,
                        c.fullname course_name,
                        c.startdate course_start_date,
                        c.enddate course_end_date,            
                        CEIL((c.enddate - c.startdate) / 86400) AS course_total_days,
                        CASE
                            WHEN c.startdate <= UNIX_TIMESTAMP() AND UNIX_TIMESTAMP() <= c.enddate 
                                THEN ceil((UNIX_TIMESTAMP() - c.startdate) / 86400)
                            WHEN UNIX_TIMESTAMP() > c.enddate 
                                THEN ceil((c.enddate - c.startdate) / 86400)
                            ELSE 0
                        END AS course_study_days
                FROM {children_and_parent_information} children
                JOIN {user} user on user.id = children.childrenid
                join {role_assignments} ra on ra.userid = children.childrenid
                join {role} r on r.id = ra.roleid
                join {context} ctx on ctx.id = ra.contextid
                join {course} c on c.id = ctx.instanceid 
                WHERE children.parentid = :parentid 
                    and r.shortname = 'student' 
                    and ctx.contextlevel = 50
                ORDER BY {$sort} {$direction}";
        $students = $DB->get_records_sql($sql, $params, $offset, $per_page);
    }

    // if parent use search input, we need to filter the children list.
    if (!empty($search_query)) {

        // Escape the search query to prevent SQL injection.
        $search_query = trim($search_query);
        $search_query = '%' . $DB->sql_like_escape($search_query) . '%';
        $params = [
            'parentid' => $parentid,
            'searchparamid' => $search_query,
            'searchparamusername' => $search_query,
            'searchparamfirstname' => $search_query,
            'searchparamlastname' => $search_query,
            'searchparamemail' => $search_query,
            'searchparamcoursename' => $search_query
        ];

        $total_count_sql = "SELECT COUNT(*)
                            FROM {children_and_parent_information} children
                            JOIN {user} user on user.id = children.childrenid
                            join {role_assignments} ra on ra.userid = children.childrenid
                            join {role} r on r.id = ra.roleid
                            join {context} ctx on ctx.id = ra.contextid
                            join {course} c on c.id = ctx.instanceid 
                            WHERE children.parentid = :parentid 
                                and r.shortname = 'student' 
                                and ctx.contextlevel = 50
                                and 
                                    (
                                        children.childrenid like :searchparamid 
                                        or user.username like :searchparamusername
                                        or user.firstname like :searchparamfirstname
                                        or user.lastname like :searchparamlastname
                                        or user.email like :searchparamemail
                                        or c.fullname like :searchparamcoursename
                                        
                                    )";

        $total_records = $DB->count_records_sql($total_count_sql, $params);
        // Process the search query.
        $sql = "SELECT CONCAT(children.childrenid, children.parentid, c.id) id,
                        children.childrenid,
                        children.parentid,
                        user.firstname children_firstname,
                        user.lastname children_lastname,
                        r.shortname role_name,
                        c.id courseid,
                        c.fullname course_name,
                        c.startdate course_start_date,
                        c.enddate course_end_date,            
                        CEIL((c.enddate - c.startdate) / 86400) AS course_total_days,
                        CASE
                            WHEN c.startdate <= UNIX_TIMESTAMP() AND UNIX_TIMESTAMP() <= c.enddate 
                                THEN ceil((UNIX_TIMESTAMP() - c.startdate) / 86400)
                            WHEN UNIX_TIMESTAMP() > c.enddate 
                                THEN ceil((c.enddate - c.startdate) / 86400)
                            ELSE 0
                        END AS course_study_days
                FROM {children_and_parent_information} children
                JOIN {user} user on user.id = children.childrenid
                join {role_assignments} ra on ra.userid = children.childrenid
                join {role} r on r.id = ra.roleid
                join {context} ctx on ctx.id = ra.contextid
                join {course} c on c.id = ctx.instanceid 
                WHERE children.parentid = :parentid 
                        and r.shortname = 'student' 
                        and ctx.contextlevel = 50 
                        and 
                    (
                        children.childrenid like :searchparamid 
                        or user.username like :searchparamusername
                        or user.firstname like :searchparamfirstname
                        or user.lastname like :searchparamlastname
                        or user.email like :searchparamemail
                        or c.fullname like :searchparamcoursename
                    )
            ORDER BY {$sort} {$direction}";

        $students = $DB->get_records_sql($sql, $params, $offset, $per_page);
    }

    // Display children list of parent on screen.
    if (!$students) {
        echo $OUTPUT->notification(get_string('no_children_found', 'local_children_course_list_management'), 'info');
    } else {
        // If there are children, display them in a table.
        // and parent does not need to search for children.
        echo html_writer::start_tag('div');

        $base_url = new moodle_url('/local/children_course_list_management/index.php', []);
        if (!empty($search_query)) {
            $base_url->param('searchquery', $search_query);
        }

        // Display the list of children in a table.
        $table = new html_table();
        $table->head = [
            get_string('stt', 'local_children_course_list_management'),
            ChildrenCourseListHelper::make_sort_table_header_helper(
                $PAGE,
                'course_name',
                get_string('course_full_name', 'local_children_course_list_management'),
                $sort,
                $direction
            ),
            ChildrenCourseListHelper::make_sort_table_header_helper(
                $PAGE,
                'children_lastname',
                get_string('student_fullname', 'local_children_course_list_management'),
                $sort,
                $direction
            ),
            get_string('teacher_fullname', 'local_children_course_list_management'),
            ChildrenCourseListHelper::make_sort_table_header_helper(
                $PAGE,
                'course_study_days',
                get_string('study_time', 'local_children_course_list_management'),
                $sort,
                $direction
            ),

            ChildrenCourseListHelper::make_sort_table_header_helper(
                $PAGE,
                'course_total_days',
                get_string('course_total_time', 'local_children_course_list_management'),
                $sort,
                $direction
            ),

            get_string('average_score', 'local_children_course_list_management'),
            get_string('actions', 'local_children_course_list_management'),
        ];
        $table->align = ['center', 'left', 'left', 'left', 'left', 'left', 'center'];
        foreach ($students as $student) {
            // add no. for the table.
            $stt = $stt + 1;

            // You might want to add a link to student's profile overview and course detail.
            $course_detail_url = new moodle_url('/course/view.php', ['id' => $student->courseid]);
            $student_profile_url = new moodle_url('/user/profile.php', ['id' => $student->childrenid]);

            $view_course_detail_action = $OUTPUT->action_icon(
                $course_detail_url,
                new pix_icon('i/hide', get_string('view_course_detail', 'local_children_course_list_management'))
            );

            // add sql to query teachers of this student in current course
            $teachers = [];
            $teachers_fullname = [];

            // search teachers by name.
            // If search query is empty, we will get all teachers of this student in current course.
            if (empty($search_query)) {

                $sql = "SELECT  CONCAT(teacher.id, role.id, course.id) id ,
                                teacher.id teacherid , 
                                teacher.firstname teacher_firstname, 
                                teacher.lastname teacher_lastname,  
                                role.id teacher_role_id, 
                                role.shortname role_shortname,
                                course.id courseid, 
                                course.fullname course_fullname
                          from {user} teacher
                          join {role_assignments} ra on ra.userid = teacher.id
                          join {role} role on role.id = ra.roleid
                          join {context} context on context.id = ra.contextid
                          join {course} course on course.id = context.instanceid
                          where (role.shortname = 'teacher' or role.shortname = 'editingteacher')
                                 and context.contextlevel = 50 
                                 and course.id = :student_course_id";
                $params = ['student_course_id' => $student->courseid];
            } else {
                $sql = "SELECT  CONCAT(teacher.id, role.id, course.id) id ,
                                teacher.id teacherid , 
                                teacher.firstname teacher_firstname, 
                                teacher.lastname teacher_lastname,  
                                role.id teacher_role_id, 
                                role.shortname role_shortname,
                                course.id courseid, 
                                course.fullname course_fullname
                          from {user} teacher
                          join {role_assignments} ra on ra.userid = teacher.id
                          join {role} role on role.id = ra.roleid
                          join {context} context on context.id = ra.contextid
                          join {course} course on course.id = context.instanceid
                          where (role.shortname = 'teacher' or role.shortname = 'editingteacher')
                                 and context.contextlevel = 50 
                                 and course.id = :student_course_id
                                 and (teacher.firstname like :searchparamteachername or teacher.lastname like :searchparamteachernameth2)";
                $params = [
                    'student_course_id' => $student->courseid,
                    'searchparamteachername' => $search_query,
                    'searchparamteachernameth2' => $search_query
                ];
            }
            // Get all teachers of this student in current course.
            $teachers = $DB->get_records_sql($sql, $params);

            if (!empty($teachers)) {
                foreach ($teachers as $teacher) {
                    // add to show teacher full name.
                    $teacher_profile_url = new moodle_url('/user/profile.php', ['id' => $teacher->teacherid]);
                    $teacher_fullname = html_writer::link($teacher_profile_url, format_string($teacher->teacher_firstname) . " " . format_string($teacher->teacher_lastname));

                    $teachers_fullname[] = $teacher_fullname;
                }
            }
            // Get image for the student.            
            // Get the avatar URL for the student.
            $student_avatar_url = \core_user::get_profile_picture(\core_user::get_user($student->childrenid, '*', MUST_EXIST));

            $course_score = grade_get_course_grade($student->childrenid, $student->courseid);
            $average_score = $course_score->grade ? round($course_score->grade, 2) : 0;

            // Add the row to the table.
            // Use html_writer to create the avatar image and other fields.
            $table->data[] = [
                $stt,
                html_writer::link($course_detail_url, format_string($student->course_name)),
                html_writer::tag(
                    'img',
                    '',
                    array(
                        'src' => $student_avatar_url->get_url($PAGE),
                        'alt' => 'Avatar image of ' . format_string($student->children_firstname) . " " . format_string($student->children_lastname),
                        'width' => 40,
                        'height' => 40,
                        'class' => 'rounded-avatar'
                    )
                )
                . html_writer::link(
                    $student_profile_url,
                    format_string($student->children_firstname) . " " . format_string($student->children_lastname),
                    ['class' => 'ms-2']
                ),
                implode(', ', $teachers_fullname),
                $student->course_study_days,
                $student->course_total_days,
                $average_score,
                $view_course_detail_action,
            ];
        }
        echo html_writer::table($table);

        echo $OUTPUT->paging_bar($total_records, $current_page, $per_page, $base_url);

        echo html_writer::end_tag('div');
    }

    echo $OUTPUT->footer();
} catch (Exception $e) {
    dlog($e->getTrace());

    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";

    throw new \moodle_exception('error', 'local_children_course_list_management', '', null, $e->getMessage());
}
