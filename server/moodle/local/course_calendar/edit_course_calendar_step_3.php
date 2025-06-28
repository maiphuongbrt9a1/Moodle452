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

try {
    // Yêu cầu người dùng đăng nhập
    require_login();
    require_capability('local/course_calendar:edit', context_system::instance()); // Kiểm tra quyền truy cập
    $PAGE->requires->css('/local/course_calendar/style/style.css');
    $PAGE->requires->js('/local/course_calendar/js/lib.js');

    // Khai báo các biến toàn cục
    global $PAGE, $OUTPUT, $DB, $USER;

    // $context = context_course::instance(SITEID); // Lấy ngữ cảnh của trang hệ thống
    $context = context_system::instance(); // Lấy ngữ cảnh của trang hệ thống
    // Đặt ngữ cảnh trang
    $PAGE->set_context($context); 

    // Thiết lập trang Moodle
    // Đặt URL cho trang hiện tại
    $PAGE->set_url(new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', [])); 
    // Tiêu đề trang
    $PAGE->set_title(get_string('teaching_schedule_assignment_choose_address_and_time', 'local_course_calendar')); 
    $PAGE->set_heading(get_string('teaching_schedule_assignment_choose_address_and_time', 'local_course_calendar'));

    // Thêm một breadcrumb cho các link khác.
    $PAGE->navbar->add(get_string('course_calendar_title', 'local_course_calendar'), new moodle_url('/local/course_calendar/index.php', [])); 


    // Thêm một breadcrumb cho các link khác.
    $PAGE->navbar->add(get_string('teaching_schedule_assignment', 'local_course_calendar'), new moodle_url('/local/course_calendar/index.php', [])); 

    // Thêm breadcrumb cho trang hiện tại
    $PAGE->navbar->add(get_string('teaching_schedule_assignment_choose_course', 'local_course_calendar'), new moodle_url('/local/course_calendar/edit_course_calendar_step_1.php', []));

    // Thêm breadcrumb cho trang hiện tại
    $PAGE->navbar->add(get_string('teaching_schedule_assignment_choose_teacher', 'local_course_calendar'), new moodle_url('/local/course_calendar/edit_course_calendar_step_2.php', []));

    // Thêm breadcrumb cho trang hiện tại
    $PAGE->navbar->add(get_string('teaching_schedule_assignment_choose_address_and_time', 'local_course_calendar'));

    // // add menu item to the settings navigation.
    // $settingsnav = $PAGE->settingsnav;
    // if (has_capability('local/course_calendar:edit', context_system::instance())) {
    //     if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
    //         $strfoo = get_string('edit_total_lesson_for_course', 'local_course_calendar');
    //         $url = new moodle_url('/local/course_calendar/edit_total_lesson_for_course.php', array('courseid' => $PAGE->course->id));
    //         $foonode = navigation_node::create(
    //             $strfoo,
    //             $url,
    //             navigation_node::NODETYPE_LEAF,
    //             get_string('edit_total_lesson_for_course', 'local_course_calendar'),
    //             'edit_total_lesson_for_course',
    //             new pix_icon('i/edit', $strfoo)
    //         );
    //         if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
    //             $foonode->make_active();
    //         }
    //         $settingnode->add_node($foonode);
    //     }
    // }


    echo $OUTPUT->header();

    // Nội dung trang của bạn
    echo $OUTPUT->box_start();
    $search_context = new stdClass();
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

    // Set default variable.
    $stt = 0;
    $available_time_address = [];

    $per_page = optional_param('perpage', 20, PARAM_INT);
    $current_page = optional_param('page', 0, PARAM_INT);
    $total_records = 0;
    $offset = $current_page * $per_page;
    $params = [];
    $courses = optional_param_array('selected_courses', [], PARAM_INT);
    $teachers = optional_param_array('selected_teachers', [], PARAM_INT);

    // Get all time_address of central.
    if (empty($search_query)) {
        $params = [];

        $total_count_sql = "SELECT count((concat(cs.id, cr.id)))
                            from {local_course_calendar_course_schedule} cs, {local_course_calendar_course_room} cr";
    
        $total_records = $DB->count_records_sql($total_count_sql, $params);

        $sql = "SELECT concat(cs.id, cr.id) id, cs.id course_schedule_id, cr.id course_room_id, cs.*, cr.*
                from {local_course_calendar_course_schedule} cs, {local_course_calendar_course_room} cr
                order by cs.id, cr.id asc";
        $available_time_address = $DB->get_records_sql($sql, $params, $offset, $per_page);
    }

    // if parent use search input, we need to filter the children list.
    if(!empty($search_query)) {
        
        // Escape the search query to prevent SQL injection.
        $search_query = trim($search_query);
        $search_query = '%' . $DB->sql_like_escape($search_query) . '%';
        $params = [
            'search_param_room_building' => $search_query,
            'search_param_ward_address'=> $search_query,
            'search_param_district_address'=> $search_query,
            'search_param_province_address'=> $search_query,
            'search_param_class_begin_time'=> $search_query,
            'search_param_class_end_time'=> $search_query,
            'search_param_class_total_sessions'=> $search_query,
        ];

        $total_count_sql = "SELECT count((concat(cs.id, cr.id)))
                            from {local_course_calendar_course_schedule} cs, {local_course_calendar_course_room} cr
                            where  
                                        (
                                            cr.room_building like :search_param_room_building 
                                            or cr.ward_address like :search_param_ward_address
                                            or cr.district_address like :search_param_district_address
                                            or cr.province_address like :search_param_province_address
                                            or cs.class_begin_time like :search_param_class_begin_time
                                            or cs.class_end_time like :search_param_class_end_time
                                            or cs.class_total_sessions like :search_param_class_total_sessions
                                        )";
        
        $total_records = $DB->count_records_sql($total_count_sql, $params);
        // Process the search query.
        $sql = "SELECT concat(cs.id, cr.id) id, cs.id course_schedule_id, cr.id course_room_id, cs.*, cr.*
                from {local_course_calendar_course_schedule} cs, {local_course_calendar_course_room} cr
                where  
                            (
                                cr.room_building like :search_param_room_building 
                                or cr.ward_address like :search_param_ward_address
                                or cr.district_address like :search_param_district_address
                                or cr.province_address like :search_param_province_address
                                or cs.class_begin_time like :search_param_class_begin_time
                                or cs.class_end_time like :search_param_class_end_time
                                or cs.class_total_sessions like :search_param_class_total_sessions
                            )
                ORDER BY cr.id, cs.id ASC";
        $available_time_address = $DB->get_records_sql($sql, $params, $offset, $per_page);
    }

    // Display children list of parent on screen.
    if (!$available_time_address) {
        echo $OUTPUT->notification(get_string('no_address_and_time_found', 'local_course_calendar'), 'info');
    } else {
        // If there are children, display them in a table.
        // and parent does not need to search for children.
        echo html_writer::start_tag('form', ['action' => 'process_edit_course_calendar_after_step_3.php', 'method' => 'get']);
        
        
        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', []);
        if (!empty($search_query)) {
            $base_url->param('searchquery', $search_query);
        }

        // Display the list of children in a table.
        $table = new html_table();
        $table->head = [
            // Checkbox "Select All"
            html_writer::empty_tag('input', [
                'type' => 'checkbox',
                'id' => 'selectall',
                'onchange' => 'toggleAllCheckboxes(this)',
            ]),
            get_string('stt', 'local_course_calendar'),
            get_string('building', 'local_course_calendar'),
            get_string('floor', 'local_course_calendar'),
            get_string('room', 'local_course_calendar'),
            get_string('start_time', 'local_course_calendar'),
            get_string('end_time', 'local_course_calendar'),
            get_string('address', 'local_course_calendar')
        ];
        $table->align = ['center', 'center', 'left', 'left','left', 'center', 'center', 'left'];
        foreach ($available_time_address as $time_address) {
            // add no. for the table.
            $stt = $stt + 1;
            
            // Add the row to the table.
            // Use html_writer to create the avatar image and other fields.
            $table->data[] = [
                html_writer::empty_tag('input', [
                    'type' => 'checkbox',
                    // 'value' => [$time_address->course_schedule_id, $time_address->course_room_id],
                    'value' => $time_address->course_schedule_id . '|' . $time_address->course_room_id,
                    'class' => 'select-checkbox',
                    'name' => 'selected_times_and_addresses[]',
                ]),
                $stt,
                $time_address->room_building,
                $time_address->room_floor,
                $time_address->room_number,
                date('D, d-m-Y H:i:s', $time_address->class_begin_time),
                date('D, d-m-Y H:i:s', $time_address->class_end_time),
                $time_address->ward_address . ', ' . $time_address->district_address . ', ' . $time_address->province_address

            ];
        }
        echo html_writer::table($table);

        if (!empty($courses)) { 
            foreach ($courses as $courseid) {
                // Add hidden input for each selected course.
                echo html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'selected_courses[]',
                    'value' => $courseid,
                ]);
            }
        }

        if (!empty($teachers)) {
            foreach ($teachers as $teacherid) {
                // Add hidden input for each selected teacher.
                echo html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'selected_teachers[]',
                    'value' => $teacherid,
                ]);
            }
        }

        echo '<div class="d-flex justify-content-end align-items-center">';
            echo '<div>';
                echo html_writer::empty_tag('input', array('class' => 'btn btn-primary form-submit', 'type' => 'submit', 'value' => get_string('save_changes','local_course_calendar')));
            echo '</div>';
        echo '</div>';
        
        echo html_writer::end_tag('form');
        
        echo $OUTPUT->paging_bar($total_records, $current_page, $per_page, $base_url);
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
