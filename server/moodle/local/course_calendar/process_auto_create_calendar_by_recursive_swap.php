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
require_once($CFG->dirroot . '/local/course_calendar/classes/form/process_auto_create_calendar.php');

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

    // Khai báo các biến toàn cục
    global $PAGE, $OUTPUT, $DB, $USER;
    $context = context_system::instance(); // Lấy ngữ cảnh của trang hệ thống
    // Đặt ngữ cảnh trang
    $PAGE->set_context($context);

    // Thiết lập trang Moodle
    // Đặt URL cho trang hiện tại
    $PAGE->set_url(new \moodle_url('/local/course_calendar/process_auto_create_calendar_by_recursive_swap.php', []));
    // Tiêu đề trang
    $PAGE->set_title(get_string('course_calendar_title', 'local_course_calendar'));
    $PAGE->set_heading(get_string('process_auto_create_course_schedule_time_table', 'local_course_calendar'));

    // Thêm một breadcrumb cho các link khác.
    $PAGE->navbar->add(get_string('course_calendar_title', 'local_course_calendar'), new \moodle_url('/local/course_calendar/index.php', []));


    // Thêm một breadcrumb cho các link khác.
    $PAGE->navbar->add(get_string('teaching_schedule_assignment', 'local_course_calendar'), new \moodle_url('/local/course_calendar/index.php', []));

    // Thêm breadcrumb cho trang hiện tại
    $PAGE->navbar->add(get_string('auto_create_course_schedule_time_table', 'local_course_calendar'), new \moodle_url('/local/course_calendar/auto_create_calendar_by_recursive_swap.php', []));

    // Thêm breadcrumb cho trang hiện tại
    $PAGE->navbar->add(get_string('process_auto_create_course_schedule_time_table', 'local_course_calendar'));

    $mform = new \local_course_calendar\form\process_auto_create_calendar_form();

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        $base_url = new moodle_url('/local/course_calendar/auto_create_calendar_by_recursive_swap.php', []);

        redirect(
            $base_url,
            "Canceled save data.",
            0,
            \core\output\notification::NOTIFY_ERROR
        );

    } else if ($mform->is_submitted()) {

        global $PAGE, $OUTPUT, $USER;

        $cache = \cache::make('local_course_calendar', 'time_table_cache');
        $cache_key = 'user_timetable_' . $USER->id;
        $time_table = $cache->get($cache_key);

        if ($time_table !== false) {
            try {
                $success_flag = $time_table->save_data_into_database();
                if ($success_flag) {

                    $cache = \cache::make('local_course_calendar', 'time_table_cache');
                    $cache_key = 'user_timetable_' . $USER->id;
                    $cache->delete($cache_key);

                    $base_url = new moodle_url('/local/course_calendar/auto_create_calendar_by_recursive_swap.php', []);
                    redirect(
                        $base_url,
                        "Save data successfully.",
                        0,
                        \core\output\notification::NOTIFY_SUCCESS
                    );
                }
            } catch (Exception $e) {
                $base_url = new moodle_url('/local/course_calendar/auto_create_calendar_by_recursive_swap.php', []);
                redirect(
                    $base_url,
                    "Time table didn't create successfully.",
                    0,
                    \core\output\notification::NOTIFY_ERROR
                );
            }
        }
    } else {
        try {
            $courseid_array = required_param_array('selected_courses', PARAM_INT);
        } catch (Exception $e) {
            dlog($e->getTrace());
            $params = [];
            $base_url = new moodle_url('/local/course_calendar/auto_create_calendar_by_recursive_swap.php', $params);
            redirect($base_url, "You must select one course.", 0, \core\output\notification::NOTIFY_ERROR);
        }

        $time_table = new \local_course_calendar\time_table_generator();
        $time_table = $time_table->create_automatic_calendar_by_recursive_swap_algorithm($courseid_array);

        $cache = \cache::make('local_course_calendar', 'time_table_cache');
        $cache_key = 'user_timetable_' . $USER->id;
        $cache->set($cache_key, $time_table);

    }

    echo $OUTPUT->header();
    // Nội dung trang của bạn
    echo $OUTPUT->box_start();

    // hiển thị trạng thái loading nếu form đang được xử lý
    echo html_writer::start_tag('div', array('id' => 'loading-overlay', 'class' => 'loading-overlay'));
    echo html_writer::start_tag('div', array('class' => 'loading-spinner'));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    $cache = \cache::make('local_course_calendar', 'time_table_cache');
    $cache_key = 'user_timetable_' . $USER->id;
    $time_table = $cache->get($cache_key);

    if ($time_table !== false) {
        $time_table->print_time_table();
    }

    $mform->display();

    echo $OUTPUT->box_end();

    echo $OUTPUT->footer();

} catch (Exception $e) {
    dlog($e->getTrace());

    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";

    throw new \moodle_exception('error', 'local_course_calendar', '', null, $e->getMessage());
}
