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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/course_calendar/lib.php');
require_once($CFG->dirroot . '/local/dlog/lib.php');
use local_course_calendar\helper as LocalCourseCalendarHelper;
use local_course_calendar as LocalCourseCalendar;

try {
    // Yêu cầu người dùng đăng nhập
    require_login();
    require_capability('local/course_calendar:edit', context_system::instance()); // Kiểm tra quyền truy cập
    $PAGE->requires->css('/local/course_calendar/style/style.css');
    $PAGE->requires->js('/local/course_calendar/js/lib.js');
    $per_page = optional_param('perpage', 20, PARAM_INT);
    $current_page = optional_param('page', 0, PARAM_INT);

    try {
        $selected_courses_from_request = required_param('selected_courses', PARAM_INT);
        $SESSION->edit_course_calendar_step_11_prev_course_section_form_selected_course = $selected_courses_from_request;
    } catch (Exception $e) {
        dlog($e->getTrace());
        $params = [];
        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_1.php', $params);
        redirect($base_url, "You must select one course.", 0, \core\output\notification::NOTIFY_ERROR);
    }


    // Khai báo các biến toàn cục
    global $PAGE, $OUTPUT, $DB, $USER;

    $selected_courses_name_from_request = $DB->get_field(
        'course',
        'fullname',
        ['id' => $selected_courses_from_request],
        MUST_EXIST
    );

    $context = context_system::instance(); // Lấy ngữ cảnh của trang hệ thống
    // Đặt ngữ cảnh trang
    $PAGE->set_context($context);

    // Thiết lập trang Moodle
    // Đặt URL cho trang hiện tại
    $PAGE->set_url(new moodle_url(
        '/local/course_calendar/edit_course_calendar_step_11_prev_course_section.php',
        []
    ));
    // Tiêu đề trang
    $PAGE->set_title(get_string(
        'previous_course_section_schedule_information:',
        'local_course_calendar',
        $selected_courses_name_from_request
    ));
    $PAGE->set_heading(get_string(
        'previous_course_section_schedule_information:',
        'local_course_calendar',
        $selected_courses_name_from_request
    ));

    // Thêm một breadcrumb cho các link khác.
    $PAGE->navbar->add(get_string(
        'course_calendar_title',
        'local_course_calendar'
    ), new moodle_url('/local/course_calendar/index.php', []));

    // Thêm một breadcrumb cho các link khác.
    $PAGE->navbar->add(get_string(
        'teaching_schedule_assignment',
        'local_course_calendar'
    ), new moodle_url('/local/course_calendar/index.php', []));
    // Thêm một breadcrumb cho các link khác.
    $params = [];
    $PAGE->navbar->add(
        get_string(
            'teaching_schedule_assignment_choose_course',
            'local_course_calendar'
        ),
        new moodle_url('/local/course_calendar/edit_course_calendar_step_1.php', $params)
    );

    // Thêm breadcrumb cho trang hiện tại
    $PAGE->navbar->add(get_string(
        'previous_course_section_schedule_information:',
        'local_course_calendar',
        $selected_courses_name_from_request
    ));
    
    echo $OUTPUT->header();

    // Nội dung trang của bạn
    echo $OUTPUT->box_start();
    $search_context = new stdClass();
    $search_context->method = 'get';
    $search_context->action = $PAGE->url; // Action URL for the search form
    $search_context->inputname = 'searchquery';
    $search_context->searchstring = get_string('searchitems', 'local_course_calendar'); // Placeholder text for the search input

    $search_query = optional_param('searchquery', '', PARAM_TEXT); // Get the search query from the URL parameters.
    $current_params = [];
    $current_params[] = [
        'name' => 'selected_courses',
        'value' => required_param('selected_courses', PARAM_INT)
    ];
    $search_context->hiddenfields = $current_params;

    $search_context->value = $search_query; // Set the value of the search input to the current search query.
    $search_context->extraclasses = 'my-2'; // Additional CSS classes for styling
    $search_context->btnclass = 'btn-primary';

    // Renderer for template core
    $core_renderer = $PAGE->get_renderer('core');

    // Render search input
    echo $core_renderer->render_from_template('core/search_input', $search_context);

    // --- End code to render Search Input ---

    // Set default variable.
    $stt = 0;
    $courses = [];
    $total_records = 0;
    $offset = $current_page * $per_page;
    $params = [];

    // Khởi tạo dữ liệu và xử lý cho việc sắp xếp dữ liệu trong các cột dữ liệu
    $valid_sort_columns = [
        'class_begin_time',
        'class_end_time',
        'room_number'
    ];

    $sort_directions = ['asc', 'desc'];

    $sort = optional_param('sort', 'class_begin_time', PARAM_ALPHANUMEXT);
    $direction = optional_param('direction', 'asc', PARAM_ALPHA);

    if (!in_array($sort, $valid_sort_columns)) {
        $sort = 'class_begin_time';
    }

    if (!in_array($direction, $sort_directions)) {
        $direction = 'asc';
    }

    // Get all children of current parent account.
    if (empty($search_query)) {
        $params = [
            'search_param_courseid' => $selected_courses_from_request
        ];

        $total_count_sql = "SELECT count(cr.id)
                                    from {local_course_calendar_course_room} cr
                                    join {local_course_calendar_course_section} cs on cr.id = cs.course_room_id
                                    where cs.courseid = :search_param_courseid";

        $total_records = $DB->count_records_sql($total_count_sql, $params);

        $sql = "SELECT cs.id as course_section_id,
                            cr.id AS room_id, -- ĐẶT CỘT ID CỦA BẢNG CHÍNH (cr) LÀM CỘT ĐẦU TIÊN VÀ ĐẢM BẢO NÓ DUY NHẤT
                            cs.courseid,
                            cr.room_number,
                            cr.room_floor,
                            cr.room_building,
                            cr.ward_address,
                            cr.district_address,
                            cr.province_address,
                            cr.room_online_url,
                            cs.class_begin_time,
                            cs.class_end_time
                        from {local_course_calendar_course_room} cr
                        join {local_course_calendar_course_section} cs on cr.id = cs.course_room_id
                        where cs.courseid = :search_param_courseid
                        order by {$sort} {$direction}";
        $course_section_schedule_information = $DB->get_records_sql($sql, $params, $offset, $per_page);
    }

    // if parent use search input, we need to filter the children list.
    if (!empty($search_query)) {

        // Escape the search query to prevent SQL injection.
        $search_query = trim($search_query);
        $search_query = '%' . $DB->sql_like_escape($search_query) . '%';
        $params = [
            'search_param_courseid' => $selected_courses_from_request,
            'search_param_room_building' => $search_query,
            'search_param_room_number' => $search_query,
            'search_param_room_floor' => $search_query,
            'search_param_ward_address' => $search_query,
            'search_param_district_address' => $search_query,
            'search_param_province_address' => $search_query,
            'search_param_start_class_time' => $start_class_time,
            'search_param_end_class_time' => $end_class_time,
        ];

        $total_count_sql = "SELECT count(cr.id)
                                    from {local_course_calendar_course_room} cr
                                    join {local_course_calendar_course_section} cs on cr.id = cs.course_room_id
                                    where cs.courseid = :search_param_courseid 
                                    and
                                        (
                                            cr.room_building like :search_param_room_building 
                                            or cr.room_number like :search_param_room_number
                                            or cr.room_floor like :search_param_room_floor
                                            or cr.ward_address like :search_param_ward_address
                                            or cr.district_address like :search_param_district_address
                                            or cr.province_address like :search_param_province_address
                                            or cs.province_address like :search_param_start_class_time
                                            or cs.province_address like :search_param_end_class_time
                                        )";

        $total_records = $DB->count_records_sql($total_count_sql, $params);
        // Process the search query.
        $sql = "SELECT cs.id as course_section_id,
                            cr.id AS room_id, -- ĐẶT CỘT ID CỦA BẢNG CHÍNH (cr) LÀM CỘT ĐẦU TIÊN VÀ ĐẢM BẢO NÓ DUY NHẤT
                            cs.courseid,
                            cr.room_number,
                            cr.room_floor,
                            cr.room_building,
                            cr.ward_address,
                            cr.district_address,
                            cr.province_address,
                            cr.room_online_url
                            cs.class_begin_time,
                            cs.class_end_time
                        from {local_course_calendar_course_room} cr
                        join {local_course_calendar_course_section} cs on cr.id = cs.course_room_id
                        where cs.courseid = :search_param_courseid 
                        and
                            (
                                cr.room_building like :search_param_room_building 
                                or cr.room_number like :search_param_room_number
                                or cr.room_floor like :search_param_room_floor
                                or cr.ward_address like :search_param_ward_address
                                or cr.district_address like :search_param_district_address
                                or cr.province_address like :search_param_province_address
                                or cs.province_address like :search_param_start_class_time
                                or cs.province_address like :search_param_end_class_time
                            )
                        order by {$sort} {$direction}";

        $course_section_schedule_information = $DB->get_records_sql(
            $sql,
            $params,
            $offset,
            $per_page
        );
    }

    // Display children list of parent on screen.
    if (!$course_section_schedule_information) {
        echo $OUTPUT->notification(get_string('no_prev_course_section_schedule_found', 'local_course_calendar'), 'info');
        $params = [];
        if (isset($selected_courses_from_request)) {
            $params['selected_courses'] = $selected_courses_from_request;
        }

        // Add a button to continue.
        $continue_button = new moodle_url('/local/course_calendar/edit_course_calendar_step_2.php', $params);
        echo '<div class="d-flex justify-content-end align-items-center">';
        echo '<div><a class="btn btn-primary " href="' . $continue_button->out() . '">Continue</a></div>';
        echo '</div>';

    } else {

        // If there are children, display them in a table.
        // and parent does not need to search for children.
        echo html_writer::start_tag(
            'form',
            [
                'action' => 'edit_course_calendar_step_2.php',
                'method' => 'get'
            ]
        );

        // Display the list of children in a table.
        $table = new html_table();
        $table->head = [
            get_string('stt', 'local_course_calendar'),
            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'class_begin_time',
                get_string('start_date', 'local_course_calendar'),
                $sort,
                $direction,
                ['selected_courses' => $selected_courses_from_request]
            ),

            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'class_end_time',
                get_string('end_date', 'local_course_calendar'),
                $sort,
                $direction,
                ['selected_courses' => $selected_courses_from_request]
            ),

            get_string('teacher_full_name', 'local_course_calendar'),

            LocalCourseCalendarHelper::make_sort_table_header_helper(
                $PAGE,
                'room_number',
                get_string('address', 'local_course_calendar'),
                $sort,
                $direction,
                ['selected_courses' => $selected_courses_from_request]
            ),
        ];
        $table->align = ['center', 'center', 'left', 'left', 'center'];
        foreach ($course_section_schedule_information as $course_section) {
            // add no. for the table.
            $stt = $stt + 1;

            // add sql to query teachers in current course
            $teachers = [];
            $teachers_fullname = [];

            // search teachers by name.
            // If search query is empty, we will get all teachers in current course.
            if (empty($search_query)) {

                $sql = "SELECT  concat(teacher.id, cs.courseid, cs.id) id, 
                                teacher.id teacherid, 
                                teacher.firstname teacher_firstname, 
                                teacher.lastname teacher_lastname,
                                cs.class_begin_time,
                                cs.class_end_time
                        from {user} teacher
                        join {local_course_calendar_course_section} cs 
                            on teacher.id = cs.editing_teacher_primary_teacher 
                                or teacher.id = cs.non_editing_teacher_secondary_teacher
                        join {local_course_calendar_course_room} cr on cs.course_room_id = cr.id
                        where cs.courseid = :course_section_id
                                and cs.class_begin_time = :course_section_class_begin_time
                                and cs.class_end_time = :course_section_class_end_time";
                $params = [
                    'course_section_id' => $course_section->courseid,
                    'course_section_class_begin_time' => $course_section->class_begin_time,
                    'course_section_class_end_time' => $course_section->class_end_time
                ];
            } else {
                $sql = "SELECT  concat(teacher.id, cs.courseid, cs.id) id, 
                                teacher.id teacherid, 
                                teacher.firstname teacher_firstname, 
                                teacher.lastname teacher_lastname
                        from {user} teacher
                        join {local_course_calendar_course_section} cs 
                            on teacher.id = cs.editing_teacher_primary_teacher 
                                or teacher.id = cs.non_editing_teacher_secondary_teacher
                        join {local_course_calendar_course_room} cr on cs.course_room_id = cr.id
                        where cs.courseid = :course_section_id
                            and cs.class_begin_time = :course_section_class_begin_time
                            and cs.class_end_time = :course_section_class_end_time
                            and (teacher.firstname like :searchparamteachername 
                                or teacher.lastname like :searchparamteachername)";
                $params = [
                    'course_section_id' => $course_section->courseid,
                    'course_section_class_begin_time' => $course_section->class_begin_time,
                    'course_section_class_end_time' => $course_section->class_end_time,
                    'searchparamteachername' => $search_query
                ];
            }
            // Get all teachers in current course.
            $teachers = $DB->get_records_sql($sql, $params);

            if (!empty($teachers)) {
                foreach ($teachers as $teacher) {
                    // add to show teacher full name.
                    $teacher_profile_url = new moodle_url('/user/profile.php', ['id' => $teacher->teacherid]);
                    $teacher_fullname = html_writer::link($teacher_profile_url, format_string($teacher->teacher_firstname) . " " . format_string($teacher->teacher_lastname));

                    $teachers_fullname[] = $teacher_fullname;
                }
            }

            // Add the row to the table.
            // Use html_writer to create the avatar image and other fields.
            $table->data[] = [
                $stt,
                date('D, d/m/Y H:i', $course_section->class_begin_time),
                date('D, d/m/Y H:i', $course_section->class_end_time),
                implode(', ', $teachers_fullname),
                $course_section->room_building
                . ', Floor ' . $course_section->room_floor
                . ', Room ' . $course_section->room_number
                . ', ' . $course_section->ward_address
                . ', ' . $course_section->district_address
                . ', ' . $course_section->province_address,
            ];
        }
        echo html_writer::table($table);

        if (isset($selected_courses_from_request)) {
            echo html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => 'selected_courses',
                'value' => $selected_courses_from_request,
            ]);
        }

        echo '<div class="d-flex justify-content-end align-items-center">';

        echo '<div class="me-2">';
        $params = [];
        $back_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_1.php', $params);
        echo '<div class="d-flex justify-content-end align-items-center">';
        echo '<div><a class="btn btn-secondary " href="' . $back_url->out() . '">Back</a></div>';
        echo '</div>';
        echo '</div>';

        echo '<div>';
        echo html_writer::empty_tag(
            'input',
            array(
                'class' => 'btn btn-primary form-submit',
                'type' => 'submit',
                'value' => get_string('next_step', 'local_course_calendar')
            )
        );
        echo '</div>';
        echo '</div>';

        $params = [];

        if (isset($selected_courses_from_request)) {
            $params['selected_courses'] = $selected_courses_from_request;
        }

        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_11_prev_course_section.php', []);
        if (!empty($search_query)) {
            $base_url->param('searchquery', $search_query);
        }

        echo html_writer::end_tag('form');

        echo $OUTPUT->paging_bar(
            $total_records,
            $current_page,
            $per_page,
            $base_url
        );

    }

    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();

} catch (Exception $e) {
    dlog($e->getTrace());

    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";

    throw new \moodle_exception(
        'error',
        'local_course_calendar',
        '',
        null,
        $e->getMessage()
    );
}
