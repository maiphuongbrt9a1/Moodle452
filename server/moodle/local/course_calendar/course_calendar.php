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
 * Version information for local_course_calendar
 *
 * @package    local_course_calendar
 * @copyright  2025 Võ Mai Phương <vomaiphuonghhvt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Include necessary Moodle libraries.
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/course_calendar/lib.php');
require_once($CFG->dirroot . '/local/dlog/lib.php');
use local_course_calendar\helper as LocalCourseCalendarHelper;

try {
    // Yêu cầu người dùng đăng nhập
    require_login();
    require_capability('local/course_calendar:view', context_system::instance()); // Kiểm tra quyền truy cập

    // Khai báo các biến toàn cục
    global $PAGE, $OUTPUT, $DB;

    $context = context_system::instance(); // Lấy ngữ cảnh của trang hệ thống
    // Đặt ngữ cảnh trang
    $PAGE->set_context($context);

    // Thiết lập trang Moodle
    // Đặt URL cho trang hiện tại
    $PAGE->set_url(new moodle_url('/local/course_calendar/course_calendar.php', []));
    // Tiêu đề trang
    $PAGE->set_title(get_string('course_calendar_list_title', 'local_course_calendar'));
    $PAGE->set_heading(get_string('course_calendar_list_heading', 'local_course_calendar'));

    $secondarynav = $PAGE->secondarynav;

    $indexurl = new moodle_url('/local/course_calendar/index.php', []);
    $secondarynav->add(get_string('teaching_schedule_assignment', 'local_course_calendar'), $indexurl);

    $settingsurl = new moodle_url('/local/course_calendar/course_calendar.php', []);
    $node = $secondarynav->add(get_string('course_calendar_list', 'local_course_calendar'), $settingsurl);
    $node->make_active();

    $reportsurl = new moodle_url('/local/course_calendar/course_calendar_statistic.php', []);
    $secondarynav->add(get_string('course_teaching_statistics', 'local_course_calendar'), $reportsurl);

    echo $OUTPUT->header();

    // Nội dung trang của bạn
    echo $OUTPUT->box_start();
    $search_context = new stdClass();
    $search_context->method = 'get'; // Method for the search form
    $search_context->action = $PAGE->url; // Action URL for the search form
    $search_context->inputname = 'searchquery';
    $search_context->searchstring = get_string('searchitems', 'local_course_calendar'); // Placeholder text for the search input

    $search_query = optional_param('searchquery', '', PARAM_TEXT); // Get the search query from the URL parameters.

    $search_context->value = $search_query; // Set the value of the search input to the current search query.
    $search_context->extraclasses = 'my-2'; // Additional CSS classes for styling
    $search_context->btnclass = 'btn-primary';

    // Renderer for template core
    $core_renderer = $PAGE->get_renderer('core');

    // Render search input
    echo $core_renderer->render_from_template('core/search_input', $search_context);

    // --- End code to render Search Input ---

    // print current week and date time
    echo "<div class = 'd-flex justify-content-center p-3 mb-2'>";
    echo "<div>";
    echo "Week " . date('W', time()) . ', ' . date('D, d-m-Y', time());
    echo "</div>";
    echo "</div>";

    // Set default variable.
    $stt = 0;
    $courses = [];
    $courses_display_form = [];
    $per_page = optional_param('perpage', 20, PARAM_INT);
    $current_page = optional_param('page', 0, PARAM_INT);
    $total_records = 0;
    $offset = $current_page * $per_page;
    $params = [];
    $admin_id = get_admin()->id;

    // Khởi tạo dữ liệu và xử lý cho việc sắp xếp dữ liệu trong các cột dữ liệu
    $valid_sort_columns = [
        'course_fullname',
        'user_lastname',
        'class_begin_time',
        'class_end_time',
        'room_number'
    ];

    $sort_directions = ['asc', 'desc'];

    $sort = optional_param('sort', 'course_fullname', PARAM_ALPHANUMEXT);
    $direction = optional_param('direction', 'asc', PARAM_ALPHA);

    if (!in_array($sort, $valid_sort_columns)) {
        $sort = 'course_fullname';
    }

    if (!in_array($direction, $sort_directions)) {
        $direction = 'asc';
    }

    // Get all course with teacher and room information.
    if (empty($search_query)) {
        $params = ['admin_id' => $admin_id];

        $total_count_sql = "SELECT count(*)
                            FROM mdl_user user 
                            join mdl_role_assignments ra on ra.userid = user.id
                            join mdl_role r on r.id = ra.roleid
                            join mdl_context ctx on ctx.id = ra.contextid
                            join mdl_course c on c.id = ctx.instanceid 
                            join mdl_local_course_calendar_course_section course_section on course_section.courseid = c.id
                            join mdl_local_course_calendar_course_room course_room on course_room.id = course_section.course_room_id
                            WHERE c.id != 1 
                                and user.id != :admin_id
                                and (r.shortname = 'editingteacher' or r.shortname = 'teacher')  
                                and ctx.contextlevel = 50   
                            ";
        $total_records = $DB->count_records_sql($total_count_sql, $params);

        $sql = "SELECT concat (user.id, c.id, course_section.class_begin_time) id,
                        user.id userid, 
                        user.firstname user_firstname, 
                        user.lastname user_lastname, 
                        c.id courseid, 
                        c.fullname course_fullname, 
                        course_room.room_building, 
                        course_room.room_floor,
                        course_room.room_number,
                        course_room.ward_address,
                        course_room.district_address,
                        course_room.province_address,
                        course_room.room_online_url,
                        course_section.class_begin_time,
                        course_section.class_end_time
                FROM mdl_user user 
                join mdl_role_assignments ra on ra.userid = user.id
                join mdl_role r on r.id = ra.roleid
                join mdl_context ctx on ctx.id = ra.contextid
                join mdl_course c on c.id = ctx.instanceid 
                join mdl_local_course_calendar_course_section course_section on course_section.courseid = c.id
                join mdl_local_course_calendar_course_room course_room on course_room.id = course_section.course_room_id
                WHERE c.id != 1 
                    and user.id != :admin_id
                    and (r.shortname = 'editingteacher' or r.shortname = 'teacher')  
                    and ctx.contextlevel = 50    
                ORDER BY {$sort} {$direction}";
        $courses = $DB->get_records_sql($sql, $params);
    }

    // if admin use search input, we need to filter the course calendar list.
    if (!empty($search_query)) {

        // Escape the search query to prevent SQL injection.
        $search_query = trim($search_query);
        $search_query = '%' . $DB->sql_like_escape($search_query) . '%';
        $params = [
            'admin_id' => $admin_id,
            'search_param_course_id' => $search_query,
            'search_param_course_name' => $search_query,
            'search_param_user_firstname' => $search_query,
            'search_param_user_lastname' => $search_query,
            'search_param_room_building' => $search_query,
            'search_param_room_floor' => $search_query,
            'search_param_room_number' => $search_query,
            'search_param_ward_address' => $search_query,
            'search_param_district_address' => $search_query,
            'search_param_province_address' => $search_query,
            'search_param_room_online_url' => $search_query,
            'search_param_class_begin_time' => $search_query,
            'search_param_class_end_time' => $search_query
        ];

        $total_count_sql = "SELECT count(*)
                            FROM mdl_user user 
                            join mdl_role_assignments ra on ra.userid = user.id
                            join mdl_role r on r.id = ra.roleid
                            join mdl_context ctx on ctx.id = ra.contextid
                            join mdl_course c on c.id = ctx.instanceid 
                            join mdl_local_course_calendar_course_section course_section on course_section.courseid = c.id
                            join mdl_local_course_calendar_course_room course_room on course_room.id = course_section.course_room_id            
                            WHERE c.id != 1 
                                and user.id != :admin_id
                                and (r.shortname = 'editingteacher' or r.shortname = 'teacher')  
                                and ctx.contextlevel = 50   
                                and 
                                    (
                                        c.id like :search_param_course_id 
                                        or c.fullname like :search_param_course_name
                                        or user.firstname like :search_param_user_firstname
                                        or user.lastname like :search_param_user_lastname
                                        or course_room.room_building like :search_param_room_building
                                        or course_room.room_floor like :search_param_room_floor
                                        or course_room.room_number like :search_param_room_number
                                        or course_section.ward_address like :search_param_ward_address
                                        or course_section.district_address like :search_param_district_address
                                        or course_section.province_address like :search_param_province_address
                                        or course_room.online_url like :search_param_room_online_url
                                        or course_section.class_begin_time like :search_param_class_begin_time
                                        or course_section.class_end_time like :search_param_class_end_time
                                    )";

        $total_records = $DB->count_records_sql($total_count_sql, $params);
        // Process the search query.
        $sql = "SELECT concat (user.id, c.id, course_section.class_begin_time) id,
                        user.id userid, 
                        user.firstname user_firstname, 
                        user.lastname user_lastname, 
                        c.id courseid, 
                        c.fullname course_fullname, 
                        course_room.room_building, 
                        course_room.room_floor,
                        course_room.room_number,
                        course_room.ward_address,
                        course_room.district_address,
                        course_room.province_address,
                        course_room.room_online_url,
                        course_section.class_begin_time,
                        course_section.class_end_time
                FROM mdl_user user 
                join mdl_role_assignments ra on ra.userid = user.id
                join mdl_role r on r.id = ra.roleid
                join mdl_context ctx on ctx.id = ra.contextid
                join mdl_course c on c.id = ctx.instanceid 
                join mdl_local_course_calendar_course_section course_section on course_section.courseid = c.id
                join mdl_local_course_calendar_course_room course_room on course_room.id = course_section.course_room_id
                WHERE c.id != 1 
                    and user.id != :admin_id
                    and (r.shortname = 'editingteacher' or r.shortname = 'teacher')  
                    and ctx.contextlevel = 50   
                    and 
                        (
                            c.id like :search_param_course_id 
                            or c.fullname like :search_param_course_name
                            or user.firstname like :search_param_user_firstname
                            or user.lastname like :search_param_user_lastname
                            or course_room.room_building like :search_param_room_building
                            or course_room.room_floor like :search_param_room_floor
                            or course_room.room_number like :search_param_room_number
                            or course_section.ward_address like :search_param_ward_address
                            or course_section.district_address like :search_param_district_address
                            or course_section.province_address like :search_param_province_address
                            or course_room.online_url like :search_param_room_online_url
                            or course_section.class_begin_time like :search_param_class_begin_time
                            or course_section.class_end_time like :search_param_class_end_time
                        )
                ORDER BY {$sort} {$direction}";

        $courses = $DB->get_records_sql($sql, $params);
    }

    // Display children list of parent on screen.
    if (!$courses) {
        echo $OUTPUT->notification(get_string('no_course_found', 'local_course_calendar'), 'info');
    } else {
        // If there are children, display them in a table.
        // and parent does not need to search for children.
        echo html_writer::start_tag('div');

        $base_url = new moodle_url('/local/course_calendar/course_calendar.php', []);
        if (!empty($search_query)) {
            $base_url->param('searchquery', $search_query);
        }

        // Display the list of children in a table.
        $table = new html_table();
        $table->head = [
            get_string('stt', 'local_course_calendar'),
            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'course_fullname',
                get_string('course_full_name', 'local_course_calendar'),
                $sort,
                $direction
            ),

            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'user_lastname',
                get_string('teacher_full_name', 'local_course_calendar'),
                $sort,
                $direction
            ),

            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'class_begin_time',
                get_string('start_time', 'local_course_calendar'),
                $sort,
                $direction
            ),

            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'class_end_time',
                get_string('end_time', 'local_course_calendar'),
                $sort,
                $direction
            ),

            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'room_number',
                get_string('address', 'local_course_calendar'),
                $sort,
                $direction
            ),
            html_writer::empty_tag('div'),
        ];
        $table->align = ['center', 'left', 'center', 'center', 'center', 'center'];

        foreach ($courses as $course) {
            $course_key = $course->courseid;
            if (
                !isset($courses_display_form[$course_key])
                or (
                    isset($courses_display_form[$course_key])
                    and date("D, H:i", $courses_display_form[$course_key]->class_begin_time) != date("D, H:i", $course->class_begin_time)
                    and date("D, H:i", $courses_display_form[$course_key]->class_end_time) != date("D, H:i", $course->class_end_time)
                )
            ) {
                $courses_display_form[$course_key] = new stdClass();

                $courses_display_form[$course_key]->stt = $stt + 1;
                $courses_display_form[$course_key]->course = $course;
                $courses_display_form[$course_key]->class_begin_time = $course->class_begin_time;
                $courses_display_form[$course_key]->class_end_time = $course->class_end_time;
                $courses_display_form[$course_key]->week[] = date("W", $course->class_begin_time);

                // You might want to add a link to course's profile overview and course detail.
                $courses_display_form[$course_key]->course_detail_url = new moodle_url('/course/view.php', ['id' => $course->courseid]);
                $courses_display_form[$course_key]->teacher_detail_url = new moodle_url('/user/profile.php', ['id' => $course->userid]);
                // If the user has permission to edit the course, add an edit link.
                if (has_capability('local/course_calendar:edit', context_system::instance())) {
                    $edit_schedule_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_2.php', ['selected_courses' => $course->courseid]);
                    $courses_display_form[$course_key]->edit_course_schedule_action = $OUTPUT->action_icon(
                        $edit_schedule_url,
                        new pix_icon('i/edit', get_string('edit_schedule', 'local_course_calendar'))
                    );
                }

                $courses_display_form[$course_key]->view_course_detail_action = $OUTPUT->action_icon(
                    $courses_display_form[$course_key]->course_detail_url,
                    new pix_icon('i/hide', get_string('view_course_detail', 'local_course_calendar'))
                );

            } else if (
                isset($courses_display_form[$course_key])
                and date("D, H:i", $courses_display_form[$course_key]->class_begin_time) == date("D, H:i", $course->class_begin_time)
                and date("D, H:i", $courses_display_form[$course_key]->class_end_time) == date("D, H:i", $course->class_end_time)
            ) {
                // If the course already exists, append the week to the existing weeks array.
                $courses_display_form[$course_key]->week[] = date("W", $course->class_begin_time);
            }

        }

        foreach ($courses_display_form as $course) {
            // Add the row to the table.
            // Use html_writer to create the avatar image and other fields.
            $table->data[] = [
                $course->stt,
                html_writer::link($course->course_detail_url, format_string($course->course->course_fullname)),
                html_writer::link($course->teacher_detail_url, $course->course->user_firstname . ' ' . $course->course->user_lastname),
                date('D, H:i', $course->class_begin_time),
                date('D, H:i', $course->class_end_time),
                implode("| ", $course->week),
                $course->course->room_building . '- Floor ' . $course->course->room_floor . '- Room ' . $course->course->room_number . ' - ' .
                $course->course->ward_address . ', ' . $course->course->district_address . ', ' . $course->course->province_address . '<br>',
                $course->view_course_detail_action . ' ' . $course->edit_course_schedule_action
            ];
        }
        echo html_writer::table($table);

        echo $OUTPUT->paging_bar($total_records, $current_page, $per_page, $base_url);

        echo html_writer::end_tag('div');
    }

    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();

} catch (Exception $e) {
    dlog($e->getTrace());

    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";

    throw new \moodle_exception('error', 'local_course_calendar', '', null, $e->getMessage());
}
