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

// Yêu cầu người dùng đăng nhập
require_login();
require_capability('local/course_calendar:view', context_system::instance()); // Kiểm tra quyền truy cập

// Khai báo các biến toàn cục
global $PAGE, $OUTPUT, $DB, $USER;

$context = context_system::instance(); // Lấy ngữ cảnh của trang hệ thống
// Đặt ngữ cảnh trang
$PAGE->set_context($context); 

// Thiết lập trang Moodle
// Đặt URL cho trang hiện tại
$PAGE->set_url(new moodle_url('/local/course_calendar/index.php', [])); 
// Tiêu đề trang
$PAGE->set_title(get_string('course_calendar_title', 'local_course_calendar')); 
$PAGE->set_heading(get_string('course_list', 'local_course_calendar'));


// Thêm một breadcrumb cho các link khác.
$PAGE->navbar->add(get_string('teaching_schedule_assignment', 'local_course_calendar'), new moodle_url('/local/course_calendar/index.php', [])); 
$PAGE->navbar->add(get_string('view_teaching_schedule', 'local_course_calendar'), new moodle_url('/local/course_calendar/course_calendar.php', [])); 
$PAGE->navbar->add(get_string('view_teaching_statistics', 'local_course_calendar'), new moodle_url('/local/course_calendar/course_calendar_statistic.php', [])); 

// Thêm breadcrumb cho trang hiện tại
$PAGE->navbar->add(get_string('course_list', 'local_course_calendar'));


echo $OUTPUT->header();

// Nội dung trang của bạn
echo $OUTPUT->box_start();

echo $OUTPUT->box_end();

echo $OUTPUT->footer();
