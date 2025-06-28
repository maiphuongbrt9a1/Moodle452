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

    echo $OUTPUT->header();
    $courses = required_param_array('selected_courses',  PARAM_INT);
    $teachers = required_param_array('selected_teachers',  PARAM_INT);
    $times_and_addresses = required_param_array('selected_times_and_addresses', PARAM_INT);
    // Xử lý dữ liệu từ bước trước

    if ($courses->count() == 1 and $teachers->count() == 1 and $times_and_addresses->count() == 1) {
        $courseid = $courses[0];
        $teacherid = $teachers[0];
        $time_and_address = explode('|', $times_and_addresses[0]);
        $course_schedule_id = $time_and_address[0];
        $course_room_id = $time_and_address[1];
    }
    
    echo $OUTPUT->footer();

} catch (Exception $e) {
    dlog($e->getTrace());
    
    echo "<pre>";
        var_dump($e->getTrace());
    echo "</pre>";
    
    throw new \moodle_exception('error', 'local_course_calendar', '', null, $e->getMessage());
}
