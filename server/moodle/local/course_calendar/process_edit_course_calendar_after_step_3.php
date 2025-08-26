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
use local_course_calendar as LocalCourseCalendar;

try {
    // Yêu cầu người dùng đăng nhập
    require_login();
    require_capability('local/course_calendar:edit', context_system::instance()); // Kiểm tra quyền truy cập
    $PAGE->requires->css('/local/course_calendar/style/style.css');
    // Khai báo các biến toàn cục
    global $PAGE, $OUTPUT, $DB, $USER;

    $context = context_system::instance(); // Lấy ngữ cảnh của trang hệ thống
    // Đặt ngữ cảnh trang
    $PAGE->set_context($context);

    // Thiết lập trang Moodle
    // Đặt URL cho trang hiện tại
    $PAGE->set_url(new moodle_url('/local/course_calendar/process_edit_course_calendar_after_step_3.php', []));
    // Tiêu đề trang
    $PAGE->set_title(get_string('teaching_schedule_assignment_processing_title', 'local_course_calendar'));
    $PAGE->set_heading(get_string('teaching_schedule_assignment_processing_heading', 'local_course_calendar'));



    try {
        // courses is array with format [courseid, courseid, courseid,....]
        $courses = required_param('selected_courses', PARAM_INT);
    } catch (Exception $e) {
        dlog($e->getTrace());
        $params = [];
        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_1.php', $params);
        redirect($base_url, "You must select one course.", 0, \core\output\notification::NOTIFY_ERROR);
    }

    try {
        // teachers is array with format [teacherid, teacherid, teacherid,....]
        $teachers = optional_param_array('selected_teachers', [], PARAM_INT);
        if (empty($teachers)) {
            if (isset($SESSION->edit_course_calendar_step_3_form_selected_teachers)) {
                $teachers = $SESSION->edit_course_calendar_step_3_form_selected_teachers;
            } else {
                $params = [];
                if (isset($courses)) {
                    $params['selected_courses'] = $courses;
                }
                $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_2.php', $params);
                redirect($base_url, "You must select at least one teacher.", 0, \core\output\notification::NOTIFY_ERROR);
            }
        } else {
            $SESSION->edit_course_calendar_step_3_form_selected_teachers = $teachers;
        }
    } catch (Exception $e) {
        dlog($e->getTrace());
        $params = [];
        if (isset($courses)) {
            $params['selected_courses'] = $courses;
        }
        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_2.php', $params);
        redirect($base_url, "You must select at least one teacher.", 0, \core\output\notification::NOTIFY_ERROR);
    }

    // room_address is roomid.
    try {
        $room_addresses = required_param('selected_room_addresses', PARAM_INT);
        $SESSION->edit_course_calendar_step_3_form_selected_room_address = $room_addresses;

    } catch (Exception $e) {
        dlog($e->getTrace());
        $params = [];
        if (isset($courses)) {
            $params['selected_courses'] = $courses;
        }

        // if (!empty($teachers) and isset($teachers)) {
        //     foreach ($teachers as $teacherid) {
        //         // Add hidden input for each selected teacher.
        //         $params['selected_teachers[]'] = $teacherid;
        //     }
        // }
        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', $params);
        redirect($base_url, "You must select one room.", 0, \core\output\notification::NOTIFY_ERROR);
    }

    try {
        // start_time and endtime is Unix timestamp. It is an integer number.
        $start_time = required_param('starttime', PARAM_INT);
        $end_time = required_param('endtime', PARAM_INT);
        $SESSION->edit_course_calendar_step_3_form_selected_starttime = $start_time;
        $SESSION->edit_course_calendar_step_3_form_selected_endtime = $end_time;

    } catch (Exception $e) {
        dlog($e->getTrace());
        $params = [];
        if (isset($courses)) {
            $params['selected_courses'] = $courses;
        }

        $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', $params);
        redirect($base_url, "You must select start time and end time and must be press find room button.", 0, \core\output\notification::NOTIFY_ERROR);
    }

    \local_course_calendar\create_manual_calendar($courses, $teachers, $room_addresses, $start_time, $end_time);
    echo $OUTPUT->header();

    echo $OUTPUT->footer();

} catch (Exception $e) {
    dlog($e->getTrace());

    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";

    throw new \moodle_exception('error', 'local_course_calendar', '', null, $e->getMessage());
}
