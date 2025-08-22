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

namespace local_course_calendar;

use moodle_url;
use html_writer;
use core\output\pix_icon;
use stdClass;
use Exception;
require_once($CFG->dirroot . '/local/dlog/lib.php');

/**
 * Summary of TIME_ZONE
 * Define the timezone for the course calendar. 'Asia/Ho_Chi_Minh'
 * @var string 
 */
const TIME_ZONE = 'Asia/Ho_Chi_Minh';
/**
 * Summary of MAX_CALENDAR_NUMBER
 * Số lượng thời khóa biểu ban đầu của quần thể
 * @var int 
 */
const MAX_CALENDAR_NUMBER = 500;

/**
 * Summary of MAX_STEP_OF_CROSSOVER_OPERATIONS
 * Số lượng lần lai ghép tối đa trong một thế hệ.
 * @var int 
 */
const MAX_NUMBER_OF_CROSSOVER_OPERATIONS_IN_ONE_GENERATION = 20;

/**
 * Summary of MAX_NUMBER_OF_GENERATION
 * Số lượng thế hệ tối đa có thể lai ghép
 * @var int
 */
const MAX_NUMBER_OF_GENERATION = 20;
/**
 * Summary of MAX_NUMBER_OF_CALL_RECURSIVE
 * Số lần tối đa có thể gọi đệ quy cho thuật toán recursive swap
 * @var int
 */
const MAX_NUMBER_OF_CALL_RECURSIVE = 1000000;
/**
 * Summary of MAX_LEVEL_RECURSIVE
 * Level tối đa còn có thể gọi đệ quy cho giải thuật recursive swap
 * @var int
 */
const MAX_LEVEL_RECURSIVE = 16;
// CÀI ĐẶT THÔNG TIN CẤU HÌNH CHO PLUGIN LOCAL COURSE CALENDAR
// CÀI ĐẶT CÁC LUẬT RÀNG BUỘC CHO XỬ LÝ THỜI KHÓA BIỂU

///////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////OLD: NOT USE: GIẢI THUẬT ĐỂ GIẢI LÀ GIẢI THUẬT DI TRUYỀN////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////

// Quy định 1 tiết là 45 phút
// 1 ca học gồm 2 tiết học, mỗi tiết học là 45 
// Ta cần xếp lớp học - môn học - thời gian - địa điểm trước (Tối ưu lần 1)
// Sau đó ta xếp giảng viên - trợ giảng cho lớp học - môn học này (tối ưu lần 2)
// Có thể sẽ cần tổng hợp các quy tắc ràng buộc để xử lý và tối ưu lần 3 nếu cần thiết.
/**
 * vỚI MỖI một soft rule có ký hiệu S ở đầu thì cần quy định thêm mức độ vi phạm của các quy tắc vì ta cần tối ưu cho các quy tắc này
 * Còn các hard rule có ký hiệu H ở đầu thì không cần vì nếu vi phạm thì ta sẽ loại bỏ kết quả đó ngay lập tức.
 * Các mức độ vi phạm gồm 4 mức đó là rất nghiêm trọng (grave violation) đánh hệ số 4 , nghiêm trọng (serious violation) đánh hệ số 3,
 * mức độ vừa (moderate violation) hệ số 2, mức độ nhẹ (minor violation) hệ số 1 và không vi phạm hệ số là 0. 
 */

/**
 * giờ học buổi sáng chỉ áp dụng cho ngày t7, cn bắt đầu từ 7h30 sáng đến 12h trưa (gồm 3 ca 1h30 phút)
 * buổi chiều bắt đầu từ 1h30 chiều đến 4h30 chiều (gồm 2 ca 1h30 phút)
 * buổi tối bắt đầu lúc 5h30 chiều đến 22h tối (gồm 3 ca 1h30 phút)
 * Có một tiếng nghỉ 4h30 đến 5h30 cho giảng viên nghỉ ăn tối
 * có 1h30 phút nghỉ trưa từ 12h trưa đến 13h30 phút chiều.

 * Tổng ngày t7 cn có tất cả 8 ca dạy
 * ngày t2 - t6 có 5 ca dạy
 */


/**
 * Summary of DATES
 * define date of week ['monday' = 0, 'tuesday' = 1, 'wednesday' = 2, 'thursday' = 3, 'friday' = 4, 'saturday' = 5, 'sunday' = 6]
 * @var array 
 */
const DATES = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];


/**
 * Summary of AVAILABLE_CLASS_SESSIONS
 * define available class sesstions ['7:30','8:15','9:00', '9:45', '10:30', '11:15', '13:30', '14:15', '15:00', '15:45', '17:30', '18:15', '19:00', '19:45', '20:30', '21:15']
 * @var array 
 */
const AVAILABLE_CLASS_SESSIONS = [
  '7:30',
  '8:15',
  '9:00',
  '9:45',
  '10:30',
  '11:15',
  '13:30',
  '14:15',
  '15:00',
  '15:45',
  '17:30',
  '18:15',
  '19:00',
  '19:45',
  '20:30',
  '21:15'
];
/**
 * STT Tiết | Khung giờ |         Buổi ||
 *Tiết 0            7h30 - 8h15       Buổi Sáng ||
 *Tiết 1            8:15 - 9:00       Buổi Sáng ||
 *Tiết 2            9:00 - 9:45       Buổi Sáng ||
 *Tiết 3            9:45 - 10:30       Buổi Sáng ||
 *Tiết 4            10:30 - 11:15       Buổi Sáng ||
 *Tiết 5            11:15 - 12:00       Buổi Sáng ||
 *Tiết 6            13:30 - 14:15       Buổi Chiều ||
 *Tiết 7            14:15 - 15:00       Buổi Chiều ||
 *Tiết 8            15:00 - 15:45       Buổi Chiều ||
 *Tiết 9            15:45 - 16:30       Buổi Chiều ||
 *Tiết 10             17:30 - 18:15       Buổi Tối ||
 *Tiết 11            18:15 - 19:00       Buổi Tối ||
 *Tiết 12            19:00 - 19:45       Buổi Tối ||
 *Tiết 13            19:45 - 20:30       Buổi Tối ||
 *Tiết 14            20:30 - 21:15       Buổi Tối ||
 *Tiết 15           21:15 - 22:00       Buổi Tối
 */
const STT_CLASS_SESSIONS = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15];
const START_MORNING = STT_CLASS_SESSIONS[0];
const END_MORNING = STT_CLASS_SESSIONS[6];
const START_AFTERNOON = STT_CLASS_SESSIONS[6];
const END_AFTERNOON = STT_CLASS_SESSIONS[10];
const START_EVENING = STT_CLASS_SESSIONS[10];
const END_EVENING = STT_CLASS_SESSIONS[15] + 1;

/**
 * Summary of TIME_SLOT_DURATION
 * Thời gian mỗi tiết học là 45 phút
 * @var int 
 */
const TIME_SLOT_DURATION = 45 * 60;

/**
 * Summary of CLASS_DURATION
 * Thời gian mỗi ca học là 90 phút (2 tiết học x 45 phút)
 * @var int 
 */
const CLASS_DURATION = 90 * 60;

const MAX_CLASS_DURATION = 5;
/**
 * Summary of number_course_session_weekly
 * @var int
 */
const NUMBER_COURSE_SESSION_WEEKLY = 2; // 2 SESSION

const TIME_GAP_BETWEEN_COURSE_SESSION_OF_SAME_COURSE = 2; // 2 DAYS

const TOTAL_COURSE_SESSION_OF_COURSE = 15; // 15 SESSIONS

const NUMBER_STUDENT_ON_COURSE = 25; // 25 STUDENTS

const CLASS_DURATION_OF_COURSE_SESSION_OF_COURSE = 2; // 2 SESSIONS

const TIME_GAP_FOR_GOTO_CLASS_BETWEEN_TWO_PHYSICAL_ADDRESS = 2; // 2 SESSIONS
class course_session_information
{
  public $courseid;
  public $course_name;
  public $course_session_length;
  public $course_session_start_time;
  public $course_session_end_time;
  public $editting_teacher_array;
  public $non_editting_teacher_array;
  public $date;
  public $random_room_stt;
  public $room;
  public $room_number;
  public $floor;
  public $building;
  public $ward;
  public $district;
  public $province;
  public $total_number_course_section;
  public $number_course_session_weekly;
  public $stt_course;
  public $first_put_successfully_in_holiday_flag;
  public $first_put_successfully_in_is_not_allow_change_session_flag;
  public $time_gap_to_skip_holiday_and_goto_next_course_session;
  public function __construct(
    $courseid = null,
    $course_name = null,
    $course_session_length = null,
    $course_session_start_time = null,
    $course_session_end_time = null,
    $editting_teacher_array = null,
    $non_editting_teacher_array = null,
    $date = null,
    $random_room_stt = null,
    $room = null,
    $floor = null,
    $building = null,
    $ward = null,
    $district = null,
    $province = null,
    $total_number_course_section = null,
    $number_course_session_weekly = null,
    $stt_course = null,
    $room_number = null,
    $first_put_successfully_in_holiday_flag = null,
    $first_put_successfully_in_is_not_allow_change_session_flag = null,
    $time_gap_to_skip_holiday_and_goto_next_course_session = null,
  ) {
    $this->courseid = $courseid;
    $this->course_name = $course_name;
    $this->course_session_length = $course_session_length;
    $this->course_session_start_time = $course_session_start_time;
    $this->course_session_end_time = $course_session_end_time;
    $this->editting_teacher_array = $editting_teacher_array;
    $this->non_editting_teacher_array = $non_editting_teacher_array;
    $this->date = $date;
    $this->random_room_stt = $random_room_stt;
    $this->room = $room;
    $this->floor = $floor;
    $this->building = $building;
    $this->ward = $ward;
    $this->district = $district;
    $this->province = $province;
    $this->total_number_course_section = $total_number_course_section;
    $this->number_course_session_weekly = $number_course_session_weekly;
    $this->stt_course = $stt_course;
    $this->room_number = $room_number;
    $this->first_put_successfully_in_holiday_flag = $first_put_successfully_in_holiday_flag;
    $this->first_put_successfully_in_is_not_allow_change_session_flag = $first_put_successfully_in_is_not_allow_change_session_flag;
    $this->time_gap_to_skip_holiday_and_goto_next_course_session = $time_gap_to_skip_holiday_and_goto_next_course_session;
  }

  public function set_value(
    $courseid = null,
    $course_name = null,
    $course_session_length = null,
    $course_session_start_time = null,
    $course_session_end_time = null,
    $editting_teacher_array = null,
    $non_editting_teacher_array = null,
    $date = null,
    $random_room_stt = null,
    $room = null,
    $floor = null,
    $building = null,
    $ward = null,
    $district = null,
    $province = null,
    $total_number_course_section = null,
    $number_course_session_weekly = null,
    $stt_course = null,
    $room_number = null,
    $first_put_successfully_in_holiday_flag = null,
    $first_put_successfully_in_is_not_allow_change_session_flag = null,
    $time_gap_to_skip_holiday_and_goto_next_course_session = null,
  ) {
    $this->courseid = $courseid;
    $this->course_name = $course_name;
    $this->course_session_length = $course_session_length;
    $this->course_session_start_time = $course_session_start_time;
    $this->course_session_end_time = $course_session_end_time;
    $this->editting_teacher_array = $editting_teacher_array;
    $this->non_editting_teacher_array = $non_editting_teacher_array;
    $this->date = $date;
    $this->random_room_stt = $random_room_stt;
    $this->room = $room;
    $this->floor = $floor;
    $this->building = $building;
    $this->ward = $ward;
    $this->district = $district;
    $this->province = $province;
    $this->total_number_course_section = $total_number_course_section;
    $this->number_course_session_weekly = $number_course_session_weekly;
    $this->stt_course = $stt_course;
    $this->room_number = $room_number;
    $this->first_put_successfully_in_holiday_flag = $first_put_successfully_in_holiday_flag;
    $this->first_put_successfully_in_is_not_allow_change_session_flag = $first_put_successfully_in_is_not_allow_change_session_flag;
    $this->time_gap_to_skip_holiday_and_goto_next_course_session = $time_gap_to_skip_holiday_and_goto_next_course_session;
  }

  public function get_copy()
  {
    $clone = new course_session_information(
      $this->courseid,
      $this->course_name,
      $this->course_session_length,
      $this->course_session_start_time,
      $this->course_session_end_time,
      $this->editting_teacher_array,
      $this->non_editting_teacher_array,
      $this->date,
      $this->random_room_stt,
      $this->room,
      $this->floor,
      $this->building,
      $this->ward,
      $this->district,
      $this->province,
      $this->total_number_course_section,
      $this->number_course_session_weekly,
      $this->stt_course,
      $this->room_number,
      $this->first_put_successfully_in_holiday_flag,
      $this->first_put_successfully_in_is_not_allow_change_session_flag,
      $this->time_gap_to_skip_holiday_and_goto_next_course_session,
    );
    return $clone;
  }
}

class time_slot
{
  public $room;
  public $date;
  public $session;
  public $course_session_information;
  public $time_slot_index;
  public $is_occupied;
  public $is_occupied_by_course_in_prev_time_slot;
  public $is_not_allow_change;
  public $room_number;
  public $floor;
  public $building;
  public $ward;
  public $district;
  public $province;
  public function __construct(
    $room = null,
    $date = null,
    $session = null,
    $course_session_information = null,
    $time_slot_index = null,
    $is_occupied = null,
    $is_occupied_by_course_in_prev_time_slot = null,
    $is_not_allow_change = null,
    $room_number = null,
    $floor = null,
    $building = null,
    $ward = null,
    $district = null,
    $province = null,

  ) {
    $this->room = $room;
    $this->date = $date;
    $this->session = $session;
    $this->course_session_information = $course_session_information;
    $this->time_slot_index = $time_slot_index;
    $this->is_occupied = $is_occupied;
    $this->is_occupied_by_course_in_prev_time_slot = $is_occupied_by_course_in_prev_time_slot;
    $this->is_not_allow_change = $is_not_allow_change;
    $this->room_number = $room_number;
    $this->floor = $floor;
    $this->building = $building;
    $this->ward = $ward;
    $this->district = $district;
    $this->province = $province;
  }

  public function set_value(
    $room = null,
    $date = null,
    $session = null,
    $course_session_information = null,
    $time_slot_index = null,
    $is_occupied = null,
    $is_occupied_by_course_in_prev_time_slot = null,
    $is_not_allow_change = null,
    $room_number = null,
    $floor = null,
    $building = null,
    $ward = null,
    $district = null,
    $province = null,

  ) {
    $this->room = $room;
    $this->date = $date;
    $this->session = $session;
    $this->course_session_information = $course_session_information;
    $this->time_slot_index = $time_slot_index;
    $this->is_occupied = $is_occupied;
    $this->is_occupied_by_course_in_prev_time_slot = $is_occupied_by_course_in_prev_time_slot;
    $this->is_not_allow_change = $is_not_allow_change;
    $this->room_number = $room_number;
    $this->floor = $floor;
    $this->building = $building;
    $this->ward = $ward;
    $this->district = $district;
    $this->province = $province;
  }

  public function get_copy()
  {
    $clone = new time_slot(
      $this->room,
      $this->date,
      $this->session,
      !empty($this->course_session_information) ? $this->course_session_information->get_copy() : null,
      $this->time_slot_index,
      $this->is_occupied,
      $this->is_occupied_by_course_in_prev_time_slot,
      $this->is_not_allow_change,
      $this->room_number,
      $this->floor,
      $this->building,
      $this->ward,
      $this->district,
      $this->province,

    );

    return $clone;
  }
}

class conflict_position
{
  public $room;
  public $date;
  public $session;
  public $time_slot_index;
  public $conflict_items_number_at_this_time_slot;
  public $conflict_items_array;

  public function __construct(
    $room = null,
    $date = null,
    $session = null,
    $time_slot_index = null,
    $conflict_items_number_at_this_time_slot = null,
    $conflict_items_array = null
  ) {
    $this->room = $room;
    $this->date = $date;
    $this->session = $session;
    $this->time_slot_index = $time_slot_index;
    $this->conflict_items_number_at_this_time_slot = $conflict_items_number_at_this_time_slot;
    $this->conflict_items_array = $conflict_items_array;
  }

  public function set_value(
    $room = null,
    $date = null,
    $session = null,
    $time_slot_index = null,
    $conflict_items_number_at_this_time_slot = null,
    $conflict_items_array = null
  ) {
    $this->room = $room;
    $this->date = $date;
    $this->session = $session;
    $this->time_slot_index = $time_slot_index;
    $this->conflict_items_number_at_this_time_slot = $conflict_items_number_at_this_time_slot;
    $this->conflict_items_array = $conflict_items_array;
  }

  public function get_copy()
  {
    $conflict_items_array = [];
    foreach ($this->conflict_items_array as $item) {
      $conflict_items_array[] = $item;
    }

    $clone = new conflict_position(
      $this->room,
      $this->date,
      $this->session,
      $this->time_slot_index,
      $this->conflict_items_number_at_this_time_slot,
      $conflict_items_array
    );
    return $clone;
  }
}

function deep_copy_calendar_array($calendar_array)
{
  $clone = array();
  $number_room = count($calendar_array);
  $number_day = count(DATES);
  $number_class_sessions = count(AVAILABLE_CLASS_SESSIONS);

  for ($i = 0; $i < $number_room; $i++) {
    $clone[] = [];
    for ($j = 0; $j < $number_day; $j++) {
      $clone[$i][] = [];
      for ($k = 0; $k < $number_class_sessions; $k++) {
        $clone[$i][$j][] = new course_session_information
        (
          $calendar_array[$i][$j][$k]->courseid,
          $calendar_array[$i][$j][$k]->course_name,
          $calendar_array[$i][$j][$k]->course_session_length,
          $calendar_array[$i][$j][$k]->course_session_start_time,
          $calendar_array[$i][$j][$k]->course_session_end_time,
          $calendar_array[$i][$j][$k]->editting_teacher_array,
          $calendar_array[$i][$j][$k]->non_editting_teacher_array,
          $calendar_array[$i][$j][$k]->date,
          $calendar_array[$i][$j][$k]->random_room_stt,
          $calendar_array[$i][$j][$k]->room,
          $calendar_array[$i][$j][$k]->floor,
          $calendar_array[$i][$j][$k]->building,
          $calendar_array[$i][$j][$k]->ward,
          $calendar_array[$i][$j][$k]->district,
          $calendar_array[$i][$j][$k]->province
        );

      }
    }
  }
  return $clone;
}

function deep_copy_calendar_community($calendar_community)
{
  $clone = [];
  $number_calendar = count($calendar_community);
  for ($i = 0; $i < $number_calendar; $i++) {
    $clone[] = deep_copy_calendar_array($calendar_community[$i]);
  }

  return $clone;
}
// BÊN DƯỚI LÀ CÁC RÀNG BUỘC VỀ THỜI GIAN CỦA LỚP HỌC
/**
 * Summary of UT_HT1
 * HT1. Các lớp - môn học phải được dạy trọn vẹn trong một buổi của một ngày trong tuần 
 * (một Lớp Môn học không được cắt ra thành các tiết cuối buổi sáng và đầu buổi chiều hay 
 * cuối ngày này và đầu buổi sáng hôm sau).
 * @var int
 */
const UT_HT1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT1
/**
 * Summary of is_class_duration
 * HT1. Các lớp - môn học phải được dạy trọn vẹn trong một buổi của một ngày trong tuần 
 * (một Lớp Môn học không được cắt ra thành các tiết cuối buổi sáng và đầu buổi chiều 
 * hay cuối ngày này và đầu buổi sáng hôm sau).
 * @param mixed $class_start_time Start time of class
 * @return boolean true if start time and end time are in one session (Morning session/ Afternoon session or evening session)
 */
function is_class_duration_in_one_session($class_start_time, $class_duration = CLASS_DURATION / TIME_SLOT_DURATION)
{
  $class_end_time = (int) $class_start_time + (int) $class_duration;

  $start_morning = START_MORNING;
  $end_morning = END_MORNING;

  $start_afternoon = START_AFTERNOON;
  $end_afternoon = END_AFTERNOON;

  $start_everning = START_EVENING;
  $end_everning = END_EVENING;

  if ($class_start_time >= $start_morning and $class_end_time < $end_morning) {
    return true;
  } else if ($class_start_time >= $start_afternoon and $class_end_time < $end_afternoon) {
    return true;
  } else if ($class_start_time >= $start_everning and $class_end_time <= $end_everning) {
    return true;
  } else {
    return false;
  }
}

function compute_score_violation_of_rule_class_duration_in_one_session($class_start_time, $class_duration = CLASS_DURATION / TIME_SLOT_DURATION)
{
  $total_score_violation = 0;
  if (is_class_duration_in_one_session($class_start_time, $class_duration)) {
    return 0;
  } else {
    $total_score_violation += UT_HT1;
  }

  return $total_score_violation;
}
/*
 *  HT2. Số lớp - môn học được quy định tránh
 không được xếp vào một số tiết học cụ thể vào trước 17h30 phút các ngày từ thứ 2 đến thứ 6 
 và thời điểm kết thúc của môn học cuối cùng là vào 22h. 
 t2 - t6 (17h30 - 22h)
*/
const UT_HT2 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT2
/**
 * Summary of is_forbidden_session
 * HT2. Số lớp - môn học được quy định tránh không được xếp vào một số tiết học cụ thể vào trước 17h30 phút các ngày từ thứ 2 đến thứ 6 và thời điểm kết thúc của môn học cuối cùng là vào 22h t2 - t6 (17h30 - 22h)
 * @param mixed $class_start_time start time of class
 * @param mixed $class_duration durating time of class
 * @return boolean true if is forbidden class sesstion
 */
function is_forbidden_session($date, $class_start_time, $class_duration = CLASS_DURATION / TIME_SLOT_DURATION)
{
  $class_end_time = (int) $class_start_time + (int) $class_duration;

  $start_everning = START_EVENING;
  $end_everning = END_EVENING;

  // ngày được xếp thep thứ tự từ 0->6 [0: mon, 1: tue, 2:wed, 3:thu, 4: fri, 5: sat, 6:sun]
  if (
    $date === 0
    or $date === 1
    or $date === 2
    or $date === 3
    or $date === 4

  ) {
    if ($class_start_time >= $start_everning and $class_end_time <= $end_everning) {
      return false;
    }

    return true;

  }

  return !is_class_duration_in_one_session($class_start_time, $class_duration);

}

function compute_score_violation_of_rule_forbidden_session($date, $class_start_time, $class_duration = CLASS_DURATION / TIME_SLOT_DURATION)
{
  $total_score_violation = 0;
  if (is_forbidden_session($date, $class_start_time, $class_duration)) {
    $total_score_violation += UT_HT2;
  }

  return $total_score_violation;
}
/** 
 * đã hoàn thành ở HT2.
 * không cần kiểm tra lại.
 *  HT3. Số lớp - môn học được quy định được xếp vào một số tiết học cụ thể nào đó.
 * Đó là vào 7h30 sáng các ngày  thứ 7, chủ nhật và thời điểm kết thúc của môn học cuối cùng là 22h.
 * t7, cn (7h30 - 22h)
 */
const UT_HT3 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT3

/**
 * HT4 Thời gian của một lớp học không được trùng vào thời gian nghỉ lễ lớn được quy định trong năm
 * như Tết Nguyên Đán, Giỗ Tổ Hùng Vương, Quốc Khánh, v.v.
 * Các ngày nghỉ lễ này thường được quy định trong lịch học của trung tâm giáo dục hoặc trường học.
 */
const UT_HT4 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT4
/**
 * Summary of is_holiday.
 * HT4 Thời gian của một lớp học không được trùng vào thời gian nghỉ lễ lớn được quy định trong năm
 * như Tết Nguyên Đán, Giỗ Tổ Hùng Vương, Quốc Khánh, v.v.
 * Các ngày nghỉ lễ này thường được quy định trong lịch học của trung tâm giáo dục hoặc trường học.
 * @param mixed $date class date
 * @return bool true if this is holiday else false
 */
function is_holiday($date)
{
  // global $DB;
  // $holiday_records = $DB->get_records('local_course_calendar_holiday');
  // if (empty($holiday_records)) {
  //   return false;
  // } else {
  //   foreach ($holiday_records as $holiday) {
  //     if (date('d-m-Y', $date) === date('d-m-Y', $holiday)) {
  //       return true;
  //     }
  //   }
  // }
  return false;
}

function compute_score_violation_of_rule_holiday($date)
{
  if (is_holiday($date)) {
    return UT_HT4;
  }

  return 0;
}
/**
 * HT5 Thời gian học của một lớp học không được vượt quá 1h30 phút liên tục.
 * Điều này có nghĩa là mỗi buổi học không được kéo dài quá 1h30 phút liên tục.
 * Sau mỗi 1h30 phút học, cần có thời gian nghỉ ngơi hoặc chuyển sang môn học khác.
 */
const UT_HT5 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT5
/**
 * Summary of is_class_overtime
 * HT5 Thời gian học của một lớp học không được vượt quá 1h30 phút liên tục.
 * Điều này có nghĩa là mỗi buổi học không được kéo dài quá 1h30 phút liên tục.
 * Sau mỗi 1h30 phút học, cần có thời gian nghỉ ngơi hoặc chuyển sang môn học khác. 
 * @param mixed $class_start_time class start time
 * @param mixed $class_end_time class end time
 * @param mixed $system_class_duration contraint class duration time ((class end time - class start time) <= system class duration )
 * @return boolean true if (class end time - class start time) >= system class duration else false
 */
function is_class_overtime($class_start_time, $class_end_time, $system_class_duration = CLASS_DURATION / TIME_SLOT_DURATION)
{
  if ((int) ($class_end_time - $class_start_time + 1) > (int) $system_class_duration) {
    return true;
  }

  return false;
}

function compute_score_violation_of_rule_class_overtime($class_start_time, $class_end_time, $system_class_duration = CLASS_DURATION / TIME_SLOT_DURATION)
{
  if (is_class_overtime($class_start_time, $class_end_time, $system_class_duration)) {
    return UT_HT5;
  }

  return 0;
}
/**
 * HT6 Phải tổ chức lớp học đủ số buổi trên tuần tuân theo quy tắc hợp đồng
 * vd môn A học 3 buổi trên 1 tuần thì mỗi tuần phải học đủ 3 buổi.
 * đối với các môn học 2 buổi trên tuần thì tương tự.
 */
const UT_HT6 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT6
/**
 * Summary of is_number_of_course_session_weekly
 * HT6 Phải tổ chức lớp học đủ số buổi trên tuần tuân theo quy tắc hợp đồng
 * vd môn A học 3 buổi trên 1 tuần thì mỗi tuần phải học đủ 3 buổi.
 * đối với các môn học 2 buổi trên tuần thì tương tự.
 * @param mixed $calendar calendar need check 
 * @param mixed $course_id_param course id need check enough session
 * @param mixed $number_course_session_weekly number course session must be taught by teacher on each week. 
 * @return bool true if not enough number course sessions weekly else false.
 */
function is_not_enough_number_of_course_session_weekly($calendar, $course_id_param, $number_course_session_weekly = NUMBER_COURSE_SESSION_WEEKLY)
{
  $number_course_sessions = 0;
  //$calendar[room-ith][date-jth][session-kth] = course_session_information object;
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      for ($k = 0; $k < $number_session; $k++) {
        if (!empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)) {
          if ($course_id_param == $calendar[$i][$j][$k]->courseid) {
            $number_course_sessions++;
          }
        }
      }
    }
  }

  if ($number_course_sessions == $number_course_session_weekly) {
    return false;
  }

  return true;
}

function compute_score_violation_of_rule_not_enough_number_of_course_session_weekly($calendar, $course_id_param, $number_course_session_weekly = NUMBER_COURSE_SESSION_WEEKLY)
{
  if (is_not_enough_number_of_course_session_weekly($calendar, $course_id_param, $number_course_session_weekly)) {
    return UT_HT6;
  }

  return 0;
}


/**
 * Summary of UT_HT7
 * @var int Không cho phép việc một course triển khai khóa học cả hai buổi một ngày
 */
const UT_HT7 = 1000000000;
function is_study_double_session_of_same_course_on_one_day($calendar, $courseid)
{
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      for ($k = 0; $k < $number_session; $k++) {
        for ($m = $k + 1; $m < $number_session; $m++) {
          if (
            !empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)
            && !empty($calendar[$i][$j][$m]) && isset($calendar[$i][$j][$m]->courseid)
          ) {
            if ($calendar[$i][$j][$k]->courseid == $calendar[$i][$j][$m]->courseid) {
              return true;
            }
          }
        }
      }
    }
  }

  return false;
}

function compute_score_violation_of_rule_study_double_session_of_same_course_on_one_day($calendar, $courseid)
{
  if (is_study_double_session_of_same_course_on_one_day($calendar, $courseid)) {
    return UT_HT7;
  }

  return 0;
}

// Các ràng buộc cứng về không gian của lớp học
/**
 * HP1 Tại mỗi thời điểm một phòng học chỉ được sử dụng cho một lớp - môn học.
 */
const UT_HP1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HP1
/**
 * Summary of is_duplicate_course_at_same_room_at_same_time
 * @param mixed $calendar calendar format is $calendar[ith-room][jth-date][kth-session] = object course_session_information
 * @param mixed $room
 * @param mixed $date
 * @param mixed $class_start_time
 * @return bool
 */
function is_duplicate_course_at_same_room_at_same_time($calendar, $room, $date, $class_start_time)
{
  $course_session_information = $calendar[$room][$date][$class_start_time];

  // ngay tại ô room - date - class_start_time đã có course
  if (!empty($course_session_information) && isset($course_session_information->courseid)) {
    return true;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó có course và class_duration của course session đó là 2
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 1]->courseid)
    && $calendar[$room][$date][$class_start_time - 1]->course_session_length == 2
  ) {
    return true;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó 2 ô có course và class_duration của course session đó là 3
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 2]->courseid)
    && $calendar[$room][$date][$class_start_time - 2]->course_session_length == 3
  ) {
    return true;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó 3 ô có course và class_duration của course session đó là 4
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 3]->courseid)
    && $calendar[$room][$date][$class_start_time - 3]->course_session_length == 4
  ) {
    return true;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó 4 ô có course và class_duration của course session đó là 5
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 4]->courseid)
    && $calendar[$room][$date][$class_start_time - 4]->course_session_length == 5
  ) {
    return true;
  }

  return false;
}

function compute_score_violation_of_rule_duplicate_course_at_same_room_at_same_time($calendar, $room, $date, $class_start_time)
{
  if (is_duplicate_course_at_same_room_at_same_time($calendar, $room, $date, $class_start_time)) {
    return UT_HP1;
  }

  return 0;
}

function get_conflict_item_of_rule_duplicate_course_at_same_room_at_same_time(
  $calendar,
  $time_slot
) {

  $room = $time_slot->room;
  $date = $time_slot->date;
  $class_start_time = $time_slot->session;
  $course_session_information = $calendar[$room][$date][$class_start_time];
  $conflict_position_array = [];

  // ngay tại ô room - date - class_start_time đã có course
  if (!empty($course_session_information) && isset($course_session_information->courseid)) {
    $conflict_items_array = [];
    $conflict_items_array[] = $course_session_information->courseid;
    $conflict_items_number = count($conflict_items_array);

    $conflict_position_array[] = new conflict_position(
      $room,
      $date,
      $class_start_time,
      $time_slot->time_slot_index,
      $conflict_items_number,
      $conflict_items_array
    );
    return $conflict_position_array;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó có course và class_duration của course session đó là 2
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 1]->courseid)
    && $calendar[$room][$date][$class_start_time - 1]->course_session_length == 2
  ) {
    $conflict_items_array = [];
    $conflict_items_array[] = $calendar[$room][$date][$class_start_time - 1]->courseid;
    $conflict_items_number = count($conflict_items_array);

    $conflict_position_array[] = new conflict_position(
      $room,
      $date,
      $class_start_time,
      $time_slot->time_slot_index,
      $conflict_items_number,
      $conflict_items_array
    );
    return $conflict_position_array;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó 2 ô có course và class_duration của course session đó là 3
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 2]->courseid)
    && $calendar[$room][$date][$class_start_time - 2]->course_session_length == 3
  ) {
    $conflict_items_array = [];
    $conflict_items_array[] = $calendar[$room][$date][$class_start_time - 2]->courseid;
    $conflict_items_number = count($conflict_items_array);

    $conflict_position_array[] = new conflict_position(
      $room,
      $date,
      $class_start_time,
      $time_slot->time_slot_index,
      $conflict_items_number,
      $conflict_items_array
    );
    return $conflict_position_array;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó 3 ô có course và class_duration của course session đó là 4
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 3]->courseid)
    && $calendar[$room][$date][$class_start_time - 3]->course_session_length == 4
  ) {
    $conflict_items_array = [];
    $conflict_items_array[] = $calendar[$room][$date][$class_start_time - 3]->courseid;
    $conflict_items_number = count($conflict_items_array);

    $conflict_position_array[] = new conflict_position(
      $room,
      $date,
      $class_start_time,
      $time_slot->time_slot_index,
      $conflict_items_number,
      $conflict_items_array
    );
    return $conflict_position_array;
  }

  // ngay tại ô room - date - class_start_time không có course 
  // nhưng ô liền trước nó 4 ô có course và class_duration của course session đó là 5
  if (
    !empty($course_session_information) && isset($calendar[$room][$date][$class_start_time - 4]->courseid)
    && $calendar[$room][$date][$class_start_time - 4]->course_session_length == 5
  ) {
    $conflict_items_array = [];
    $conflict_items_array[] = $calendar[$room][$date][$class_start_time - 4]->courseid;
    $conflict_items_number = count($conflict_items_array);

    $conflict_position_array[] = new conflict_position(
      $room,
      $date,
      $class_start_time,
      $time_slot->time_slot_index,
      $conflict_items_number,
      $conflict_items_array
    );
    return $conflict_position_array;
  }

  return $conflict_position_array;
}


/**
 * HP2 sĩ số của một lớp - môn học không được vượt quá sĩ số tối đa của phòng học. mặc định là 25 học sinh một phòng học.
 */
const MAX_STUDENT_OF_COURSE = 25;
const UT_HP2 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HP2

// Các ràng buộc cứng về giảng viên của lớp học
/**
 * HG1 Mỗi lớp - môn học phải được phân công tối thiểu cho một giảng viên duy nhất. và tối đa là hai giảng viên.
 * Điều này có nghĩa là mỗi lớp học phải có ít nhất một giảng viên phụ trách, và không được có quá hai giảng viên cùng dạy một lớp học.
 * Và thời khóa biểu của hai giảng viên này phải không bị trùng lặp trong cùng một thời gian.
 * và thời gian giảng dạy của hai giảng viên được chia theo buổi học 
 * có nghĩa là vd một môn học của một lớp có 15 buổi dạy thì 
 * giảng viên 1 có thể dạy 8 buổi và giảng viên 2 có thể dạy 7 buổi.
 * và các buổi này không được trùng lặp với nhau.
 */
const UT_HG1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HG1

/*
 * HG2: GIẢNG VIÊN DẠY MÔN NÀO THÌ PHẢI CÓ CHUYÊN MÔN ĐÓ
 * Mặc định chuyên môn giảng dạy của giảng viên là tên của loại danh mục khóa học mà giảng viên giảng dạy
 * Vd môn kỹ thuật lập trình, công nghệ phần mềm, quản trị quy trình phát triển phần mềm, v.v. đều thuộc danh mục công nghệ thông tin
 * vậy các giảng viên giảng dạy các môn học này đều có chuyên môn là công nghệ thông tin.
 */
const UT_HG2 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HG2

/**
 * HG3: Mỗi lớp học - môn học phải được phân công tối thiểu cho một trợ giảng. và tối đa là hai trợ giảng.
 */
const UT_HG3 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HG3

// Các ràng buộc mềm về thời gian của lớp học
/**
 * ST1: Thời khóa biểu của một sinh viên nên hạn chế các ngày học cả 2 buổi (sáng tới chiều)
 */
const UT_ST1 = 1000; // ĐIỂM ƯU TIÊN cho ràng buộc ST1

// ĐÁNH HỆ SỐ CHO VI PHẠM
/**
 * Summary of VP_ST1_GRAVE_VIOLATION
 * học cả hai buổi sáng chiều t7 và có nhiều hơn một buổi chiều của ngày (trong t2-t6)
 * hoặc học cả hai buổi sáng chiều cn và có nhiều hơn một buổi chiều của ngày (trong t2-t6)
 * @var int
 */
const VP_ST1_GRAVE_VIOLATION = 4;
// học cả hai buổi sáng chiều t7 
// hoặc
// học cả hai buổi sáng chiều cn 
const VP_ST1_SERIOUS_VIOLATION = 3;

const VP_ST1_MODERATE_VIOLATION = 2;
// học cả chiều t7 và sáng cn
const VP_ST1_MINOR_VIOLATION = 1;
// không có vi phạm
const VP_ST1_NO_VIOLATION = 0;
/**
 * Summary of compute_score_violation_of_rule_student_study_all_day
 * @param mixed $calendar
 * @return int return violation score for rule ST1: Thời khóa biểu của một sinh viên nên hạn chế các ngày học cả 2 buổi (sáng tới chiều)
 */
function compute_score_violation_of_rule_student_study_all_day($calendar)
{
  global $DB;
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $total_score_violation = 0;

  $users_join_course_sql = "SELECT
                          CONCAT(user.id, c.id) id,
                          user.id userid, 
                          user.firstname user_firstname, 
                          user.lastname user_lastname
                        FROM {user} user 
                        join {role_assignments} ra on ra.userid = user.id
                        join {role} r on r.id = ra.roleid
                        join {context} ctx on ctx.id = ra.contextid
                        join {course} c on c.id = ctx.instanceid 
                        WHERE c.id != 1 
                              and ctx.contextlevel = 50
                              and r.shortname = 'student'
                        GROUP BY user.id  
                        ORDER BY 
                                user.id,
                                user.firstname, 
                                user.lastname ASC";
  $params = [];
  $users = $DB->get_records_sql($users_join_course_sql, $params);

  if (empty($users)) {
    return $total_score_violation;
  }

  foreach ($users as $user) {
    $course_sql = "SELECT
                        CONCAT(user.id, c.id) id,
                        user.id userid, 
                        user.firstname user_firstname, 
                        user.lastname user_lastname, 
                        c.id courseid, 
                        c.fullname course_fullname
                    FROM mdl_user user 
                    join mdl_role_assignments ra on ra.userid = user.id
                    join mdl_role r on r.id = ra.roleid
                    join mdl_context ctx on ctx.id = ra.contextid
                    join mdl_course c on c.id = ctx.instanceid 
                    WHERE c.id != 1 
                        and ctx.contextlevel = 50
                        and r.shortname = 'student'
                        and user.id = :userid

                    ORDER BY 
                            c.id, 
                            user.id,
                            user.firstname, 
                            user.lastname ASC";
    $params = ['userid' => $user->userid];
    $courses = $DB->get_records_sql($course_sql, $params);

    $user_study_sesstion_on_t2_t6 = 0;
    $is_study_saturday_morning = false;
    $is_study_saturday_afternoon = false;
    $is_study_sunday_morning = false;
    $is_study_sunday_afternoon = false;

    foreach ($courses as $course) {
      // Scan T2 -> T6 find tìm buổi học của người dùng này nếu có.
      // nếu tìm được một buổi học trong khoảng t2 -> t6 thì ghi nhận tổng số buổi học đó
      for ($i = 0; $i < $number_room; $i++) {
        // tìm trong khoảng t2 đến t6 chỉ gồm 5 ngày.
        // chú ý là buổi sáng đã bị cấm nhưng ở đây vẫn cần kiểm tra luôn.
        for ($j = 0; $j < $number_day - 2; $j++) {
          for ($k = 0; $k < $number_session; $k++) {
            if (!empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)) {
              if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
                $user_study_sesstion_on_t2_t6++;
              }
            }
          }
        }
      }
      // Kiểm tra xem người này có học cả buổi sáng và buổi chiều của ngày t7 hoặc cn
      // Kiểm tra cho buổi sáng.
      for ($i = 0; $i < $number_room; $i++) {
        // tìm trong khoảng t7 đến cn chỉ gồm 2 ngày.
        for ($j = $number_day - 2; $j < $number_day; $j++) {
          for ($k = 0; $k < STT_CLASS_SESSIONS[6]; $k++) {
            if (!empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)) {
              if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
                if ($j == 5) {
                  $is_study_saturday_morning = true;
                } else {
                  $is_study_sunday_morning = true;
                }
              }
            }
          }
        }
      }

      // Kiểm tra cho buổi chiều.
      for ($i = 0; $i < $number_room; $i++) {
        // tìm trong khoảng t7 đến cn chỉ gồm 2 ngày.
        for ($j = $number_day - 2; $j < $number_day; $j++) {
          for ($k = STT_CLASS_SESSIONS[6]; $k < STT_CLASS_SESSIONS[10]; $k++) {
            if (!empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)) {
              if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
                if ($j == 5) {
                  $is_study_saturday_afternoon = true;
                } else {
                  $is_study_sunday_afternoon = true;
                }
              }
            }
          }
        }
      }
      // kiểm tra đk học cả hai buổi sáng chiều ngày t7 / cn và có thêm nhiều hơn 1 buổi các ngày t2 - t6
      if (
        ($is_study_saturday_morning && $is_study_saturday_afternoon && $user_study_sesstion_on_t2_t6 >= 1)
        || ($is_study_sunday_morning && $is_study_sunday_afternoon && $user_study_sesstion_on_t2_t6 >= 1)
      ) {
        $total_score_violation += VP_SP1_GRAVE_VIOLATION * UT_ST1;
      } else if (
        ($is_study_saturday_morning && $is_study_saturday_afternoon)
        || ($is_study_sunday_morning && $is_study_sunday_afternoon)
      ) {
        $total_score_violation += VP_ST1_SERIOUS_VIOLATION * UT_ST1;
      } else if ($is_study_saturday_afternoon && $is_study_sunday_morning) {
        $total_score_violation += VP_ST1_MINOR_VIOLATION * UT_ST1;
      } else {
        $total_score_violation += VP_ST1_NO_VIOLATION * UT_ST1;
      }
    }
  }

  return $total_score_violation;
}
/**
 * ST2: Thời khóa biểu của một sinh viên (hay một lớp cứng) các tiết học của một buổi học phải được sắp xếp liên tục
 * hạn chế việc có các tiết học trống trong một buổi học.
 */
const UT_ST2 = 100000; // ĐIỂM ƯU TIÊN cho ràng buộc ST2

// ĐÁNH HỆ SỐ CHO VI PHẠM
// Ở giữa có nhiều hơn 3 tiết trống (3*45 phút)
const VP_ST2_GRAVE_VIOLATION = 4;
// ở giữa có 3 tiết trống 
const VP_ST2_SERIOUS_VIOLATION = 3;
// ở giữa có 2 tiết trống 
const VP_ST2_MODERATE_VIOLATION = 2;

const VP_ST2_MINOR_VIOLATION = 1;
// không có vi phạm 
const VP_ST2_NO_VIOLATION = 0;

function compute_score_violation_of_rule_class_session_continuously($calendar)
{
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  $number_empty_place_greate_than_3 = 0;
  $number_empty_place_equal_3 = 0;
  $number_empty_place_equal_2 = 0;

  $total_score_violation = 0;

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      $count_temp = 0;
      for ($k = 0; $k < $number_session; $k++) {
        if (!empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)) {
          $skip_step = $calendar[$i][$j][$k]->course_session_length;
          // bỏ qua các ô liên tiếp là chỗ chứa cho course có độ dài hiện tại
          // độ dài buổi học của khóa học là 2 thì chỉ cần bỏ qua 1 ô tính từ ô hiện tại
          // độ dài buổi học của khóa học là 3 thì chỉ cần bỏ qua 2 ô tính từ ô hiện tại
          $k += $skip_step - 1;

          if ($count_temp > 3) {
            $number_empty_place_greate_than_3++;
          } else if ($count_temp == 3) {
            $number_empty_place_equal_3++;
          } elseif ($count_temp == 2) {
            $number_empty_place_equal_2++;
          }

          $count_temp = 0;
          continue;
        }

        // sau khi bỏ qua các ô là ô khóa học và vòng lặp vẫn còn chạy vào đây tức là ô hiện tại đang duyệt là một ô trống
        // và ô trống này là thực chất nó không có course session nào và cũng không bị chiếm chỗ bởi độ dài của course session
        // bắt đầu đếm số ô trống tiết
        // đếm đến khi chạm một ô khóa học mới thì ngừng và kiểm tra xem số ô trống đang là loại nào (2, 3, > 3 ô liên tục)
        $count_temp++;
      }
    }
  }

  if ($number_empty_place_greate_than_3 == 0 && $number_empty_place_equal_3 == 0 && $number_empty_place_equal_2 == 0) {
    return UT_ST2 * VP_ST2_NO_VIOLATION;
  }

  $total_score_violation = ($number_empty_place_greate_than_3 * VP_ST2_GRAVE_VIOLATION + $number_empty_place_equal_3 * VP_ST2_SERIOUS_VIOLATION + $number_empty_place_equal_2 * VP_ST2_MODERATE_VIOLATION) * UT_ST2;

  return $total_score_violation;
}

function get_conflict_item_of_rule_class_session_continuously($calendar)
{
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $time_slot_index = 0;
  $conflict_position_array = [];

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      for ($k = 0; $k < $number_session; $k++) {
        $time_slot_index++;

        if (!empty($calendar[$i][$j][$k]) && isset($calendar[$i][$j][$k]->courseid)) {
          $skip_step = $calendar[$i][$j][$k]->course_session_length;
          // bỏ qua các ô liên tiếp là chỗ chứa cho course có độ dài hiện tại
          // độ dài buổi học của khóa học là 2 thì chỉ cần bỏ qua 1 ô tính từ ô hiện tại
          // độ dài buổi học của khóa học là 3 thì chỉ cần bỏ qua 2 ô tính từ ô hiện tại
          $k += $skip_step - 1;
          continue;
        }

        // sau khi bỏ qua các ô là ô khóa học và vòng lặp vẫn còn chạy vào đây tức là ô hiện tại đang duyệt là một ô trống
        // và ô trống này là thực chất nó không có course session nào và cũng không bị chiếm chỗ bởi độ dài của course session
        // bắt đầu đếm số ô trống tiết
        // đếm đến khi chạm một ô khóa học mới thì ngừng và kiểm tra xem số ô trống đang là loại nào (2, 3, > 3 ô liên tục)
        $conflict_position = new conflict_position(
          $i,
          $j,
          $k,
          $time_slot_index - 1
        );
        $conflict_position_array[] = $conflict_position;
      }
    }
  }


  return $conflict_position_array;
}

/**
 * ST3: Các lớp học- môn học được xếp sao cho 
 * buổi học mà giảng viên trợ giảng lên lớp học - môn học là ít nhất nhưng số tiết dạy là nhiều nhất
 * vd vào ngày thứ 7 giảng viên có 3 ca học (6 tiết học) thì nếu xếp lịch học 3 ca này sẽ cần nằm gọn trong một buổi sáng 
 * hoặc buổi chiều của ngày thứ 7.
 * để cho buổi học cần lên giảng dạy là 1 buổi ít nhất nhưng dạy được cả ba ca là nhiều nhất
 */
const UT_ST3 = 100000; // ĐIỂM ƯU TIÊN cho ràng buộc ST3

// ĐÁNH HỆ SỐ CHO VI PHẠM
// giảng viên chỉ đi dạy có 1 ca trên buổi (1h30 phút cho một buổi)
const VP_ST3_GRAVE_VIOLATION = 4;
// giảng viên đi dạy có 2 ca trên buổi nhưng có tiết trống xen giữa (2ca = 2*1h30phut = 3h00phut / buổi )
const VP_ST3_SERIOUS_VIOLATION = 3;
// giảng viên đi dạy có 2 ca và không có tiết trống xen giữa
const VP_ST3_MODERATE_VIOLATION = 2;
const VP_ST3_MINOR_VIOLATION = 1;
// giảng viên đi dạy 3 ca 1 buổi không có tiết trống xen giữa
const VP_ST3_NO_VIOLATION = 0;

function compute_score_violation_of_rule_largest_teaching_hours($calendar)
{
  global $DB;
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $total_score_violation = 0;

  $users_join_course_sql = "SELECT
                          CONCAT(user.id, r.id , c.id) id,
                          user.id userid, 
                          user.firstname user_firstname, 
                          user.lastname user_lastname
                        FROM {user} user 
                        join {role_assignments} ra on ra.userid = user.id
                        join {role} r on r.id = ra.roleid
                        join {context} ctx on ctx.id = ra.contextid
                        join {course} c on c.id = ctx.instanceid 
                        WHERE c.id != 1 
                              and ctx.contextlevel = 50
                              and r.shortname = 'editingteacher' or r.shortname = 'teacher'
                        GROUP BY user.id  
                        ORDER BY 
                                user.id,
                                user.firstname, 
                                user.lastname ASC";
  $params = [];
  $users = $DB->get_records_sql($users_join_course_sql, $params);

  if (empty($users)) {
    return $total_score_violation;
  }

  foreach ($users as $user) {
    $course_sql = "SELECT
                        CONCAT(user.id, r.id, c.id) id,
                        user.id userid, 
                        user.firstname user_firstname, 
                        user.lastname user_lastname, 
                        c.id courseid, 
                        c.fullname course_fullname
                    FROM mdl_user user 
                    join mdl_role_assignments ra on ra.userid = user.id
                    join mdl_role r on r.id = ra.roleid
                    join mdl_context ctx on ctx.id = ra.contextid
                    join mdl_course c on c.id = ctx.instanceid 
                    WHERE c.id != 1 
                        and ctx.contextlevel = 50
                        and r.shortname = 'editingteacher' or r.shortname = 'teacher'
                        and user.id = :userid

                    ORDER BY 
                            c.id, 
                            user.id,
                            user.firstname, 
                            user.lastname ASC";
    $params = ['userid' => $user->userid];
    $courses = $DB->get_records_sql($course_sql, $params);

    for ($i = 0; $i < $number_room; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        // mảng $stt_class_session lưu lại vị trí tiết học mà người giảng viên giảng dạy trong ngày hôm đó
        $stt_class_session = [];
        // kiểm tra cho buổi sáng người giảng viên này dạy bao nhiêu ca dạy (1 ca dạy = 2 tiết học liên tục = 2 * 45 phút = 1h30 phút)
        $number_class_session_in_morning = 0;
        for ($k = 0; $k < STT_CLASS_SESSIONS[6]; $k++) {
          foreach ($courses as $course) {
            if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
              $number_class_session_in_morning++;
              $stt_class_session[] = $k;
            }
          }
        }
        // kiểm tra cho buổi chiều người giảng viên này dạy bao nhiêu ca dạy (1 ca dạy = 2 tiết học liên tục = 2 * 45 phút = 1h30 phút)
        $number_class_session_in_afternoon = 0;
        for ($k = STT_CLASS_SESSIONS[6]; $k < STT_CLASS_SESSIONS[10]; $k++) {
          foreach ($courses as $course) {
            if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
              $number_class_session_in_afternoon++;
              $stt_class_session[] = $k;

            }
          }
        }
        // kiểm tra cho buổi tối người giảng viên này dạy bao nhiêu ca dạy (1 ca dạy = 2 tiết học liên tục = 2 * 45 phút = 1h30 phút)
        $number_class_session_in_evening = 0;
        for ($k = STT_CLASS_SESSIONS[10]; $k <= STT_CLASS_SESSIONS[15]; $k++) {
          foreach ($courses as $course) {
            if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
              $number_class_session_in_evening++;
              $stt_class_session[] = $k;

            }
          }
        }

        // kiểm tra các điều kiện ràng buộc.
        if ($number_class_session_in_morning == 1 or $number_class_session_in_afternoon == 1 or $number_class_session_in_evening == 1) {
          $total_score_violation += VP_ST3_GRAVE_VIOLATION * UT_ST3;
        } elseif ($number_class_session_in_morning == 2 or $number_class_session_in_afternoon == 2 or $number_class_session_in_evening == 2) {
          $number_class_session = count($stt_class_session);
          $is_continuous_class_session = true;
          for ($m = 0; $m < $number_class_session - 1; $m++) {
            if ($stt_class_session[$m] + $calendar[$i][$j][$stt_class_session[$m]]->course_session_length != $stt_class_session[$m + 1]) {
              $is_continuous_class_session = false;
              break;
            }
          }

          if ($is_continuous_class_session) {
            $total_score_violation += VP_ST3_MODERATE_VIOLATION * UT_ST3;
          } else {
            $total_score_violation += VP_ST3_SERIOUS_VIOLATION * UT_ST3;
          }
        } else {
          $total_score_violation += VP_ST3_NO_VIOLATION * UT_ST3;
        }
      }
    }
  }

  return $total_score_violation;
}

function get_conflict_item_of_rule_largest_teaching_hours($calendar)
{
  global $DB;
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $total_score_violation = 0;

  $users_join_course_sql = "SELECT
                          CONCAT(user.id, r.id , c.id) id,
                          user.id userid, 
                          user.firstname user_firstname, 
                          user.lastname user_lastname
                        FROM {user} user 
                        join {role_assignments} ra on ra.userid = user.id
                        join {role} r on r.id = ra.roleid
                        join {context} ctx on ctx.id = ra.contextid
                        join {course} c on c.id = ctx.instanceid 
                        WHERE c.id != 1 
                              and ctx.contextlevel = 50
                              and r.shortname = 'editingteacher' or r.shortname = 'teacher'
                        GROUP BY user.id  
                        ORDER BY 
                                user.id,
                                user.firstname, 
                                user.lastname ASC";
  $params = [];
  $users = $DB->get_records_sql($users_join_course_sql, $params);

  if (empty($users)) {
    return $total_score_violation;
  }

  foreach ($users as $user) {
    $course_sql = "SELECT
                        CONCAT(user.id, r.id, c.id) id,
                        user.id userid, 
                        user.firstname user_firstname, 
                        user.lastname user_lastname, 
                        c.id courseid, 
                        c.fullname course_fullname
                    FROM mdl_user user 
                    join mdl_role_assignments ra on ra.userid = user.id
                    join mdl_role r on r.id = ra.roleid
                    join mdl_context ctx on ctx.id = ra.contextid
                    join mdl_course c on c.id = ctx.instanceid 
                    WHERE c.id != 1 
                        and ctx.contextlevel = 50
                        and r.shortname = 'editingteacher' or r.shortname = 'teacher'
                        and user.id = :userid

                    ORDER BY 
                            c.id, 
                            user.id,
                            user.firstname, 
                            user.lastname ASC";
    $params = ['userid' => $user->userid];
    $courses = $DB->get_records_sql($course_sql, $params);

    for ($i = 0; $i < $number_room; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        // mảng $stt_class_session lưu lại vị trí tiết học mà người giảng viên giảng dạy trong ngày hôm đó
        $stt_class_session = [];
        // kiểm tra cho buổi sáng người giảng viên này dạy bao nhiêu ca dạy (1 ca dạy = 2 tiết học liên tục = 2 * 45 phút = 1h30 phút)
        $number_class_session_in_morning = 0;
        for ($k = 0; $k < STT_CLASS_SESSIONS[6]; $k++) {
          foreach ($courses as $course) {
            if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
              $number_class_session_in_morning++;
              $stt_class_session[] = $k;
            }
          }
        }
        // kiểm tra cho buổi chiều người giảng viên này dạy bao nhiêu ca dạy (1 ca dạy = 2 tiết học liên tục = 2 * 45 phút = 1h30 phút)
        $number_class_session_in_afternoon = 0;
        for ($k = STT_CLASS_SESSIONS[6]; $k < STT_CLASS_SESSIONS[10]; $k++) {
          foreach ($courses as $course) {
            if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
              $number_class_session_in_afternoon++;
              $stt_class_session[] = $k;

            }
          }
        }
        // kiểm tra cho buổi tối người giảng viên này dạy bao nhiêu ca dạy (1 ca dạy = 2 tiết học liên tục = 2 * 45 phút = 1h30 phút)
        $number_class_session_in_evening = 0;
        for ($k = STT_CLASS_SESSIONS[10]; $k <= STT_CLASS_SESSIONS[15]; $k++) {
          foreach ($courses as $course) {
            if ($calendar[$i][$j][$k]->courseid == $course->courseid) {
              $number_class_session_in_evening++;
              $stt_class_session[] = $k;

            }
          }
        }

        // kiểm tra các điều kiện ràng buộc.
        if ($number_class_session_in_morning == 1 or $number_class_session_in_afternoon == 1 or $number_class_session_in_evening == 1) {
          $total_score_violation += VP_ST3_GRAVE_VIOLATION * UT_ST3;
        } elseif ($number_class_session_in_morning == 2 or $number_class_session_in_afternoon == 2 or $number_class_session_in_evening == 2) {
          $number_class_session = count($stt_class_session);
          $is_continuous_class_session = true;
          for ($m = 0; $m < $number_class_session - 1; $m++) {
            if ($stt_class_session[$m] + $calendar[$i][$j][$stt_class_session[$m]]->course_session_length != $stt_class_session[$m + 1]) {
              $is_continuous_class_session = false;
              break;
            }
          }

          if ($is_continuous_class_session) {
            $total_score_violation += VP_ST3_MODERATE_VIOLATION * UT_ST3;
          } else {
            $total_score_violation += VP_ST3_SERIOUS_VIOLATION * UT_ST3;
          }
        } else {
          $total_score_violation += VP_ST3_NO_VIOLATION * UT_ST3;
        }
      }
    }
  }

  return $total_score_violation;
}

/**
 * ST4: Các môn học - lớp học - giảng viên cần ưu tiên xếp lịch học vào các buổi tối ngày t7 - cn 
 * ưu tiên 2 là tối các ngày trong tuần t2 - t6.
 * ưu tiên thì xếp vào sáng t7, cn
 * ưu tiên 4: Xếp vào chiều t2 - t6
 */
/**
  * giờ học buổi sáng chỉ áp dụng cho ngày t7, cn bắt đầu từ 7h30 sáng đến 12h trưa (gồm 3 ca 1h30 phút)
  * buổi chiều bắt đầu từ 1h30 chiều đến 4h30 chiều (gồm 2 ca 1h30 phút)
  * buổi tối bắt đầu lúc 5h30 chiều đến 22h tối (gồm 3 ca 1h30 phút)
  * Có một tiếng nghỉ 4h30 đến 5h30 cho giảng viên nghỉ ăn tối
  * có 1h30 phút nghỉ trưa từ 12h trưa đến 13h30 phút chiều.

  * Tổng ngày t7 cn có tất cả 8 ca dạy
  * ngày t2 - t6 có 3 ca dạy
  */

const UT_ST4 = 100000; // ĐIỂM ƯU TIÊN cho ràng buộc ST4

// ĐÁNH HỆ SỐ CHO VI PHẠM
// được xếp vào sáng hay chiều t2 - t6
const VP_ST4_GRAVE_VIOLATION = 4;
// xếp lịch vào sáng t7
const VP_ST4_SERIOUS_VIOLATION = 3;
// xếp lịch vào sáng cn
const VP_ST4_MODERATE_VIOLATION = 2;
// được xếp vào tối t2 - t6
const VP_ST4_MINOR_VIOLATION = 1;
// được xếp vào chiều hoặc tối t7 hoặc cn
const VP_ST4_NO_VIOLATION = 0;

function compute_score_violation_of_rule_priority_order_of_class_session($calendar)
{
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $total_score_violation = 0;

  // Phải chèn thêm điều kiện kiểm tra có course vào ngày đó nữa thì mới đúng
  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      for ($k = 0; $k < $number_session; $k++) {
        if ($number_session >= STT_CLASS_SESSIONS[10] and isset($calendar[$i][$j][$k]->courseid)) {
          // nếu là tối thứ 7 hoặc chủ nhật
          // 5 và 6 là index của ngày bên trong biến const DATES
          if ($number_day == 5 or $number_day == 6) {
            $total_score_violation += VP_ST4_NO_VIOLATION * UT_ST4;
          } else {
            // nếu là tối của các ngày từ 2 đến 6
            $total_score_violation += VP_ST4_MINOR_VIOLATION * UT_ST4;
          }

        } elseif ($number_session >= STT_CLASS_SESSIONS[6] and isset($calendar[$i][$j][$k]->courseid)) {
          // NẾU là chiều của t7 hay chủ nhật
          if ($number_day == 5 or $number_day == 6) {
            $total_score_violation += VP_ST4_NO_VIOLATION * UT_ST4;
          } else {
            // nếu là chiều của ngày t2 đến t6
            $total_score_violation += VP_ST4_GRAVE_VIOLATION * UT_ST4;
          }
        } elseif ($number_session >= STT_CLASS_SESSIONS[0] and isset($calendar[$i][$j][$k]->courseid)) {
          // NẾU đây là buổi sáng
          // kiểm tra ngày chủ nhật
          if ($number_day == 6) {
            $total_score_violation += VP_ST4_MODERATE_VIOLATION * UT_ST4;
          }
          // nếu là ngày t7
          elseif ($number_day == 5) {
            $total_score_violation += VP_ST4_SERIOUS_VIOLATION * UT_ST4;
          } else {
            $total_score_violation += VP_ST4_GRAVE_VIOLATION * UT_ST4;
          }
        }
      }
    }
  }

  return $total_score_violation;
}

/**
 * ST5: Các lớp học - môn học cần ưu tiên xếp lịch học vào các chiều các ngày từ thứ 2 đến thứ 6.
 * đã gộp cái này vào ST4.
 */

/**
 * ST6 : Các môn học lớp học của một sinh viên cần được xếp lịch học giãn cách ngày 
 * vd học môn A vào chiều T2 thì không được học môn A vào chiều T3
 * mà nên đổi môn A sang chiều T4 hoặc T5 tương tự như vậy cho các môn học khác của sinh viên này.
 * Điều này giúp sinh viên có thời gian nghỉ ngơi và ôn tập giữa các buổi học.
 */

const UT_ST6 = 100000; // ĐIỂM ƯU TIÊN cho ràng buộc ST6

// ĐÁNH HỆ SỐ CHO VI PHẠM
// có hơn 3 môn học vào các ngày liên tiếp
const VP_ST6_GRAVE_VIOLATION = 4;
// có 3 môn học liên tiếp ngày
const VP_ST6_SERIOUS_VIOLATION = 3;
// có hai môn học liên tiếp ngày
const VP_ST6_MODERATE_VIOLATION = 2;
// có 1 môn học liên tiếp ngày
const VP_ST6_MINOR_VIOLATION = 1;
// Không có môn nào học liên tiếp nhau trong tuần
const VP_ST6_NO_VIOLATION = 0;

function compute_score_violation_of_rule_time_gap_between_class_session($calendar)
{
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $total_score_violation = 0;
  $number_course_study_on_continuous_day = 0;

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day - 1; $j++) {
      for ($k = 0; $k < $number_session; $k++) {
        for ($m = 0; $m < $number_session; $m++) {
          if ($calendar[$i][$j][$k]->courseid == $calendar[$i][$j + 1][$m]->courseid) {
            $number_course_study_on_continuous_day++;
          }
        }
      }
    }
  }

  if ($number_course_study_on_continuous_day > 3) {
    $total_score_violation += VP_ST6_GRAVE_VIOLATION * UT_ST6;
  } elseif ($number_course_study_on_continuous_day == 3) {
    $total_score_violation += VP_ST6_SERIOUS_VIOLATION * UT_ST6;

  } elseif ($number_course_study_on_continuous_day == 2) {
    $total_score_violation += VP_ST6_MODERATE_VIOLATION * UT_ST6;

  } elseif ($number_course_study_on_continuous_day == 1) {
    $total_score_violation += VP_ST6_MINOR_VIOLATION * UT_ST6;
  } else {
    $total_score_violation += VP_ST6_NO_VIOLATION * UT_ST6;
  }

  return $total_score_violation;
}
// Các ràng buộc mềm về không gian của lớp học
/**
 * SP1: Các lớp học - môn học của một buổi học của một sinh viên, giảng viên cần được xếp vào các phòng học gần nhau
 * Điều này giúp sinh viên và giảng viên có thể di chuyển dễ dàng giữa các phòng học trong cùng một buổi học.
 * Ví dụ: nếu một sinh viên có hai lớp học trong cùng một buổi sáng, thì các lớp học này nên được xếp vào các phòng học gần nhau.
 * Và cơ sở học của môn A là CS1 thì cơ sở học của môn B cũng nên là CS1 tại cùng một thời điểm.
 */
const UT_SP1 = 100000; // ĐIỂM ƯU TIÊN cho ràng buộc SP1

// ĐÁNH HỆ SỐ CHO VI PHẠM
// CÁC LỚP HỌC ở hai cơ sở phường xã quận huyện tỉnh khác nhau
const VP_SP1_GRAVE_VIOLATION = 4;

// các phòng cùng 1 cơ sở nhưng hai tòa nhà khác nhau
const VP_SP1_SERIOUS_VIOLATION = 3;

const VP_SP1_MODERATE_VIOLATION = 2;
// các phòng ở cùng 1 cơ sở nhưng cách nhau từ 2 tầng
const VP_SP1_MINOR_VIOLATION = 1;
// cùng 1 cơ sở cùng tầng hoặc ở trên 1 tầng.
const VP_SP1_NO_VIOLATION = 0;
function is_gap_floor_greate_than_2($class1_information, $class2_information)
{
  if (!isset($class1_information->floor) or !isset($class2_information->floor)) {
    return false;
  }

  if (abs($class1_information->floor - $class2_information->floor) >= 2) {
    return true;
  }

  return false;
}
function is_same_building($class1_information, $class2_information)
{
  if (!isset($class1_information->building) or !isset($class2_information->building)) {
    return false;
  }

  if (strtolower($class1_information->building) == strtolower($class2_information->building)) {
    return true;
  }

  return false;
}

function is_same_teaching_facility_address($class1_information, $class2_information)
{
  if (!isset($class1_information->province) or !isset($class2_information->province)) {
    return false;
  }

  if (!isset($class1_information->district) or !isset($class2_information->district)) {
    return false;
  }

  if (!isset($class1_information->ward) or !isset($class2_information->ward)) {
    return false;
  }

  if (
    strtolower($class1_information->province) == strtolower($class2_information->province)
    and strtolower($class1_information->district) == strtolower($class2_information->district)
    and strtolower($class1_information->ward) == strtolower($class2_information->ward)
  ) {
    return true;
  }

  return false;
}
function compute_score_violation_of_rule_room_gap_between_class_session($calendar)
{
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $total_score_violation = 0;

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      for ($k = 0; $k < $number_session - 1; $k++) {
        if (!empty($calendar[$i][$j][$k]) and !empty($calendar[$i][$j][$k + 1])) {
          if (!is_same_teaching_facility_address($calendar[$i][$j][$k], $calendar[$i][$j][$k + 1])) {
            $total_score_violation += VP_SP1_GRAVE_VIOLATION * UT_SP1;
          }

          if (!is_same_building($calendar[$i][$j][$k], $calendar[$i][$j][$k + 1])) {
            $total_score_violation += VP_SP1_SERIOUS_VIOLATION * UT_SP1;
          }

          if (is_gap_floor_greate_than_2($calendar[$i][$j][$k], $calendar[$i][$j][$k + 1])) {
            $total_score_violation += VP_SP1_MINOR_VIOLATION * UT_SP1;
          } else {
            $total_score_violation += VP_SP1_NO_VIOLATION * UT_SP1;

          }

        }
      }
    }
  }

  return $total_score_violation;
}

/**
 * SP2: Các phòng học cần được sử dụng hiệu quả, tránh tình trạng phòng học trống trong cùng một thời điểm.
 * Điều này có nghĩa là nếu một phòng học đang được sử dụng cho một lớp học thì đảm bảo số lượng sinh viên ở trong phòng học đó 
 * phải lớn hơn 2/3 sức chứa của phòng học đó.
 * Nếu không thì nên chuyển lớp học đó sang phòng học khác có sức chứa phù hợp
 */
const UT_SP2 = 100; // ĐIỂM ƯU TIÊN cho ràng buộc SP2

// ĐÁNH HỆ SỐ CHO VI PHẠM
// sĩ số chỉ bằng 1/4 sức chứa của phòng
const VP_SP2_GRAVE_VIOLATION = 4;
// sĩ số chỉ bằng 1/3 sức chứa của phòng
const VP_SP2_SERIOUS_VIOLATION = 3;
// sĩ số bằng 1/2 sức chứa của phòng
const VP_SP2_MODERATE_VIOLATION = 2;
// sĩ số bằng 2/3 sức chứa của phòng
const VP_SP2_MINOR_VIOLATION = 1;
// sĩ số lớn hơn 2/3 sức chứa của phòng.
const VP_SP2_NO_VIOLATION = 0;
function evaluate_function_of_genetic_algorithm($calendar, $calendar_index)
{
  $total_score_violation = 0;
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  for ($i = 0; $i < $number_room; $i++) {
    for ($j = 0; $j < $number_day; $j++) {
      for ($k = 0; $k < $number_session; $k++) {
        $course_session_information = $calendar[$i][$j][$k];
        if (!empty($course_session_information) and isset($course_session_information->courseid)) {
          $total_score_violation += compute_score_violation_of_rule_class_duration_in_one_session(
            $course_session_information->course_session_start_time,
            $course_session_information->course_session_length
          );

          $total_score_violation += compute_score_violation_of_rule_forbidden_session(
            $course_session_information->date,
            $course_session_information->course_session_start_time,
            $course_session_information->course_session_length
          );

          $total_score_violation += compute_score_violation_of_rule_holiday($course_session_information->date);

          $total_score_violation += compute_score_violation_of_rule_class_overtime(
            $course_session_information->date,
            $course_session_information->course_session_end_time
          );

          $total_score_violation += compute_score_violation_of_rule_not_enough_number_of_course_session_weekly(
            $calendar,
            $course_session_information->courseid
          );

          $total_score_violation += compute_score_violation_of_rule_study_double_session_of_same_course_on_one_day(
            $calendar,
            $course_session_information->courseid
          );

          $total_score_violation += compute_score_violation_of_rule_duplicate_course_at_same_room_at_same_time(
            $calendar,
            $course_session_information->random_room_stt,
            $course_session_information->date,
            $course_session_information->course_session_start_time
          );

        }
      }
    }
  }

  $total_score_violation += compute_score_violation_of_rule_student_study_all_day($calendar);

  $total_score_violation += compute_score_violation_of_rule_class_session_continuously($calendar);

  $total_score_violation += compute_score_violation_of_rule_largest_teaching_hours($calendar);

  $total_score_violation += compute_score_violation_of_rule_priority_order_of_class_session($calendar);

  $total_score_violation += compute_score_violation_of_rule_time_gap_between_class_session($calendar);

  $total_score_violation += compute_score_violation_of_rule_room_gap_between_class_session($calendar);

  return ['total_score_violation' => $total_score_violation, 'calendar_index' => $calendar_index];
}

function hybridization_method_by_day($father_calendar, $mother_calendar, $random_alpha_gen_index)
{
  $number_room = count($father_calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);
  $children_calendar = [];

  // tạo con bên tay phải trước 
  // con bên tay phải được định nghĩa là con mà tại vị trí gen alpha dùng để ghép và bên phải của alpha là gen của mẹ
  // ràng buộc là vị trí alpha [1, day_number - 1] để đảm bảo luôn luôn có ít nhất 1 gen là của cha hoặc của mẹ
  if ($random_alpha_gen_index >= 1) {
    $s1_right_hand_side_child_calendar = deep_copy_calendar_array($father_calendar);
    for ($i = 0; $i < $number_room; $i++) {
      for ($j = $random_alpha_gen_index; $j < $number_day; $j++) {
        for ($k = 0; $k < $number_session; $k++) {
          $s1_right_hand_side_child_calendar[$i][$j][$k] = new course_session_information(
            $mother_calendar[$i][$j][$k]->courseid,
            $mother_calendar[$i][$j][$k]->course_name,
            $mother_calendar[$i][$j][$k]->course_session_length,
            $mother_calendar[$i][$j][$k]->course_session_start_time,
            $mother_calendar[$i][$j][$k]->course_session_end_time,
            $mother_calendar[$i][$j][$k]->editting_teacher_array,
            $mother_calendar[$i][$j][$k]->non_editting_teacher_array,
            $mother_calendar[$i][$j][$k]->date,
            $mother_calendar[$i][$j][$k]->random_room_stt,
            $mother_calendar[$i][$j][$k]->room,
            $mother_calendar[$i][$j][$k]->floor,
            $mother_calendar[$i][$j][$k]->building,
            $mother_calendar[$i][$j][$k]->ward,
            $mother_calendar[$i][$j][$k]->district,
            $mother_calendar[$i][$j][$k]->province
          );
        }
      }
    }

    $children_calendar[] = $s1_right_hand_side_child_calendar;
  }

  // tạo con bên tay trái
  // con bên tay trái được định nghĩa là con mà tại vị trí gen alpha - 1 dùng để ghép và bên trái của alpha - 1 là gen của mẹ
  // ràng buộc là vị trí của gen alpha [0, day_number - 2] để đảm bảo luôn luôn có ít nhất 1 gen là của cha hoặc của mẹ.
  if ($random_alpha_gen_index <= $number_day - 2) {
    $s2_left_hand_side_child_calendar = deep_copy_calendar_array($father_calendar);
    for ($i = 0; $i < $number_room; $i++) {
      for ($j = 0; $j <= $random_alpha_gen_index; $j++) {
        for ($k = 0; $k < $number_session; $k++) {
          $s2_left_hand_side_child_calendar[$i][$j][$k] = new course_session_information(
            $mother_calendar[$i][$j][$k]->courseid,
            $mother_calendar[$i][$j][$k]->course_name,
            $mother_calendar[$i][$j][$k]->course_session_length,
            $mother_calendar[$i][$j][$k]->course_session_start_time,
            $mother_calendar[$i][$j][$k]->course_session_end_time,
            $mother_calendar[$i][$j][$k]->editting_teacher_array,
            $mother_calendar[$i][$j][$k]->non_editting_teacher_array,
            $mother_calendar[$i][$j][$k]->date,
            $mother_calendar[$i][$j][$k]->random_room_stt,
            $mother_calendar[$i][$j][$k]->room,
            $mother_calendar[$i][$j][$k]->floor,
            $mother_calendar[$i][$j][$k]->building,
            $mother_calendar[$i][$j][$k]->ward,
            $mother_calendar[$i][$j][$k]->district,
            $mother_calendar[$i][$j][$k]->province
          );
        }
      }
    }

    $children_calendar[] = $s2_left_hand_side_child_calendar;
  }

  return $children_calendar;
}

function hybridization_method_by_session($father_calendar, $mother_calendar, $random_alpha_gen_index)
{
  $children_calendar = [];
  $number_room = count($father_calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  // tạo con bên tay phải trước 
  // con bên tay phải được định nghĩa là con mà tại vị trí gen alpha dùng để ghép và bên phải của alpha là gen của mẹ
  // ràng buộc là vị trí alpha [1, session_number - 1] để đảm bảo luôn luôn có ít nhất 1 gen là của cha hoặc của mẹ
  if ($random_alpha_gen_index >= 1) {
    $s1_right_hand_side_child_calendar = deep_copy_calendar_array($father_calendar);
    for ($i = 0; $i < $number_room; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        for ($k = $random_alpha_gen_index; $k < $number_session; $k++) {
          $s1_right_hand_side_child_calendar[$i][$j][$k] = new course_session_information(
            $mother_calendar[$i][$j][$k]->courseid,
            $mother_calendar[$i][$j][$k]->course_name,
            $mother_calendar[$i][$j][$k]->course_session_length,
            $mother_calendar[$i][$j][$k]->course_session_start_time,
            $mother_calendar[$i][$j][$k]->course_session_end_time,
            $mother_calendar[$i][$j][$k]->editting_teacher_array,
            $mother_calendar[$i][$j][$k]->non_editting_teacher_array,
            $mother_calendar[$i][$j][$k]->date,
            $mother_calendar[$i][$j][$k]->random_room_stt,
            $mother_calendar[$i][$j][$k]->room,
            $mother_calendar[$i][$j][$k]->floor,
            $mother_calendar[$i][$j][$k]->building,
            $mother_calendar[$i][$j][$k]->ward,
            $mother_calendar[$i][$j][$k]->district,
            $mother_calendar[$i][$j][$k]->province
          );
        }
      }
    }

    $children_calendar[] = $s1_right_hand_side_child_calendar;
  }

  // tạo con bên tay trái
  // con bên tay trái được định nghĩa là con mà tại vị trí gen alpha - 1 dùng để ghép và bên trái của alpha - 1 là gen của mẹ
  // ràng buộc là vị trí của gen alpha [0, session_number - 2] để đảm bảo luôn luôn có ít nhất 1 gen là của cha hoặc của mẹ.
  if ($random_alpha_gen_index <= $number_day - 2) {
    $s2_left_hand_side_child_calendar = deep_copy_calendar_array($father_calendar);
    for ($i = 0; $i < $number_room; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        for ($k = 0; $k <= $random_alpha_gen_index; $k++) {
          $s2_left_hand_side_child_calendar[$i][$j][$k] = new course_session_information(
            $mother_calendar[$i][$j][$k]->courseid,
            $mother_calendar[$i][$j][$k]->course_name,
            $mother_calendar[$i][$j][$k]->course_session_length,
            $mother_calendar[$i][$j][$k]->course_session_start_time,
            $mother_calendar[$i][$j][$k]->course_session_end_time,
            $mother_calendar[$i][$j][$k]->editting_teacher_array,
            $mother_calendar[$i][$j][$k]->non_editting_teacher_array,
            $mother_calendar[$i][$j][$k]->date,
            $mother_calendar[$i][$j][$k]->random_room_stt,
            $mother_calendar[$i][$j][$k]->room,
            $mother_calendar[$i][$j][$k]->floor,
            $mother_calendar[$i][$j][$k]->building,
            $mother_calendar[$i][$j][$k]->ward,
            $mother_calendar[$i][$j][$k]->district,
            $mother_calendar[$i][$j][$k]->province
          );
        }
      }
    }

    $children_calendar[] = $s2_left_hand_side_child_calendar;
  }

  return $children_calendar;
}

function hybridization_method_by_room($father_calendar, $mother_calendar, $random_alpha_gen_index)
{
  $children_calendar = [];
  $number_room = count($father_calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  // tạo con bên tay phải trước 
  // con bên tay phải được định nghĩa là con mà tại vị trí gen alpha dùng để ghép và bên phải của alpha là gen của mẹ
  // ràng buộc là vị trí alpha [1, session_number - 1] để đảm bảo luôn luôn có ít nhất 1 gen là của cha hoặc của mẹ
  if ($random_alpha_gen_index >= 1) {
    $s1_right_hand_side_child_calendar = deep_copy_calendar_array($father_calendar);
    for ($i = $random_alpha_gen_index; $i < $number_room; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        for ($k = 0; $k < $number_session; $k++) {
          $s1_right_hand_side_child_calendar[$i][$j][$k] = new course_session_information(
            $mother_calendar[$i][$j][$k]->courseid,
            $mother_calendar[$i][$j][$k]->course_name,
            $mother_calendar[$i][$j][$k]->course_session_length,
            $mother_calendar[$i][$j][$k]->course_session_start_time,
            $mother_calendar[$i][$j][$k]->course_session_end_time,
            $mother_calendar[$i][$j][$k]->editting_teacher_array,
            $mother_calendar[$i][$j][$k]->non_editting_teacher_array,
            $mother_calendar[$i][$j][$k]->date,
            $mother_calendar[$i][$j][$k]->random_room_stt,
            $mother_calendar[$i][$j][$k]->room,
            $mother_calendar[$i][$j][$k]->floor,
            $mother_calendar[$i][$j][$k]->building,
            $mother_calendar[$i][$j][$k]->ward,
            $mother_calendar[$i][$j][$k]->district,
            $mother_calendar[$i][$j][$k]->province
          );
        }
      }
    }

    $children_calendar[] = $s1_right_hand_side_child_calendar;
  }

  // tạo con bên tay trái
  // con bên tay trái được định nghĩa là con mà tại vị trí gen alpha - 1 dùng để ghép và bên trái của alpha - 1 là gen của mẹ
  // ràng buộc là vị trí của gen alpha [0, session_number - 2] để đảm bảo luôn luôn có ít nhất 1 gen là của cha hoặc của mẹ.
  if ($random_alpha_gen_index <= $number_day - 2) {
    $s2_left_hand_side_child_calendar = deep_copy_calendar_array($father_calendar);
    for ($i = 0; $i <= $random_alpha_gen_index; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        for ($k = 0; $k < $number_session; $k++) {
          $s2_left_hand_side_child_calendar[$i][$j][$k] = new course_session_information(
            $mother_calendar[$i][$j][$k]->courseid,
            $mother_calendar[$i][$j][$k]->course_name,
            $mother_calendar[$i][$j][$k]->course_session_length,
            $mother_calendar[$i][$j][$k]->course_session_start_time,
            $mother_calendar[$i][$j][$k]->course_session_end_time,
            $mother_calendar[$i][$j][$k]->editting_teacher_array,
            $mother_calendar[$i][$j][$k]->non_editting_teacher_array,
            $mother_calendar[$i][$j][$k]->date,
            $mother_calendar[$i][$j][$k]->random_room_stt,
            $mother_calendar[$i][$j][$k]->room,
            $mother_calendar[$i][$j][$k]->floor,
            $mother_calendar[$i][$j][$k]->building,
            $mother_calendar[$i][$j][$k]->ward,
            $mother_calendar[$i][$j][$k]->district,
            $mother_calendar[$i][$j][$k]->province
          );
        }
      }
    }

    $children_calendar[] = $s2_left_hand_side_child_calendar;
  }

  return $children_calendar;
}

function hybridization_method_by_gene_mutation($father_calendar, $mother_calendar)
{
  $number_room = count($father_calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  $random_number_gene_mutation = random_int(1, $number_room * $number_day * $number_session);
  $random_gen_mutation_index = [];
  for ($i = 0; $i < $random_number_gene_mutation; $i++) {
    $random_gen_mutation_index[] = ['room' => random_int(0, $number_room - 1), 'day' => random_int(0, $number_day - 1), 'session' => random_int(0, $number_session - 1)];
  }

  $children_calendar = [];
  $father_mutation = deep_copy_calendar_array($father_calendar);
  $mother_mutation = deep_copy_calendar_array($mother_calendar);

  foreach ($random_gen_mutation_index as $gene_index) {
    $father_mutation[$gene_index['room']][$gene_index['day']][$gene_index['session']] = $mother_calendar[$gene_index['room']][$gene_index['day']][$gene_index['session']];
    $mother_mutation[$gene_index['room']][$gene_index['day']][$gene_index['session']] = $father_calendar[$gene_index['room']][$gene_index['day']][$gene_index['session']];
    $children_calendar[] = $father_mutation;
    $children_calendar[] = $mother_mutation;
  }

  return $children_calendar;
}
function select_good_individuals_in_the_calendar_community($calendar_community, $max_number_of_individuals = MAX_CALENDAR_NUMBER, $ith_generation = 0)
{
  $good_individuals = [];
  // Sau mỗi thế hệ lai tạo tiến hành đánh giá lại điểm của mỗi cá thể trong quần thể các thời khóa biểu
  // Sau đó sort lại mảng calendar_score_violation_array theo thứ tự tăng dần điểm của điểm vi phạm được biểu thị ở cột 'total-score-violation'
  // Sau đó thực hiện việc chọn lọc chỉ lấy 50 cá thể có điểm nhỏ nhất theo thứ tự index 0 ->49 và xóa các phần tử calendar phía sau index 49.

  $calendar_number = count($calendar_community);
  $calendar_score_violation_array = [];
  // đánh giá điểm vi phạm của mỗi calendar trong thế hệ hiện tại.
  for ($k = 0; $k < $calendar_number; $k++) {
    $calendar_score_violation_array[] = evaluate_function_of_genetic_algorithm($calendar_community[$k], $k);
  }

  // // chỉ giữ lại những giá trị nào không vi phạm vào các ràng buộc cứng (các ràng buộc có điểm vi phạm UT_HT1=UT_HT2=UT_HT3=UT_HT4=UT_HT5=UT_HT6=UT_HT7=....= 1000000000)
  // // thực tế với cách làm hiện tại các cá thể tạo ra trong các thế hệ rất hiếm không vi phạm ràng buộc cứng 
  // // do đó giải thuật này có ý nghĩa học thuật nhưng không thể áp dụng vào bài toán và đạt được hiệu quả cao.
  if ($ith_generation < MAX_NUMBER_OF_GENERATION / 100000) {
    $calendar_score_violation_array = array_filter(
      $calendar_score_violation_array,
      function ($value) {
        // Ở những thế hệ ban đầu cho phép vi phạm 7 ràng buộc cứng. 
        // Càng về thế hệ sau thì sẽ khép lại yêu cầu về vi phạm ràng buộc cứng dần dần.
        return $value['total_score_violation'] < 7 * UT_HT1;
      }
    );
  } elseif ($ith_generation < MAX_NUMBER_OF_GENERATION / 10000) {
    $calendar_score_violation_array = array_filter(
      $calendar_score_violation_array,
      function ($value) {
        // Ở những thế hệ ban đầu cho phép vi phạm 5 ràng buộc cứng. 
        // Càng về thế hệ sau thì sẽ khép lại yêu cầu về vi phạm ràng buộc cứng dần dần.
        return $value['total_score_violation'] < 5 * UT_HT1;
      }
    );
  } elseif ($ith_generation < MAX_NUMBER_OF_GENERATION / 1000) {
    $calendar_score_violation_array = array_filter(
      $calendar_score_violation_array,
      function ($value) {
        // cho phép vi phạm 4 ràng buộc cứng. 
        // Càng về thế hệ sau thì sẽ khép lại yêu cầu về vi phạm ràng buộc cứng dần dần.
        return $value['total_score_violation'] < 4 * UT_HT1;
      }
    );
  } elseif ($ith_generation < MAX_NUMBER_OF_GENERATION / 100) {
    $calendar_score_violation_array = array_filter(
      $calendar_score_violation_array,
      function ($value) {
        // cho phép vi phạm 4 ràng buộc cứng. 
        // Càng về thế hệ sau thì sẽ khép lại yêu cầu về vi phạm ràng buộc cứng dần dần.
        return $value['total_score_violation'] < 3 * UT_HT1;
      }
    );
  } elseif ($ith_generation < MAX_NUMBER_OF_GENERATION / 10) {
    $calendar_score_violation_array = array_filter(
      $calendar_score_violation_array,
      function ($value) {
        // cho phép vi phạm 4 ràng buộc cứng. 
        // Càng về thế hệ sau thì sẽ khép lại yêu cầu về vi phạm ràng buộc cứng dần dần.
        return $value['total_score_violation'] < 2 * UT_HT1;
      }
    );
  } else {
    $calendar_score_violation_array = array_filter(
      $calendar_score_violation_array,
      function ($value) {
        // cho phép vi phạm 4 ràng buộc cứng. 
        // Càng về thế hệ sau thì sẽ khép lại yêu cầu về vi phạm ràng buộc cứng dần dần.
        return $value['total_score_violation'] < 1 * UT_HT1;
      }
    );
  }

  // sắp xếp lại mảng $calendar_score_violation_array
  array_multisort(
    array_column(
      $calendar_score_violation_array,
      'total_score_violation'
    ),
    SORT_ASC,
    SORT_REGULAR,
    $calendar_score_violation_array
  );

  // chỉ lấy ra những cá thể tốt nhất trong số lượng max number inviduals mà có điểm nhỏ hơn điểm các vi phạm cứng
  for ($i = 0; $i < $max_number_of_individuals and $i < count($calendar_score_violation_array); $i++) {
    $calendar_index = $calendar_score_violation_array[$i]['calendar_index'];
    $good_individuals[] = deep_copy_calendar_array($calendar_community[$calendar_index]);
  }
  return $good_individuals;
}
function genetic_algorithm($initial_calendar_community)
{
  $second_calendar_community = [];

  for ($i = 0; $i < MAX_NUMBER_OF_GENERATION; $i++) {
    $calendar_number = count($initial_calendar_community);
    if ($calendar_number > 0) {
      for ($j = 0; $j < count($initial_calendar_community); $j++) {
        $second_calendar_community[] = deep_copy_calendar_array($initial_calendar_community[$i]);
      }

      if (count($second_calendar_community) > 3 * MAX_CALENDAR_NUMBER) {
        $second_calendar_community = select_good_individuals_in_the_calendar_community($second_calendar_community, MAX_CALENDAR_NUMBER, $i);
      }

      for ($j = 0; $j < MAX_NUMBER_OF_CROSSOVER_OPERATIONS_IN_ONE_GENERATION; $j++) {
        $random_father_calendar_index = random_int(0, $calendar_number - 1);
        $random_mother_calendar_index = random_int(0, $calendar_number - 1);
        $random_alpha_gen_index = 0;

        $random_alpha_gen_index = random_int(0, count(DATES) - 1);
        $initial_calendar_community += hybridization_method_by_day($initial_calendar_community[$random_father_calendar_index], $initial_calendar_community[$random_mother_calendar_index], $random_alpha_gen_index);
        $random_alpha_gen_index = random_int(0, count(AVAILABLE_CLASS_SESSIONS) - 1);
        $initial_calendar_community += hybridization_method_by_session($initial_calendar_community[$random_father_calendar_index], $initial_calendar_community[$random_mother_calendar_index], $random_alpha_gen_index);
        $random_alpha_gen_index = random_int(0, count($initial_calendar_community[0]) - 1);
        $initial_calendar_community += hybridization_method_by_room($initial_calendar_community[$random_father_calendar_index], $initial_calendar_community[$random_mother_calendar_index], $random_alpha_gen_index);
        $initial_calendar_community += hybridization_method_by_gene_mutation($initial_calendar_community[$random_father_calendar_index], $initial_calendar_community[$random_mother_calendar_index]);
      }

      $initial_calendar_community = select_good_individuals_in_the_calendar_community($initial_calendar_community, MAX_CALENDAR_NUMBER, $i);
    } else {
      $initial_calendar_community = $second_calendar_community;
    }
  }

  $initial_calendar_community = select_good_individuals_in_the_calendar_community($initial_calendar_community, MAX_CALENDAR_NUMBER, $i);
  return $initial_calendar_community;
}


/**
 * Extend the settings navigation for course calendar.
 * @param \settings_navigation $settingsnav The settings navigation object.
 * @param \context $context The context of the current page.
 */
function local_course_calendar_extend_settings_navigation($settingsnav, $context)
{
  global $CFG, $PAGE;

  // Only add this settings item on non-site course pages.
  if (!$PAGE->course or $PAGE->course->id == 1) {
    return;
  }

  // Only let users with the appropriate capability see this settings item.
  if (!has_capability('local/course_calendar:edit_total_lesson_for_course', \context_course::instance($PAGE->course->id))) {
    return;
  }

  if ($settingnode = $settingsnav->find('courseadmin', \navigation_node::TYPE_COURSE)) {
    $strfoo = get_string('edit_total_lesson_for_course', 'local_course_calendar');
    $url = new moodle_url('/local/course_calendar/edit_total_lesson_for_course.php', array('courseid' => $PAGE->course->id));
    $foonode = \navigation_node::create(
      $strfoo,
      $url,
      \navigation_node::NODETYPE_LEAF,
      get_string('edit_total_lesson_for_course', 'local_course_calendar'),
      'edit_total_lesson_for_course',
      new pix_icon('i/edit', $strfoo)
    );
    if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
      $foonode->make_active();
    }
    $settingnode->add_node($foonode);
  }
}
function get_empty_rooms(&$room_list, $start_time, $end_time)
{

  foreach ($room_list as $room) {
    $room_address = $room->room_id;
    if (!is_empty_room($room_address, $start_time, $end_time)) {
      unset($room_list[$room_address]);
    }
  }

}
function is_empty_room($room_address, $start_time, $end_time)
{
  global $DB;

  // Check if the room address is valid
  if (empty($room_address) || empty($start_time) || empty($end_time)) {
    return false;
  }
  // Check if the room exists in the database
  $room_exists = $DB->record_exists('local_course_calendar_course_room', ['id' => $room_address]);
  if (!$room_exists) {
    return false;
  }
  // Check if there are any course sections in the specified room that overlap with the given time range
  // This query checks if there are any course sections in the specified room that overlap with the given time range
  // It returns true if there are no overlapping course sections, indicating that the room is empty during that time
  // If there are overlapping course sections, it returns false, indicating that the room is not empty

  $params = [
    'room_address' => $room_address,
    'start_time_th1' => $start_time,
    'end_time_th1' => $end_time,
    'start_time_th2' => $start_time,
    'end_time_th2' => $end_time,
    'start_time_th3' => $start_time,
    'start_time_th31' => $start_time,
    'end_time_th3' => $end_time,
    'start_time_th4' => $start_time,
    'end_time_th41' => $end_time,
    'end_time_th4' => $end_time,
  ];

  $sql = "SELECT concat(cr.id, cs.id, cs.class_begin_time, cs.class_end_time) course_section_information_id, cr.*, cs.*
  FROM {local_course_calendar_course_section} cs
  JOIN {local_course_calendar_course_room} cr ON cs.course_room_id = cr.id
  WHERE cr.id = :room_address 
        and 
        (
          (:start_time_th1 <= cs.class_begin_time and cs.class_end_time <= :end_time_th1)
          or (cs.class_begin_time <= :start_time_th2 and :end_time_th2 <= cs.class_end_time)
          or (cs.class_begin_time <= :start_time_th3 and :start_time_th31 < cs.class_end_time and cs.class_end_time <= :end_time_th3)
          or (:start_time_th4 <= cs.class_begin_time and cs.class_begin_time < :end_time_th41 and :end_time_th4 <= cs.class_end_time)
        )";

  $overlapping_sections = $DB->get_records_sql($sql, $params);
  if (empty($overlapping_sections)) {
    return true;
  }

  return false;
}

function is_available_teacher($teacher_id, $start_time, $end_time)
{
  global $DB;
  // check if the teacher exists in the database
  $teacher_exists = $DB->record_exists('user', ['id' => $teacher_id]);
  if (!$teacher_exists) {
    return false;
  }

  // check if the teacher has any course sections that overlap with the given time range
  $params = [
    'teacher_id' => $teacher_id,
    'start_time_th1' => $start_time,
    'end_time_th1' => $end_time,
    'start_time_th2' => $start_time,
    'end_time_th2' => $end_time,
    'start_time_th3' => $start_time,
    'start_time_th31' => $start_time,
    'end_time_th3' => $end_time,
    'start_time_th4' => $start_time,
    'end_time_th41' => $end_time,
    'end_time_th4' => $end_time,
  ];

  // This query checks if the teacher has any course sections that overlap with the given time range
  $sql = "SELECT *
  FROM {user} teacher
  JOIN {role_assignments} ra on teacher.id = ra.userid
  JOIN {role} r on ra.roleid = r.id
  JOIN {context} ctx on ra.contextid = ctx.id
  JOIN {course} c on ctx.instanceid = c.id
  JOIN {local_course_calendar_course_section} cs on c.id = cs.courseid
  WHERE teacher.id = :teacher_id 
        and ctx.contextlevel = 50
        and (r.shortname = 'editingteacher' or r.shortname = 'teacher')
        and 
        (
          (:start_time_th1 <= cs.class_begin_time and cs.class_end_time <= :end_time_th1)
          or (cs.class_begin_time <= :start_time_th2 and :end_time_th2 <= cs.class_end_time)
          or (cs.class_begin_time <= :start_time_th3 and :start_time_th31 < cs.class_end_time and cs.class_end_time <= :end_time_th3)
          or (:start_time_th4 <= cs.class_begin_time and cs.class_begin_time < :end_time_th41 and :end_time_th4 <= cs.class_end_time)
        )";

  $overlapping_sections = $DB->get_records_sql($sql, $params);
  if (empty($overlapping_sections)) {
    return true;
  }
  return false;

}

function create_manual_calendar(int $courses, array $teachers, int $room_addresses, int $start_time, int $end_time)
{
  // define global variable
  global $DB, $USER, $SESSION;
  $courseid = $courses;
  $roomid = $room_addresses;

  if (empty($courses)) {
    $params = [];
    $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_1.php', $params);
    redirect($base_url, "You must select one course.", 0, \core\output\notification::NOTIFY_ERROR);
  }

  if (empty($teachers)) {
    $params = [];
    if (isset($courses)) {
      $params['selected_courses'] = $courses;
    }
    $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_2.php', $params);
    redirect($base_url, "You must select at least one teacher.", 0, \core\output\notification::NOTIFY_ERROR);
  }

  if (empty($room_addresses)) {
    $params = [];
    if (isset($courses)) {
      $params['selected_courses'] = $courses;
    }

    // if (!empty($teachers) and isset($teachers)) {
    //   foreach ($teachers as $teacherid) {
    //     // Add hidden input for each selected teacher.
    //     $params['selected_teachers[]'] = $teacherid;
    //   }
    // }
    $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', $params);
    redirect($base_url, "You must select one room.", 0, \core\output\notification::NOTIFY_ERROR);
  }

  if (is_empty_room($room_addresses, $start_time, $end_time) === false) {
    $params = [];
    if (isset($courses)) {
      $params['selected_courses'] = $courses;
    }

    // if (!empty($teachers) and isset($teachers)) {
    //   foreach ($teachers as $teacherid) {
    //     // Add hidden input for each selected teacher.
    //     $params['selected_teachers[]'] = $teacherid;
    //   }
    // }

    $SESSION->start_class_time = $start_time;
    $SESSION->end_class_time = $end_time;

    $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', $params);
    redirect($base_url, "The room is not available for the specified time range.", 0, \core\output\notification::NOTIFY_ERROR);
  }

  foreach ($teachers as $teacher_id) {
    if (is_available_teacher($teacher_id, $start_time, $end_time) === false) {
      $params = [];
      if (isset($courses)) {
        $params['selected_courses'] = $courses;
      }

      // if (!empty($teachers) and isset($teachers)) {
      //   foreach ($teachers as $teacherid) {
      //     // Add hidden input for each selected teacher.
      //     $params['selected_teachers[]'] = $teacherid;
      //   }
      // }

      $SESSION->start_class_time = $start_time;
      $SESSION->end_class_time = $end_time;

      $base_url = new moodle_url('/local/course_calendar/edit_course_calendar_step_3.php', $params);
      redirect($base_url, "The teacher is not available for the specified time range.", 0, \core\output\notification::NOTIFY_ERROR);
    }
  }

  // create a new course section
  $new_course_section = new stdClass();

  // Các thuộc tính của đối tượng phải khớp với tên cột trong bảng DB
  $new_course_section->courseid = $courseid; // ID của khóa học
  $new_course_section->created_user_id = $USER->id; // ID của người dùng hiện tại (lấy từ global $USER)
  $new_course_section->modified_user_id = null;
  $new_course_section->course_room_id = $roomid; // ID của phòng học
  $new_course_section->createdtime = time(); // Timestamp hiện tại
  $new_course_section->modifiedtime = time(); // Timestamp hiện tại
  $new_course_section->class_begin_time = $start_time; // Timestamp bắt đầu (ví dụ)
  $new_course_section->class_end_time = $end_time; // Timestamp kết thúc (ví dụ)
  $new_course_section->class_total_sessions = ceil(($end_time - $start_time) / (45 * 60)); // Ví dụ
  $new_course_section->reason = null; // Hoặc một chuỗi nếu có lý do
  $new_course_section->is_cancel = 0; // 0 = false, 1 = true
  $new_course_section->is_makeup = 0;
  $new_course_section->is_accepted = 1;
  $new_course_section->visible = 1;
  if (count($teachers) >= 2) {
    $new_course_section->editing_teacher_primary_teacher = $teachers[0];
    $new_course_section->non_editing_teacher_secondary_teacher = $teachers[1];
  } else {
    $new_course_section->editing_teacher_primary_teacher = $teachers[0];
    $new_course_section->non_editing_teacher_secondary_teacher = 0;
  }

  // Chèn bản ghi vào bảng
  try {
    $inserted_id = $DB->insert_record('local_course_calendar_course_section', $new_course_section);

    if ($inserted_id !== false) {
      // Thông báo lịch dạy thay đổi đến học viên 
      $sql = "SELECT  user.*
              from {user} user
              join {role_assignments} ra on ra.userid = user.id
              join {role} r on r.id = ra.roleid
              join {context} ctx on ctx.id = ra.contextid
              join {course} c on c.id = ctx.instanceid 
              join {local_course_calendar_course_section} cs on c.id = cs.courseid 
              join {local_course_calendar_course_room} cr on cs.course_room_id = cr.id
              where cs.courseid = :course_section_id
                      and cs.class_begin_time = :course_section_class_begin_time
                      and cs.class_end_time = :course_section_class_end_time";
      $params = [
        'course_section_id' => $courses,
        'course_section_class_begin_time' => $start_time,
        'course_section_class_end_time' => $end_time
      ];
      $students = $DB->get_records_sql($sql, $params);

      // xử lý việc gửi otp code 
      $from = get_admin();
      $subject = 'You have new class section from central.';
      $th1_sub_message = 'First teacher name: '
        . $DB->get_field('user', 'firstname', ['id' => $teachers[0]])
        . $DB->get_field('user', 'lastname', ['id' => $teachers[0]]);
      $th2_sub_message = ' ';
      if (count($teachers) >= 2) {
        $th2_sub_message = 'Second teacher name: '
          . $DB->get_field('user', 'firstname', ['id' => $teachers[1]])
          . $DB->get_field('user', 'lastname', ['id' => $teachers[1]]);
      }

      $message = 'Course name: ' . $DB->get_field('course', 'fullname', ['id' => $courses])
        . "\n"
        . $th1_sub_message
        . "\n"
        . $th2_sub_message
        . "\n"
        . 'Room number: ' . $DB->get_field('local_course_calendar_course_room', 'room_number', ['id' => $room_addresses])
        . "\n"
        . 'Floor: ' . $DB->get_field('local_course_calendar_course_room', 'room_floor', ['id' => $room_addresses])
        . "\n"
        . 'Building: ' . $DB->get_field('local_course_calendar_course_room', 'room_building', ['id' => $room_addresses])
        . "\n"
        . 'Address: ' . $DB->get_field('local_course_calendar_course_room', 'ward_address', ['id' => $room_addresses])
        . ' ' . $DB->get_field('local_course_calendar_course_room', 'ward_address', ['id' => $room_addresses])
        . ' ' . $DB->get_field('local_course_calendar_course_room', 'district_address', ['id' => $room_addresses])
        . ' ' . $DB->get_field('local_course_calendar_course_room', 'province_address', ['id' => $room_addresses])
        . "\n"
        . 'Start class time: ' . date('D, d-m-Y H:i', $start_time)
        . "\n"
        . 'End class time: ' . date('D, d-m-Y H:i', $end_time);

      foreach ($students as $student) {
        $to = $student;

        if (email_to_user($to, $from, $subject, $message)) {
          $msg = "Email to user successfully";
        } else {
          $msg = 'Email to user failure.';
        }

      }

      // gửi tin nhắn thông báo đến cho giảng viên
      foreach ($teachers as $teacher) {
        $to = $DB->get_record('user', ['id' => $teacher]);
        if (email_to_user($to, $from, $subject, $message)) {
          $msg = "Email to user successfully";
        } else {
          $msg = 'Email to user failure.';
        }
      }

      unset($SESSION->edit_course_calendar_step_11_prev_course_section_form_selected_course);
      unset($SESSION->edit_course_calendar_step_3_form_selected_teachers);
      unset($SESSION->edit_course_calendar_step_3_form_selected_room_address);
      unset($SESSION->edit_course_calendar_step_3_form_selected_starttime);
      unset($SESSION->edit_course_calendar_step_3_form_selected_endtime);
      unset($SESSION->start_class_time);
      unset($SESSION->end_class_time);

      redirect(
        new moodle_url(
          '/local/course_calendar/edit_course_calendar_step_1.php',
          []
        ),
        "Inserted new course section with course section ID: " . $inserted_id,
        60,
        \core\output\notification::NOTIFY_SUCCESS
      );
      exit;
    } else {
      $params = [];
      if (isset($courses)) {
        $params['selected_courses'] = $courses;
      }

      // if (!empty($teachers) and isset($teachers)) {
      //   foreach ($teachers as $teacherid) {
      //     // Add hidden input for each selected teacher.
      //     $params['selected_teachers[]'] = $teacherid;
      //   }
      // }

      if (!empty($start_class_time) and !empty($end_class_time)) {
        $params['starttime'] = $start_class_time;
        $params['endtime'] = $end_class_time;
      }

      redirect(
        new moodle_url(
          '/local/course_calendar/edit_course_calendar_step_3.php',
          $params
        ),
        "Cannot insert record." . $inserted_id,
        60,
        \core\output\notification::NOTIFY_ERROR
      );
      exit;
    }
  } catch (\moodle_exception $e) {
    dlog($e->getTrace());
    // Xử lý các lỗi từ database, ví dụ: ràng buộc duy nhất bị vi phạm
    \core\notification::error("Error inserting data: " . $e->getMessage());
    // Ghi log lỗi để debug chi tiết hơn
    debugging("Database insert error: " . $e->getMessage(), DEBUG_DEVELOPER);
  }
}


/**
 * Summary of create_automatic_calendar_by_genetic_algorithm:
 * Hàm này dùng để lên lịch tự động cho tất cả các khóa học chưa có lịch học.
 * Luồng xử lý: Lấy ra tất cả các khóa học mà chưa có lịch học. Và xếp thời khóa biểu cho khóa học đó.
 * Hàm này chỉ xếp thời khóa biểu cho course. Còn giảng viên khi tạo khóa học đã thêm vào giảng viên vào khóa học rồi thì giảng viên sẽ phải đi dạy theo thời khóa biểu này
 * @return array $calendar là mảng chứa kết quả thời khóa biểu cần cấu trúc thời khóa biểu là $calendar[room][date][sesstion].
 */

function create_automatic_calendar_by_genetic_algorithm()
{
  global $DB;
  $courses_not_schedule_sql = "SELECT c.id courseid, c.category, c.shortname, c.startdate, c.enddate, c.visible
                              FROM {local_course_calendar_course_section} cs
                              RIGHT JOIN {course} c on cs.courseid = c.id
                              WHERE cs.courseid is null and c.id != 1 and c.visible = 1 and c.enddate >= UNIX_TIMESTAMP(NOW())";
  $params = [];
  $courses_not_schedule = $DB->get_records_sql($courses_not_schedule_sql, $params);
  $available_rooms = $DB->get_records('local_course_calendar_course_room');

  $courses_not_schedule_courseid_array = [];
  foreach ($courses_not_schedule as $course) {
    $courses_not_schedule_courseid_array[] = $course->courseid;
  }

  $available_rooms_roomid_array = [];
  foreach ($available_rooms as $room) {
    $available_rooms_roomid_array[] = $room->id;
  }

  $number_courses_not_schedule = count($courses_not_schedule);
  $number_room = count($available_rooms);
  $number_class_sessions = count(STT_CLASS_SESSIONS);
  $number_day = count(DATES);

  $initial_calendar_community = [];
  $calendar = [];

  for ($i = 0; $i < $number_room; $i++) {
    $calendar[] = [];
    for ($j = 0; $j < $number_day; $j++) {
      $calendar[$i][] = [];
      for ($k = 0; $k < $number_class_sessions; $k++) {
        $calendar[$i][$j][] = new course_session_information();
      }
    }
  }

  // init 50 null calendar 
  for ($i = 0; $i < MAX_CALENDAR_NUMBER; $i++) {
    $initial_calendar_community[] = deep_copy_calendar_array($calendar);
  }

  // pass value for 50 random calendar
  for ($i = 0; $i < MAX_CALENDAR_NUMBER; $i++) {
    $index = 0;
    $used_room_day_session_array = [];
    for ($j = 0; $j < NUMBER_COURSE_SESSION_WEEKLY * $number_courses_not_schedule; $j++) {
      // Đảm bảo rằng trong bất kỳ thời khóa biểu khởi tạo mặc định thì với mỗi môn học đều có NUMBER_COURSE_SESSION_WEEKLY.
      // Đảm bảo bằng cách duyệt qua và thêm vào n lần khóa học vào thời khóa biểu với n = NUMBER_COURSE_SESSION_WEEKLY.
      $courseid = $courses_not_schedule_courseid_array[$index];
      if ($index === $number_courses_not_schedule - 1) {
        $index = 0;
      } else {
        $index++;
      }

      $random_room = random_int(0, $number_room - 1);
      $random_day = random_int(0, $number_day - 1);
      $random_session = random_int(0, $number_class_sessions - 1);

      if (!empty($used_room_day_session_array)) {
        foreach ($used_room_day_session_array as $used) {
          while ($used[0] === $random_room && $used[1] === $random_day && $used[2] === $random_session) {
            $random_room = random_int(0, $number_room - 1);
            $random_day = random_int(0, $number_day - 1);
            $random_session = random_int(10, $number_class_sessions - 1);
          }
        }
      }
      // liên kết physical room with số thứ tự của random room 
      $roomid = $available_rooms_roomid_array[$random_room];

      $initial_calendar_community[$i][$random_room][$random_day][$random_session] = new course_session_information(
        $courses_not_schedule[$courseid]->courseid,
        $courses_not_schedule[$courseid]->shortname,
        CLASS_DURATION / TIME_SLOT_DURATION,
        $random_session,
        $random_session + (CLASS_DURATION / TIME_SLOT_DURATION),
        null,
        null,
        $random_day,
        $random_room,
        $available_rooms[$roomid]->room_number,
        $available_rooms[$roomid]->room_floor,
        $available_rooms[$roomid]->room_building,
        $available_rooms[$roomid]->ward_address,
        $available_rooms[$roomid]->district_address,
        $available_rooms[$roomid]->province_address,
      );

      $used_room_day_session_array[] = [$random_room, $random_day, $random_session];
    }

  }

  $initial_calendar_community = genetic_algorithm($initial_calendar_community);

  if (empty($initial_calendar_community)) {
    return [];
  }
  return $initial_calendar_community[0];
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////OLD: NOT USE: ALGORITHM: RECURSIVE SWAP ALGORITHM///////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////

class TimetableGenerator
{
  private $course_array;
  private $room_array;
  private $time_slot_array;
  private $number_room;
  private $number_day;
  private $number_class_sessions;
  private $max_level_recursive;
  private $max_number_of_call_recursive;
  private $number_of_call_recursive;

  public function __construct(
    $course_array = null,
    $room_array = null,
    $time_slot_array = null,
    $number_room = null,
    $number_day = null,
    $number_class_sessions = null,
    $max_level_recursive = null,
    $max_number_of_call_recursive = null,
    $number_of_call_recursive = null
  ) {
    $this->course_array = $course_array;
    $this->room_array = $room_array;
    $this->time_slot_array = $time_slot_array;
    $this->number_room = $number_room;
    $this->number_day = $number_day;
    $this->number_class_sessions = $number_class_sessions;
    $this->max_level_recursive = $max_level_recursive;
    $this->max_number_of_call_recursive = $max_number_of_call_recursive;
    $this->number_of_call_recursive = $number_of_call_recursive;
  }

  public function get_time_slot_array()
  {
    return $this->time_slot_array;
  }
  /**
   * Summary of format_time_table
   * Hàm này dùng để định dạng lại time_table theo format calendar[room][day][session].
   * @return array Trả về mảng calendar là định dạng mới của time_table. Định dạng của calendar là calendar[room][day][session].
   */
  public function format_time_table($time_slot_array)
  {
    $calendar = [];
    for ($i = 0; $i < $this->number_room; $i++) {
      $calendar[] = [];
      for ($j = 0; $j < $this->number_day; $j++) {
        $calendar[$i][] = [];
        for ($k = 0; $k < $this->number_class_sessions; $k++) {
          foreach ($time_slot_array as $time_slot) {
            if ($time_slot->room == $i and $time_slot->date == $j and $time_slot->session == $k) {
              $calendar[$i][$j][] = new course_session_information(
                $time_slot->course_session_information->courseid,
                $time_slot->course_session_information->course_name,
                $time_slot->course_session_information->course_session_length,
                $time_slot->course_session_information->course_session_start_time,
                $time_slot->course_session_information->course_session_end_time,
                $time_slot->course_session_information->editting_teacher_array,
                $time_slot->course_session_information->non_editting_teacher_array,
                $time_slot->course_session_information->date,
                $time_slot->course_session_information->random_room_stt,
                $time_slot->course_session_information->room,
                $time_slot->course_session_information->floor,
                $time_slot->course_session_information->building,
                $time_slot->course_session_information->ward,
                $time_slot->course_session_information->district,
                $time_slot->course_session_information->province
              );

              break;
            }
          }
        }
      }
    }

    return $calendar;
  }

  /**
   * Summary of init_conflict_position_array
   * Hàm này thực hiện khởi tạo một mảng chứa các conflict position.
   * @return conflict_position[]
   */
  public function init_conflict_position_array()
  {
    // chuẩn bị để tạo mảng chứa các conflict_position nếu ta cố gắng đặt một hoạt động Ai vào vị trí time_slot_index.
    // mảng conflict_position này có độ dài đúng bằng mảng time_slot_array.
    $conflict_position_array = [];
    $time_slot_index = 0;
    for ($i = 0; $i < $this->number_room; $i++) {
      for ($j = 0; $j < $this->number_day; $j++) {
        for ($k = 0; $k < $this->number_class_sessions; $k++) {
          $conflict_position_array[] = new conflict_position(
            $i,
            $j,
            $k,
            $time_slot_index,
            0,
            []
          );
          $time_slot_index++;
        }
      }
    }

    return $conflict_position_array;
  }

  /**
   * Summary of deep_copy_time_slot_array
   * Hàm này dùng để sao chép sâu mảng dữ liệu time_slot_array[]
   * @param mixed $time_slot_array
   * @return time_slot[]
   */
  public function deep_copy_time_slot_array($time_slot_array)
  {
    $clone = [];
    foreach ($time_slot_array as $time_slot) {
      $clone[] = new time_slot(
        $time_slot->room,
        $time_slot->date,
        $time_slot->session,
        $time_slot->course_session_information->get_copy(),
        $time_slot->time_slot_index,
        $time_slot->is_occupied,
        $time_slot->is_occupied_by_course_in_prev_time_slot
      );
    }
    return $clone;
  }
  /**
   * Summary of deep_copy_course_array
   * Hàm này dùng để copy lại tất cả các phần tử của mảng course array.
   * @param mixed $course_array
   * @return array
   */
  public function deep_copy_course_array($course_array)
  {
    $clone = [];
    foreach ($course_array as $course) {
      $clone[] = $course;
    }
    return $clone;
  }

  /**
   * Summary of check_position
   * Hàm này dùng để thực hiện kiểm tra tất cả các ràng buộc tại vị trí time_slot trước khi điền thông tin của course vào.
   * @param mixed $time_slot
   * @param mixed $course
   * @return bool trả về true nếu tất cả các ràng buộc được đáp ứng. Trái lại trả về false.
   */
  public function check_position(
    $time_slot,
    $course
  ) {
    // prepare data
    $deep_copy_time_slot_array = $this->deep_copy_time_slot_array($this->time_slot_array);
    // foreach ($deep_copy_time_slot_array as $temp_time_slot) {
    //   if ($time_slot->time_slot_index == $temp_time_slot->time_slot_index) {
    //     if ($this->set_course_information_to_time_slot($deep_copy_time_slot_array, $temp_time_slot, $course)) {
    //       break;
    //     } else {
    //       throw new Exception(
    //         "Error Processing Request: Check position at " . $temp_time_slot->room . "-" . $temp_time_slot->date . "-" . $temp_time_slot->session . "-" . $temp_time_slot->course_session_information->courseid,
    //         1
    //       );
    //     }
    //   }
    // }
    $calendar = $this->format_time_table($deep_copy_time_slot_array);

    if (
      compute_score_violation_of_rule_class_duration_in_one_session(
        $time_slot->session,
        $course->class_duration
      ) == 0
      and
      compute_score_violation_of_rule_class_overtime(
        $time_slot->session,
        $time_slot->session + $course->class_duration - 1,
        $course->class_duration
      ) == 0
      and compute_score_violation_of_rule_class_session_continuously(
        $calendar
      ) == 0
      and
      compute_score_violation_of_rule_duplicate_course_at_same_room_at_same_time(
        $calendar,
        $time_slot->room,
        $time_slot->date,
        $time_slot->session
      ) == 0
      and
      compute_score_violation_of_rule_forbidden_session(
        $time_slot->date,
        $time_slot->session,
        $course->class_duration
      ) == 0
      and compute_score_violation_of_rule_holiday($time_slot->date) == 0
      and compute_score_violation_of_rule_largest_teaching_hours($calendar) == 0
      and compute_score_violation_of_rule_not_enough_number_of_course_session_weekly(
        $calendar,
        $course->courseid,
        $course->number_course_session_weekly
      ) == 0
      and compute_score_violation_of_rule_priority_order_of_class_session($calendar) == 0
      and compute_score_violation_of_rule_room_gap_between_class_session($calendar) == 0
      and compute_score_violation_of_rule_student_study_all_day($calendar) == 0
      and compute_score_violation_of_rule_study_double_session_of_same_course_on_one_day(
        $calendar,
        $course->courseid
      ) == 0
      and compute_score_violation_of_rule_time_gap_between_class_session($calendar) == 0
    ) {
      return true;
    }

    return false;

  }

  /**
   * Summary of set_course_information_to_time_slot
   * Hàm này dùng để ghi dữ liệu của course vào trong một time_slot
   * @param mixed $time_slot
   * @param mixed $course
   * @return bool trả về true nếu việc ghi dữ liệu là thành công và ngược lại trả về false.
   */
  public function set_course_information_to_time_slot(&$time_slot_array, &$time_slot, $course)
  {
    $time_slot->course_session_information = new course_session_information(
      $course->courseid,
      $course->shortname,
      $course->class_duration,
      $time_slot->session,
      $course->class_duration + $time_slot->session,
      null,
      null,
      $time_slot->date,
      $time_slot->room,
      $time_slot->room,
    );
    $time_slot->is_occupied = true;
    $time_slot->is_occupied_by_course_in_prev_time_slot = false;

    // Thực hiện việc đánh dấu các ô liên tiếp trong time_slot_array là đã bị chiếm dụng nếu có một course nào đó có độ dài > 1
    if ($course->class_duration > 1) {
      $number_time_slot_array = count($time_slot_array);

      for ($i = 0; $i < $number_time_slot_array; $i++) {
        $temp_time_slot = $time_slot_array[$i];
        if ($temp_time_slot->time_slot_index == $time_slot->time_slot_index) {
          for ($j = 1; $j < $course->class_duration; $j++) {
            if ($i + $j < $number_time_slot_array) {
              $temp_time_slot = $time_slot_array[$i + $j];
              $temp_time_slot->is_occupied = true;
              $temp_time_slot->is_occupied_by_course_in_prev_time_slot = true;
            }
          }

          break;
        }
      }

    }

    return true;
  }

  /**
   * Summary of get_conflict_items_at_this_time_slot
   * Hàm này dùng để thực hiện lấy ra tất cả các thông tin về course đụng độ 
   * khi tiến hành đặt thêm thông tin mới về một course vào trong một time_slot
   * Các thông tin của course đụng độ có thể nằm tại vị trí time_slot hoặc nằm xung quanh vị trí time_slot.
   * @param mixed $time_slot_array
   * @param mixed $time_slot
   * @param mixed $course
   * @return array trả về danh sách các course.
   */
  public function get_conflict_items_at_this_time_slot($time_slot, $course)
  {
    $conflict_items_array = [];

    // prepare data
    $deep_copy_time_slot_array = $this->deep_copy_time_slot_array($this->time_slot_array);
    foreach ($deep_copy_time_slot_array as $temp_time_slot) {
      if ($time_slot->time_slot_index == $temp_time_slot->time_slot_index) {
        if ($this->set_course_information_to_time_slot($deep_copy_time_slot_array, $temp_time_slot, $course)) {
          break;
        } else {
          throw new Exception(
            "Error Processing Request: Check position at " . $temp_time_slot->room . "-" . $temp_time_slot->date . "-" . $temp_time_slot->session . "-" . $temp_time_slot->course_session_information->courseid,
            1
          );
        }
      }
    }
    $calendar = $this->format_time_table($deep_copy_time_slot_array);

    // check condition
    if (
      compute_score_violation_of_rule_class_duration_in_one_session(
        $time_slot->session,
        $course->class_duration
      ) > 0
    ) {
      $conflict_items_array += [];
    }

    if (
      compute_score_violation_of_rule_class_overtime(
        $time_slot->session,
        $time_slot->session + $course->class_duration,
        $course->class_duration
      ) > 0
    ) {
      $conflict_items_array += [];
    }

    if (
      compute_score_violation_of_rule_class_session_continuously(
        $calendar
      ) > 0
    ) {
      $conflict_items_array += get_conflict_item_of_rule_class_session_continuously($calendar);
    }

    if (
      compute_score_violation_of_rule_duplicate_course_at_same_room_at_same_time(
        $calendar,
        $time_slot->room,
        $time_slot->date,
        $time_slot->session
      ) > 0
    ) {
      $conflict_items_array += get_conflict_item_of_rule_duplicate_course_at_same_room_at_same_time(
        $calendar,
        $time_slot
      );
    }

    if (
      compute_score_violation_of_rule_forbidden_session(
        $time_slot->date,
        $time_slot->session,
        $course->class_duration
      ) > 0
    ) {
      $conflict_items_array += [];
    }

    if (compute_score_violation_of_rule_holiday($time_slot->date) > 0) {
      $conflict_items_array += [];
    }

    // if (compute_score_violation_of_rule_largest_teaching_hours($calendar) > 0) {
    //   $conflict_items_array += get_conflict_item_of_rule_largest_teaching_hours($calendar);
    // }

    // if (
    //   compute_score_violation_of_rule_not_enough_number_of_course_session_weekly(
    //     $calendar,
    //     $course->courseid,
    //     $course->number_course_session_weekly
    //   ) > 0
    // ) {
    //   $conflict_items_array += get_conflict_item_of_rule_not_enough_number_of_course_session_weekly(
    //     $calendar,
    //     $course->courseid,
    //     $course->number_course_session_weekly
    //   );
    // }

    // if (compute_score_violation_of_rule_priority_order_of_class_session($calendar) > 0) {
    //   $conflict_items_array += get_conflict_item_of_rule_priority_order_of_class_session($calendar);
    // }

    // if (compute_score_violation_of_rule_room_gap_between_class_session($calendar) > 0) {
    //   $conflict_items_array += get_conflict_item_of_rule_room_gap_between_class_session($calendar);
    // }

    // if (compute_score_violation_of_rule_student_study_all_day($calendar) > 0) {
    //   $conflict_items_array += get_conflict_item_of_rule_student_study_all_day($calendar);
    // }

    // if (
    //   compute_score_violation_of_rule_study_double_session_of_same_course_on_one_day(
    //     $calendar,
    //     $course->courseid
    //   ) > 0
    // ) {
    //   $conflict_items_array += get_conflict_item_of_rule_study_double_session_of_same_course_on_one_day(
    //     $calendar,
    //     $course->courseid
    //   );
    // }

    // if (
    //   compute_score_violation_of_rule_time_gap_between_class_session($calendar) > 0
    // ) {
    //   $conflict_items_array += get_conflict_item_of_rule_time_gap_between_class_session($calendar);
    // }

    return $conflict_items_array;
  }

  /**
   * Summary of remove_conflict_items_from_time_slot
   * Hàm này dùng để gỡ các course đụng độ ra khỏi time_slot này hoặc time_slot xung quanh đó.
   * @param mixed $time_slot
   * @param mixed $conflict_position_array
   * @param mixed $position
   * @return array Trả về một mảng chứa thông tin các course đã bị gỡ ra.
   */
  public function remove_conflict_items_from_time_slot($time_slot, $conflict_position_array, $position)
  {
    $courses = [];
    return $courses;
  }

  /**
   * Summary of try_place_course_to_time_slot.
   * Đây là hàm đệ quy chính của giải thuật create time table by recursive swap.
   * @param mixed $course Thông tin của khóa học
   * @param mixed $level_recursive Thông tin về mức đệ quy đang được gọi
   * @return bool return true if place course to time slot successfully else false.
   */
  public function try_place_course_to_time_slot(
    $course,
    $level_recursive
  ) {

    // Check stop condition
    if ($level_recursive > MAX_LEVEL_RECURSIVE or $level_recursive > $this->max_level_recursive) {
      return false;
    }

    $this->number_of_call_recursive++;
    if ($this->number_of_call_recursive > $this->max_number_of_call_recursive) {
      return false;
    }

    // 2) Try to place each activity (A_i) in an allowed time slot, following the above order, one at a time.
    // Search for an available slot (T_j) for A_i, in which this activity can be placed respecting the constraints.
    // If more slots are available, choose a random one. If none is available, do recursive swapping:
    foreach ($this->time_slot_array as $time_slot) {
      $is_put_course_to_time_slot_successfully = false;
      if ($this->check_position($time_slot, $course)) {
        $is_put_course_to_time_slot_successfully = $this->set_course_information_to_time_slot($this->time_slot_array, $time_slot, $course);
        if ($is_put_course_to_time_slot_successfully) {
          // log
          echo "<pre>";
          echo var_dump($time_slot);
          echo "</pre>";

          $this->course_array = array_filter($this->course_array, function ($course_param) use ($course) {
            return $course_param->courseid != $course->courseid;
          });
          return true;
        }
      }
    }

    if (!$is_put_course_to_time_slot_successfully) {
      //     2 a) For each time slot T_j, consider what happens if you put A_i into T_j. There will be a list of other
      // activities which don't agree with this move (for instance, activity A_k is on the same slot T_j and has the
      // same teacher or same students as A_i). Keep a list of conflicting activities for each time slot T_j.

      $conflict_position_array = $this->init_conflict_position_array();
      foreach ($this->time_slot_array as $time_slot) {
        $conflict_items_array = $this->get_conflict_items_at_this_time_slot($time_slot, $course);

        foreach ($conflict_position_array as $conflict_position) {
          if ($conflict_position->time_slot_index == $time_slot->time_slot_index) {
            $conflict_position->set_value(
              $time_slot->room,
              $time_slot->date,
              $time_slot->session,
              $time_slot->time_slot_index,
              count($conflict_items_array),
              $conflict_items_array
            );

            break;
          }
        }
      }

      // 2 b) Choose a slot (T_j) with lowest number of conflicting activities. Say the list of activities in this
      // slot contains 3 activities: A_p, A_q, A_r.
      $conflict_items_number = [];
      foreach ($conflict_position_array as $conflict_position) {
        $conflict_items_number[] = $conflict_position->conflict_items_number_at_this_time_slot;
      }
      array_multisort(
        $conflict_items_number,
        SORT_ASC,
        SORT_REGULAR,
        $conflict_position_array
      );

      foreach ($conflict_position_array as $position) {
        // Thực hiện việc lưu lại thông tin của thời khóa biểu tại thời điểm này 
        // nếu không đặt được thông tin course mới vào thì khôi phục lại bằng dữ liệu này
        $backup_time_slot_array = $this->deep_copy_time_slot_array($this->time_slot_array);
        $backup_course_array = $this->deep_copy_course_array($this->course_array);

        $time_slot = $this->time_slot_array[0];
        for ($i = 0; $i < count($this->time_slot_array); $i++) {
          if ($this->time_slot_array[$i]->time_slot_index == $position->time_slot_index) {
            $time_slot = $this->time_slot_array[$i];
            break;
          }
        }

        // 2 c) Place A_i at T_j and make A_p, A_q, A_r unallocated.

        // check here please
        // Chỗ này bắt buộc phải thực hiện được việc set course information sau khi đã xóa bỏ thông tin conflict mới hợp lệ
        $is_put_course_to_time_slot_successfully = $this->set_course_information_to_time_slot($this->time_slot_array, $time_slot, $course);
        $unplaced_courses_array = [];
        if ($is_put_course_to_time_slot_successfully) {
          $unplaced_courses_array = $this->remove_conflict_items_from_time_slot(
            $time_slot,
            $conflict_position_array,
            $position
          );

          //       2 d) Recursively try to place A_p, A_q, A_r (if the level of recursion is not too large, say 14,
          // and if the total number of recursive calls counted since step (2) on A_i began is not too large, say 2*n),
          // as in step (2).

          if (!empty($unplaced_courses_array)) {
            $is_success = true;
            foreach ($unplaced_courses_array as $course) {
              if (!$this->try_place_course_to_time_slot($course, $level_recursive + 1)) {
                $is_success = false;
                break;
              }
            }

            //           2 e) If successfully placed A_p, A_q, A_r, return with success, otherwise try other time slots
            // (go to step (2 b) and choose the next best time slot).

            if ($is_success) {
              return true;
            } else {
              // Khôi phục lại dữ liệu tại các time_slot do đệ quy thực hiện thay đổi dữ liệu
              $this->time_slot_array = $backup_time_slot_array;
              $this->course_array = $backup_course_array;
            }
          }
        }
      }
    }

    // 2 f) If all (or a reasonable number of) time slots were tried unsuccessfully, return without success.
    return false;
  }

  /**
   * Summary of generate_time_table.
   * Hàm này thực hiện việc khởi tạo time table và trực tiếp gọi hàm đệ quy để tạo timetable.
   * @return void 
   */
  public function generate_time_table()
  {
    // Bước 1: Sort the activities, most difficult first. Not critical step, but speeds up the algorithm maybe 10 times or more.

    array_multisort(
      array_column($this->course_array, 'class_duration'),
      SORT_DESC,
      SORT_REGULAR,
      array_column($this->course_array, 'number_course_session_weekly'),
      SORT_DESC,
      SORT_REGULAR,
      array_column($this->course_array, 'number_student_on_course'),
      SORT_DESC,
      SORT_REGULAR,
      $this->course_array
    );

    // Bước 2: Try to place each activity (A_i) in an allowed time slot, following the above order, one at a time.
    // Search for an available slot (T_j) for A_i, in which this activity can be placed respecting the constraints.
    // If more slots are available, choose a random one. If none is available, do recursive swapping:
    foreach ($this->course_array as $course) {
      $this->number_of_call_recursive = 0;

      // Bước 2 g) If we are at level 0, and we had no success in placing A_i, place it like in steps (2 b) and (2 c),
      // but without recursion. We have now 3 - 1 = 2 more activities to place. Go to step (2) (some methods to
      // avoid cycling are used here).
      if (!$this->try_place_course_to_time_slot($course, 0)) {
        // prepare data
        $conflict_position_array = $this->init_conflict_position_array();
        foreach ($this->time_slot_array as $time_slot) {
          $conflict_items_array = $this->get_conflict_items_at_this_time_slot($time_slot, $course);

          foreach ($conflict_position_array as $conflict_position) {
            if ($conflict_position->time_slot_index == $time_slot->time_slot_index) {
              $conflict_position->set_value(
                $time_slot->room,
                $time_slot->date,
                $time_slot->session,
                $time_slot->time_slot_index,
                count($conflict_items_array),
                $conflict_items_array
              );

              break;
            }
          }
        }

        $conflict_items_number = [];
        foreach ($conflict_position_array as $conflict_position) {
          $conflict_items_number[] = $conflict_position->conflict_items_number_at_this_time_slot;
        }
        array_multisort(
          $conflict_items_number,
          SORT_ASC,
          SORT_REGULAR,
          $conflict_position_array
        );

        //       2 b) Choose a slot (T_j) with lowest number of conflicting activities. Say the list of activities in this
        // slot contains 3 activities: A_p, A_q, A_r.

        foreach ($conflict_position_array as $position) {
          $time_slot = $this->time_slot_array[0];
          for ($i = 0; $i < count($this->time_slot_array); $i++) {
            if ($this->time_slot_array[$i]->time_slot_index == $position->time_slot_index) {
              $time_slot = $this->time_slot_array[$i];
              break;
            }
          }

          // 2 c) Place A_i at T_j and make A_p, A_q, A_r unallocated.

          $is_put_course_to_time_slot_successfully = $this->set_course_information_to_time_slot($this->time_slot_array, $time_slot, $course);
          $unplaced_courses_array = [];
          if ($is_put_course_to_time_slot_successfully) {
            $unplaced_courses_array = $this->remove_conflict_items_from_time_slot(
              $time_slot,
              $conflict_position_array,
              $position
            );

            if (!empty($unplaced_courses_array)) {
              foreach ($unplaced_courses_array as $course) {
                $this->try_place_course_to_time_slot($course, 0);
              }
            }
          }

        }
      }
    }
  }

  /**
   * Summary of create_automatic_calendar_by_recursive_swap_algorithm
   * Hàm này thực hiện việc truy xuất các dữ liệu cần thiết cho việc tạo thời khóa biểu và tiến hành gọi hàm 
   * generate_time_table() để tạo thời khóa biểu.
   * @return TimetableGenerator
   */
  public function create_automatic_calendar_by_recursive_swap_algorithm()
  {
    // Lấy ra các course mà chưa được tạo lịch học 
    global $DB;
    $courses_not_schedule_sql = "SELECT c.id courseid, c.category, c.shortname, c.startdate, 
                                        c.enddate, c.visible, cc.class_duration, cc.number_course_session_weekly, 
                                        cc.number_student_on_course
                                FROM mdl_local_course_calendar_course_section cs
                                RIGHT JOIN mdl_course c on cs.courseid = c.id
                                join mdl_local_course_calendar_course_config_for_calendar cc on cc.courseid = c.id
                                WHERE cs.courseid is null 
                                      and c.id != 1 
                                      and c.visible = 1 
                                      and c.enddate >= UNIX_TIMESTAMP(NOW())";
    $params = [];
    $courses_not_schedule = $DB->get_records_sql($courses_not_schedule_sql, $params);

    // Tạo dữ liệu course_list bao gồm những course ta sẽ cần xếp thời khóa biểu và cần sắp xếp chúng theo thứ tự ưu tiên
    // Thứ tự ưu tiên là thời gian diễn ra buổi học (số tiết học trên một buổi)
    // Số buổi học trên một tuần
    // Phải tạo thêm course_list để lưu là vì khi chúng ta thực hiện thêm course vào trong thời khóa biểu sẽ cần xóa course đó ra 
    // và khi cần unlocated course thì cần nơi để lưu trở lại.
    // và ta cũng cần sort lại mảng course này để sắp xếp lại theo thứ tự ưu tiên
    // nếu ta làm trên mảng chính luôn có thể sẽ gây sai sót dữ liệu.

    // và cũng vì cái $course_not_schedule đang được đánh index theo cột đầu tiên là courseid ta không biết chắc các id này có theo thứ tự không
    // nó sẽ khó cho quá trình duyệt qua mảng sẽ gây thiếu sót, khó khăn khi chỉ có thể dùng mỗi foreach.

    $course_array = [];
    foreach ($courses_not_schedule as $course) {
      $course_array[] = $course;
    }

    // Lấy ra các phòng học sẵn có của trung tâm
    $available_rooms = $DB->get_records('local_course_calendar_course_room');

    // Lưu lại các room vào mảng có index theo thứ tự từ 0->n
    $room_array = [];
    foreach ($available_rooms as $room) {
      $room_array[] = $room;
    }

    // các biến được chuẩn bị cho việc tạo mảng time slot
    $number_courses_not_schedule = count($courses_not_schedule);
    $number_room = count($available_rooms);
    $number_class_sessions = count(STT_CLASS_SESSIONS);
    $number_day = count(DATES);
    $time_slot_array = [];
    $time_slot_index = 0;

    for ($i = 0; $i < $number_room; $i++) {
      for ($j = 0; $j < $number_day; $j++) {
        for ($k = 0; $k < $number_class_sessions; $k++) {
          $time_slot_array[] = new time_slot(
            $i,
            $j,
            $k,
            new course_session_information(),
            $time_slot_index,
            false,
            false
          );
          $time_slot_index++;
        }
      }
    }

    // hai biến này dùng để giới hạn lại số lần gọi đệ quy để xử lý bài toán.
    // độ sâu tối đa mà thuật toán có thể gọi đến là 16 - theo hướng dẫn của giải thuật fet application - file doc
    // Số lần có thể gọi đệ quy là bằng 2 * số hoạt động có thể có - trong bài toán này số hoạt động có thể có là $number_course_not_schedule.
    $max_level_recursive = 16;
    $number_of_call_recursive = 0;
    $max_number_of_call_recursive = 2 * $number_courses_not_schedule;

    // Khởi tạo calendar kết quả
    // Tạo calendar bằng giải thuật recursive swap
    // Trả về kết quả.
    $time_table = new TimetableGenerator(
      $course_array,
      $room_array,
      $time_slot_array,
      $number_room,
      $number_day,
      $number_class_sessions,
      $max_level_recursive,
      $max_number_of_call_recursive,
      $number_of_call_recursive
    );

    $time_table->generate_time_table();

    return $time_table;
  }
}

class helper
{
  /**
   * Generates a sortable table header link with an arrow icon indicating the current sort direction.
   * @param object $current_page The current page object containing the URL.
   * @param string $column_name The name of the column to sort.
   * @param string $display_column_name The display name of the column.
   * @param string $current_sort_column The currently sorted column.
   * @param string $current_direction The current sort direction ('asc' or 'desc').
   * @return string HTML output for the sortable header link with an arrow icon. 
   */
  public static function make_sort_table_header_helper(
    $current_page,
    $column_name,
    $display_column_name,
    $current_sort_column,
    $current_direction,
    $param_array = []
  ) {
    global $OUTPUT;
    $new_direction = '';
    if ($current_sort_column === $column_name and $current_direction === 'asc') {
      $new_direction = 'desc';
    } else {
      $new_direction = 'asc';
    }

    $param_array += ['sort' => $column_name, 'direction' => $new_direction];
    $new_url = new moodle_url($current_page->url, $param_array);

    $arrow_up = new pix_icon('t/sort_asc', $display_column_name, 'core', ['class' => 'icon-inline']);
    $arrow_down = new pix_icon('t/sort_desc', $display_column_name, 'core', ['class' => 'icon-inline']);
    $arrow = $arrow_up;

    if ($current_sort_column === $column_name) {
      // Mũi tên lên/xuống
      $arrow = ($current_direction === 'asc') ? $arrow_up : $arrow_down;
    }

    return $display_column_name . ' ' . $OUTPUT->action_icon(
      $new_url,
      $arrow
    );
  }

}

///////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////NEW ALGORITHM: RECURSIVE SWAP/////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////


class time_table_generator
{
  private $course_array;
  private $room_array;
  private $time_slot_array;
  private $teacher_and_non_teacher_array;
  private $number_room;
  private $number_day;
  private $number_class_sessions;
  private $max_level_recursive;
  private $max_number_of_call_recursive;
  private $level_recursive;
  private $number_of_call_recursive;
  private $earliest_start_date_timestamp;
  private $latest_end_date_timestamp;
  private $file_handle;
  private $putted_course;
  private $unlocate_course_array;

  public function __construct(
    $course_array = null,
    $room_array = null,
    $time_slot_array = null,
    $number_room = null,
    $number_day = null,
    $number_class_sessions = null,
    $max_level_recursive = null,
    $max_number_of_call_recursive = null,
    $number_of_call_recursive = null,
    $teacher_and_non_teacher_array = null,
    $level_recursive = null,
    $earliest_start_date_timestamp = null,
    $latest_end_date_timestamp = null,
    $putted_course = null,
    $unlocate_course_array = null,

  ) {
    $this->course_array = $course_array;
    $this->room_array = $room_array;
    $this->time_slot_array = $time_slot_array;
    $this->number_room = $number_room;
    $this->number_day = $number_day;
    $this->number_class_sessions = $number_class_sessions;
    $this->max_level_recursive = $max_level_recursive;
    $this->max_number_of_call_recursive = $max_number_of_call_recursive;
    $this->number_of_call_recursive = $number_of_call_recursive;
    $this->teacher_and_non_teacher_array = $teacher_and_non_teacher_array;
    $this->level_recursive = $level_recursive;
    $this->earliest_start_date_timestamp = $earliest_start_date_timestamp;
    $this->latest_end_date_timestamp = $latest_end_date_timestamp;
    $this->putted_course = $putted_course;
    $this->unlocate_course_array = $unlocate_course_array;
  }

  public function set_file_handle_for_write_log($file_hanlde)
  {
    $this->file_handle = $file_hanlde;
  }

  public function get_file_handle_for_write_log()
  {
    return $this->file_handle;
  }

  /**
   * So sánh phần giờ của hai timestamp, xác định xem giờ của timestamp đầu tiên có sớm hơn giờ của timestamp thứ hai không.
   *
   * Hàm này bỏ qua phần ngày và chỉ so sánh phần giờ, phút, giây trong timestamp.
   * Thích hợp cho các trường hợp chỉ cần so sánh dựa trên thời gian (không bao gồm ngày).
   *
   * @param int $timestamp_1 Timestamp đầu tiên (Unix timestamp).
   * @param int $timestamp_2 Timestamp thứ hai (Unix timestamp).
   * @return bool Trả về true nếu phần giờ của timestamp đầu tiên sớm hơn phần giờ của timestamp thứ hai; ngược lại trả về false.
   */
  public function is_smaller_hour_and_minute($timestamp_1, $timestamp_2)
  {
    // Giả sử bạn có hai timestamp
    $timestamp1 = $timestamp_1;
    $timestamp2 = $timestamp_2;

    // Tạo đối tượng DateTime từ timestamp
    $date1 = new \DateTime();
    $date1->setTimestamp($timestamp1);

    $date2 = new \DateTime();
    $date2->setTimestamp($timestamp2);

    // Tạo các đối tượng DateTime mới chỉ với phần giờ
// Sử dụng ngày gốc để tránh các vấn đề liên quan đến timezone
    $hour_only1 = new \DateTime($date1->format('H:i'));
    $hour_only2 = new \DateTime($date2->format('H:i'));

    // So sánh hai đối tượng
    if ($hour_only1 < $hour_only2) {
      return true;
    }

    return false;
  }

  /**
   * Compares the hour part of two timestamps, determining if the hour of the first timestamp is greater than the hour of the second.
   *
   * This function ignores the date part and only compares the hour, minute, and second components of the timestamps.
   * It is suitable for scenarios where you need to compare based solely on time, without considering the date.
   *
   * @param int $timestamp_1 The first timestamp (Unix timestamp).
   * @param int $timestamp_2 The second timestamp (Unix timestamp).
   * @return bool Returns true if the hour part of the first timestamp is greater than the hour part of the second timestamp; otherwise, returns false.
   */
  public function is_greater_hour_and_minute($timestamp_1, $timestamp_2)
  {
    // Giả sử bạn có hai timestamp
    $timestamp1 = $timestamp_1;
    $timestamp2 = $timestamp_2;

    // Tạo đối tượng DateTime từ timestamp
    $date1 = new \DateTime();
    $date1->setTimestamp($timestamp1);

    $date2 = new \DateTime();
    $date2->setTimestamp($timestamp2);

    // Tạo các đối tượng DateTime mới chỉ với phần giờ
// Sử dụng ngày gốc để tránh các vấn đề liên quan đến timezone
    $hour_only1 = new \DateTime($date1->format('H:i'));
    $hour_only2 = new \DateTime($date2->format('H:i'));

    // So sánh hai đối tượng
    if ($hour_only1 > $hour_only2) {
      return true;
    }

    return false;
  }

  /**
   * Compares two timestamps to determine if they represent the same time (ignoring the date part).
   *
   * @param int $timestamp_1 The first timestamp.
   * @param int $timestamp_2 The second timestamp.
   * @return bool Returns true if the time parts of the two timestamps are identical; otherwise, returns false.
   */
  public function is_equal_hour_and_minute($timestamp_1, $timestamp_2)
  {
    // Giả sử bạn có hai timestamp
    $timestamp1 = $timestamp_1;
    $timestamp2 = $timestamp_2;

    // Tạo đối tượng DateTime từ timestamp
    $date1 = new \DateTime();
    $date1->setTimestamp($timestamp1);

    $date2 = new \DateTime();
    $date2->setTimestamp($timestamp2);

    // Tạo các đối tượng DateTime mới chỉ với phần giờ
// Sử dụng ngày gốc để tránh các vấn đề liên quan đến timezone
    $hour_only1 = new \DateTime($date1->format('H:i'));
    $hour_only2 = new \DateTime($date2->format('H:i'));

    // So sánh hai đối tượng
    if ($hour_only1 == $hour_only2) {
      return true;
    }

    return false;
  }

  /////////////////////////////////////////////////////////////////////////////////////

  /**
   * So sánh phần ngày - tháng - năm của hai timestamp, xác định xem ngày - tháng - năm của timestamp đầu tiên có sớm hơn ngày - tháng - năm của timestamp thứ hai không.
   *
   * Hàm này chỉ so sánh phần ngày - tháng - năm, trong timestamp.
   * Thích hợp cho các trường hợp chỉ cần so sánh dựa trên thời gian .
   *
   * @param int $timestamp_1 Timestamp đầu tiên (Unix timestamp).
   * @param int $timestamp_2 Timestamp thứ hai (Unix timestamp).
   * @return bool Trả về true nếu phần ngày - tháng - năm của timestamp đầu tiên sớm hơn phần ngày - tháng - năm của timestamp thứ hai; ngược lại trả về false.
   */
  public function is_smaller_day($timestamp_1, $timestamp_2)
  {
    // Giả sử bạn có hai timestamp
    $timestamp1 = $timestamp_1;
    $timestamp2 = $timestamp_2;

    // Tạo đối tượng DateTime từ timestamp
    $date1 = new \DateTime();
    $date1->setTimestamp($timestamp1);

    $date2 = new \DateTime();
    $date2->setTimestamp($timestamp2);

    // Tạo các đối tượng DateTime mới chỉ với phần ngày - tháng - năm
// Sử dụng ngày gốc để tránh các vấn đề liên quan đến timezone
    $day_only1 = new \DateTime($date1->format('d-m-Y'));
    $day_only2 = new \DateTime($date2->format('d-m-Y'));

    // So sánh hai đối tượng
    if ($day_only1 < $day_only2) {
      return true;
    }

    return false;
  }

  /**
   * Compares the date-month-year part of two timestamps, determining if the date-month-year of the first timestamp is greater than the date-month-year of the second.
   *
   * This function only compares the date-month-yearcomponents of the timestamps.
   * It is suitable for scenarios where you need to compare based solely on time
   *
   * @param int $timestamp_1 The first timestamp (Unix timestamp).
   * @param int $timestamp_2 The second timestamp (Unix timestamp).
   * @return bool Returns true if the date-month-year part of the first timestamp is greater than the date-month-year part of the second timestamp; otherwise, returns false.
   */
  public function is_greater_day($timestamp_1, $timestamp_2)
  {
    // Giả sử bạn có hai timestamp
    $timestamp1 = $timestamp_1;
    $timestamp2 = $timestamp_2;

    // Tạo đối tượng DateTime từ timestamp
    $date1 = new \DateTime();
    $date1->setTimestamp($timestamp1);

    $date2 = new \DateTime();
    $date2->setTimestamp($timestamp2);

    // Tạo các đối tượng DateTime mới chỉ với phần ngày - tháng - năm
// Sử dụng ngày gốc để tránh các vấn đề liên quan đến timezone
    $day_only1 = new \DateTime($date1->format('d-m-Y'));
    $day_only2 = new \DateTime($date2->format('d-m-Y'));

    // So sánh hai đối tượng
    if ($day_only1 > $day_only2) {
      return true;
    }

    return false;
  }

  /**
   * Compares two timestamps to determine if they represent the same time (ignoring the date part).
   *
   * @param int $timestamp_1 The first timestamp.
   * @param int $timestamp_2 The second timestamp.
   * @return bool Returns true if the time parts of the two timestamps are identical; otherwise, returns false.
   */
  public function is_equal_day($timestamp_1, $timestamp_2)
  {
    // Giả sử bạn có hai timestamp
    $timestamp1 = $timestamp_1;
    $timestamp2 = $timestamp_2;

    // Tạo đối tượng DateTime từ timestamp
    $date1 = new \DateTime();
    $date1->setTimestamp($timestamp1);

    $date2 = new \DateTime();
    $date2->setTimestamp($timestamp2);

    // Tạo các đối tượng DateTime mới chỉ với phần ngày - tháng - năm
// Sử dụng ngày gốc để tránh các vấn đề liên quan đến timezone
    $day_only1 = new \DateTime($date1->format('d-m-Y'));
    $day_only2 = new \DateTime($date2->format('d-m-Y'));

    // So sánh hai đối tượng
    if ($day_only1 == $day_only2) {
      return true;
    }

    return false;
  }

  public function get_time_slot_array()
  {
    return $this->time_slot_array;
  }

  /**
   * Summary of deep_copy_time_slot_array
   * Hàm này dùng để sao chép sâu mảng dữ liệu time_slot_array[]
   * @param mixed $time_slot_array
   * @return time_slot[]
   */
  public function deep_copy_time_slot_array($time_slot_array)
  {
    $clone = [];
    foreach ($time_slot_array as $time_slot) {
      $clone[] = new time_slot(
        $time_slot->room,
        $time_slot->date,
        $time_slot->session,
        $time_slot->course_session_information ? $time_slot->course_session_information->get_copy() : null,
        $time_slot->time_slot_index,
        $time_slot->is_occupied,
        $time_slot->is_occupied_by_course_in_prev_time_slot,
        $time_slot->is_not_allow_change,
        $time_slot->room_number,
        $time_slot->floor,
        $time_slot->building,
        $time_slot->ward,
        $time_slot->district,
        $time_slot->province,

      );
    }
    return $clone;
  }
  /**
   * Summary of deep_copy_course_array
   * Hàm này dùng để copy lại tất cả các phần tử của mảng course array.
   * @param mixed $course_array
   * @return array
   */
  public function deep_copy_course_array($course_array)
  {
    $clone = [];
    foreach ($course_array as $course) {
      $clone[] = clone $course;
    }
    return $clone;
  }

  /**
   * Summary of deep_copy_room_array
   * Hàm này dùng để copy lại tất cả các phần tử của mảng room array.
   * @param mixed $room_array
   * @return array
   */
  public function deep_copy_room_array($room_array)
  {
    $clone = [];
    foreach ($room_array as $room) {
      $clone[] = clone $room;
    }
    return $clone;
  }

  /**
   * Summary of teacher_and_non_teacher_array
   * Hàm này dùng để copy lại tất cả các phần tử của mảng teacher and non teacher array.
   * @param mixed $teacher_and_non_teacher_array
   * @return array
   */
  public function deep_copy_teacher_and_non_teacher_array($teacher_and_non_teacher_array)
  {
    $clone = [];
    foreach ($teacher_and_non_teacher_array as $teacher_and_non_teacher) {
      $clone[] = clone $teacher_and_non_teacher;
    }
    return $clone;
  }


  public function insert_teacher_and_non_teacher_to_time_table()
  {
    // Việc insert teacher mặc định đã được làm trước khi lên lịch
    // người giảng viên cần được gán vào khóa học trước khi lên lịch dạy.
  }

  public function is_put_all_course_into_time_slot($course_array)
  {
    $number_course = count($course_array);
    $count_number_placed_course = 0;

    foreach ($course_array as $course) {
      foreach ($this->time_slot_array as $time_slot) {
        if (
          !empty($time_slot->course_session_information)
          and isset($time_slot->course_session_information->courseid)
          and isset($time_slot->course_session_information->stt_course)
          and isset($time_slot->is_occupied)
          and isset($time_slot->is_occupied_by_course_in_prev_time_slot)
          and $time_slot->course_session_information->courseid == $course->courseid
          and $time_slot->course_session_information->stt_course == $course->stt_course
          and $time_slot->is_occupied == true
          and $time_slot->is_occupied_by_course_in_prev_time_slot == false
        ) {
          $count_number_placed_course++;
        }
      }

    }

    if ($count_number_placed_course == $number_course) {
      return true;
    }

    return false;
  }

  public function get_time_slot_index($stt_room, $stt_class_session, $class_date_timestamp)
  {
    $time_slot_array_length = count($this->time_slot_array);
    for ($i = 0; $i < $time_slot_array_length; $i++) {
      if (
        $this->time_slot_array[$i]->room == $stt_room
        and $this->time_slot_array[$i]->session == $stt_class_session
        and date('D, d-m-Y', $this->time_slot_array[$i]->date) == date('D, d-m-Y', $class_date_timestamp)
      ) {
        return $i;
      }
    }
    return false;
  }

  public function get_index_of_course_session_in_course_array($course)
  {
    $index = 0;
    $course_array_length = count($this->course_array);

    for ($i = 0; $i < $course_array_length; $i++) {
      if (
        $this->course_array[$i]->courseid == $course->courseid
        and $this->course_array[$i]->stt_course == $course->stt_course
      ) {
        $index = $i;
        break;
      }
    }

    return $index;
  }

  public function get_time_gap_to_skip_holiday_and_goto_next_course_session($time_slot, $course)
  {
    $time_gap = 0;
    for ($i = TIME_GAP_BETWEEN_COURSE_SESSION_OF_SAME_COURSE; $i < 60; $i += TIME_GAP_BETWEEN_COURSE_SESSION_OF_SAME_COURSE) {
      if (empty($this->check_holiday($i * 24 * 60 * 60 + $time_slot->date, $course))) {
        $time_gap = $i;
        break;
      }
    }
    return $time_gap;
  }

  public function compute_number_day_between_start_day_and_end_day($start_date_timestamp, $end_date_timestamp)
  {
    $start_date_datetime = (new \DateTime())->setTimestamp($start_date_timestamp);
    $end_date_datetime = (new \DateTime())->setTimestamp($end_date_timestamp);
    $number_day = $start_date_datetime->diff($end_date_datetime)->days;

    return (int) $number_day;

  }

  public function check_time_gap_and_check_fix_class_address_between_course_session_of_course(&$course, $time_slot, $time_slot_array)
  {
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;
    $putted_course_infor = [];

    if (empty($this->putted_course)) {
      return [];
    }

    if (!empty($this->putted_course)) {
      foreach ($this->putted_course as $putted_course) {
        if ($putted_course['courseid'] == $course->courseid) {
          $putted_course_infor = $putted_course;
          break;
        }
      }

      if (empty($putted_course_infor)) {
        return [];
      }
    }


    $prev_course_session_time_slot_array = $putted_course_infor['putted_time_slot_array'];
    $count_prev_course_session = count($prev_course_session_time_slot_array);

    if (empty($prev_course_session_time_slot_array)) {
      return []; // EMPTY ARRAY
    }

    // get monday and sunday of current week
    $monday_timestamp = $time_slot->date;
    $sunday_timestamp = $time_slot->date;
    for ($i = 0; $i < 8; $i++) {
      $date = $time_slot->date - $i * 24 * 60 * 60;
      if (date("D", $date) == "Mon") {
        $monday_timestamp = $date;
        break;
      }
    }

    $sunday_timestamp = $monday_timestamp + 6 * 24 * 60 * 60;
    $count_number_course_session_in_current_week = 0;
    foreach ($prev_course_session_time_slot_array as $prev_time_slot) {
      if ($prev_time_slot->date >= $monday_timestamp and $prev_time_slot->date <= $sunday_timestamp) {
        $count_number_course_session_in_current_week++;
      }
    }

    // kiểm tra tuần hiện tại đã đặt dủ buổi hay chưa nếu chưa đủ buổi thì đặt thêm vào.
    if (
      $count_number_course_session_in_current_week > 0
      and $count_number_course_session_in_current_week < $course->number_course_session_weekly
    ) {
      $index = $count_number_course_session_in_current_week - 1;
      // Nếu đây là course session mà nó đặt vào vị trí ngày đã bị chiếm trước đó rồi thì phải xử lý bằng việc 
      // tìm phòng khác trong cùng ngày và cùng time_gap với buổi liền trước nó
      if ($course->first_put_successfully_in_is_not_allow_change_session_flag) {
        if (
          ($prev_course_session_time_slot_array[$index]->room <= $time_slot->room
            and $prev_course_session_time_slot_array[$index]->session == $time_slot->session
            and $prev_course_session_time_slot_array[$count_prev_course_session - 1]->date < $time_slot->date
            and $this->compute_number_day_between_start_day_and_end_day(
              $prev_course_session_time_slot_array[$count_prev_course_session - 1]->date,
              $time_slot->date
            ) == TIME_GAP_BETWEEN_COURSE_SESSION_OF_SAME_COURSE)
        ) {
          return [];
        }

      }
      // Nếu đây là course session mà đáng lẽ ra nó đã được đặt nhưng thời gian nó đặt là ngay trúng ngày lễ
      // xử lý bằng cách đặt nó vào buổi học tiếp theo nữa (bỏ qua thời gian tại ngày lễ)
      // do đó khóa học sẽ bị kéo dài thời gian nhưng vẫn đảm bảo học đúng ngày thứ, giờ học, địa điểm học 
      else if ($course->first_put_successfully_in_holiday_flag) {
        if (
          ($prev_course_session_time_slot_array[$index]->room <= $time_slot->room
            and $prev_course_session_time_slot_array[$index]->session == $time_slot->session
            and $prev_course_session_time_slot_array[$count_prev_course_session - 1]->date < $time_slot->date
            and $this->compute_number_day_between_start_day_and_end_day(
              $prev_course_session_time_slot_array[$count_prev_course_session - 1]->date,
              $time_slot->date
            ) == $course->time_gap_to_skip_holiday_and_goto_next_course_session)

        ) {
          return [];
        }
      }
      // Nếu đây không là một buổi học mà nó không bị dính hai điều kiện trùng vào thời gian nghỉ lễ và trùng vào một thời gian đã bị chiếm trước
      // Thì cố gắng tìm đúng vị trí phòng, ngày học và h học so với buổi học trước đó đã đặt
      else if (
        ($prev_course_session_time_slot_array[$index]->room <= $time_slot->room
          and $prev_course_session_time_slot_array[$index]->session == $time_slot->session
          and $prev_course_session_time_slot_array[$count_prev_course_session - 1]->date < $time_slot->date
          and $this->compute_number_day_between_start_day_and_end_day(
            $prev_course_session_time_slot_array[$count_prev_course_session - 1]->date,
            $time_slot->date
          ) == TIME_GAP_BETWEEN_COURSE_SESSION_OF_SAME_COURSE)
      ) {
        // kiểm tra điều kiện course session này có đụng độ vào lễ hay tiết bị chiếm trước không 
        // nếu có tiến hành ghi lại các cờ vào trong thông tin của course session
        if ($time_slot->is_not_allow_change) {
          $course->first_put_successfully_in_is_not_allow_change_session_flag = true;
          return [
            'error_type' => 8,
            'error_decription' => 'This time slot does not fix room, session, time gap for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
            'time_slot_conflict_array' => $time_slot_conflict_array,
            'number_time_slot_conflict' => $number_time_slot_conflict
          ];
        } else if (!empty($this->check_holiday($time_slot, $course))) {
          $course->first_put_successfully_in_holiday_flag = true;
          $time_gap_to_skip_holiday_and_goto_next_course_session = 0;
          $time_gap_to_skip_holiday_and_goto_next_course_session = $this->get_time_gap_to_skip_holiday_and_goto_next_course_session($time_slot, $course);
          $course->time_gap_to_skip_holiday_and_goto_next_course_session = $time_gap_to_skip_holiday_and_goto_next_course_session;
          return [
            'error_type' => 8,
            'error_decription' => 'This time slot does not fix room, session, time gap for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
            'time_slot_conflict_array' => $time_slot_conflict_array,
            'number_time_slot_conflict' => $number_time_slot_conflict
          ];
        }
        // nếu không có gì bất thường thì không có lỗi.
        else {
          return [];
        }
      }
    }
    // nếu đây là buổi học bắt đầu của tuần sau khi tuần học trước đã đặt đủ buổi
    else if ($count_number_course_session_in_current_week == 0) {
      if (
        $prev_course_session_time_slot_array[0]->room <= $time_slot->room
        and $prev_course_session_time_slot_array[0]->session == $time_slot->session
        and date("D", $prev_course_session_time_slot_array[0]->date) == date("D", $time_slot->date)
      ) {
        return [];
      }
    }
    // kiểm tra nếu đã đặt đủ buổi trong tuần rồi thì báo lỗi và không cho đặt nữa.
    else if ($count_number_course_session_in_current_week == $course->number_course_session_weekly) {
      return [
        'error_type' => 11,
        'error_decription' => 'This current week has enough number course session for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];
    }

    return [
      'error_type' => 8,
      'error_decription' => 'This time slot does not fix room, session, time gap for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
      'time_slot_conflict_array' => $time_slot_conflict_array,
      'number_time_slot_conflict' => $number_time_slot_conflict
    ];
  }

  public function check_forbidden_session($time_slot, $course)
  {
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;
    $date_string_format = date("D", $time_slot->date);

    if (
      $date_string_format == "Mon"
      or $date_string_format == "Tue"
      or $date_string_format == "Wed"
      or $date_string_format == "Thu"
      or $date_string_format == "Fri"
    ) {
      if ($time_slot->session < START_AFTERNOON) {
        return [
          'error_type' => 7,
          'error_decription' => 'This time slot is forbidden session for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      }
    }
    return [];
  }

  public function check_holiday($time_slot, $course)
  {
    global $DB;
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    $params = [
      'start_date' => $this->earliest_start_date_timestamp,
      'end_date' => $this->latest_end_date_timestamp
    ];
    $holiday_sql = "SELECT * 
                    FROM {local_course_calendar_holiday} h 
                    WHERE h.holiday >= :start_date and h.holiday <= :end_date";
    $holiday_in_system_config = $DB->get_records_sql($holiday_sql, $params);

    foreach ($holiday_in_system_config as $holiday) {
      if (date("d-m-Y", $holiday->holiday) == date("d-m-Y", $time_slot->date)) {
        return [
          'error_type' => 6,
          'error_decription' => 'This time slot is holiday for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      }
    }

    return [];
  }

  public function check_class_session_during_over_max_teaching_time($course)
  {
    $course_session_length = $course->class_duration;
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;


    if ($course_session_length > MAX_CLASS_DURATION) {
      return [
        'error_type' => 5,
        'error_decription' => 'This course is over max teaching time in system config for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];

    }
    return [];
  }

  public function check_class_session_during_in_one_session_of_day($course, $time_slot, $time_slot_array)
  {
    $course_session_length = $course->class_duration;
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    if ($time_slot->session < END_EVENING and $time_slot->session + $course_session_length >= END_EVENING) {
      return [
        'error_type' => 2,
        'error_decription' => 'This time slot have not enough time slot range in one evening session of day for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];

    } else if ($time_slot->session < END_AFTERNOON and $time_slot->session + $course_session_length >= END_AFTERNOON) {
      return [
        'error_type' => 3,
        'error_decription' => 'This time slot have not enough time slot range in one afternoon session of day for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];

    } else if ($time_slot->session < END_MORNING and $time_slot->session + $course_session_length >= END_MORNING) {
      return [
        'error_type' => 4,
        'error_decription' => 'This time slot have not enough time slot range in one morning session of day for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];

    }

    return [];
  }

  public function check_duplicate_course_at_same_time($course, $time_slot, $time_slot_array)
  {
    $course_session_length = $course->class_duration;
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;
    $count_time_slot_array = count($time_slot_array);

    for ($i = $time_slot->time_slot_index; $i < $time_slot->time_slot_index + $course_session_length; $i++) {
      if ($i >= $count_time_slot_array) {
        break;
      }

      if (
        $time_slot_array[$i]->is_occupied
        or $time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot
        or $time_slot_array[$i]->is_not_allow_change
        or
        (!empty($time_slot_array[$i]->course_session_information)
          and isset($time_slot_array[$i]->course_session_information->courseid))
      ) {
        $time_slot_conflict_array[] = $time_slot_array[$i]->get_copy();
        $number_time_slot_conflict++;
      }
    }


    if ($number_time_slot_conflict > 0) {
      $start_time_slot_of_time_slot_conflict_array = $time_slot_conflict_array[0];
      $end_time_slot_of_time_slot_conflict_array = $time_slot_conflict_array[$number_time_slot_conflict - 1];

      if (
        ($start_time_slot_of_time_slot_conflict_array->is_occupied
          and $start_time_slot_of_time_slot_conflict_array->is_occupied_by_course_in_prev_time_slot)
        or $start_time_slot_of_time_slot_conflict_array->is_not_allow_change
      ) {
        for ($i = $start_time_slot_of_time_slot_conflict_array->time_slot_index - 1; $i >= 0; $i--) {
          if (
            !empty($time_slot_array[$i]->course_session_information)
            and isset($time_slot_array[$i]->course_session_information->courseid)
            and $time_slot_array[$i]->is_occupied
            and $time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot == false
          ) {
            array_unshift($time_slot_conflict_array, $time_slot_array[$i]->get_copy());
            $number_time_slot_conflict++;
            break;
          } else if (
            $time_slot_array[$i]->is_occupied
            and $time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot
          ) {
            array_unshift($time_slot_conflict_array, $time_slot_array[$i]->get_copy());
            $number_time_slot_conflict++;
          } else if ($time_slot_array[$i]->is_not_allow_change) {
            array_unshift($time_slot_conflict_array, $time_slot_array[$i]->get_copy());
            $number_time_slot_conflict++;
          } else if (
            empty($time_slot_array[$i]->course_session_information)
            and $time_slot_array[$i]->is_occupied == false
            and $time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot == false
          ) {
            break;
          }
        }
      }

      if (
        ($end_time_slot_of_time_slot_conflict_array->is_occupied
          and $end_time_slot_of_time_slot_conflict_array->is_occupied_by_course_in_prev_time_slot)
        or !empty($end_time_slot_of_time_slot_conflict_array->course_session_information)
        or $end_time_slot_of_time_slot_conflict_array->is_not_allow_change
      ) {
        $time_slot_array_length = count($time_slot_array);
        for ($i = $end_time_slot_of_time_slot_conflict_array->time_slot_index + 1; $i < $time_slot_array_length; $i++) {
          if (
            !empty($time_slot_array[$i]->course_session_information)
            or (empty($time_slot_array[$i]->course_session_information)
              and $time_slot_array[$i]->is_occupied == false
              and $time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot == false)
          ) {
            break;
          } else {
            $time_slot_conflict_array[] = $time_slot_array[$i]->get_copy();
            $number_time_slot_conflict++;
          }
        }
      }

      return [
        'error_type' => 1,
        'error_decription' => 'This time slot have already course before when put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];
    }

    return [];
  }

  public function check_enough_time_slot_range_to_put_course($course, $time_slot, $time_slot_array)
  {
    $course_session_length = $course->class_duration;
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    if ($time_slot->session + $course_session_length > $this->number_class_sessions - 1) {
      return [
        'error_type' => 0,
        'error_decription' => 'Not enough time slot range for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];
    }

    for ($i = $time_slot->time_slot_index; $i < $time_slot->time_slot_index + $course_session_length; $i++) {
      if ($this->is_time_slot_not_allow_change($this->time_slot_array[$i])) {
        return [
          'error_type' => 0,
          'error_decription' => 'Not enough time slot range for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      }
    }

    if (
      empty($time_slot->course_session_information)
      and $time_slot->is_occupied == false
      and $time_slot->is_occupied_by_course_in_prev_time_slot == false
      and $time_slot->is_not_allow_change == false
    ) {
      $count_time_slot_array = count($time_slot_array);
      for ($i = $time_slot->time_slot_index + 1; $i < $time_slot->time_slot_index + $course_session_length; $i++) {
        if ($i >= $count_time_slot_array) {
          break;
        }

        if (
          (!empty($time_slot_array[$i]->course_session_information)
            and isset($time_slot_array[$i]->course_session_information->courseid)
            and $time_slot_array[$i]->is_occupied
            and $time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot == false)
          or $time_slot_array[$i]->is_not_allow_change
        ) {
          return $this->check_duplicate_course_at_same_time($course, $time_slot, $time_slot_array);
        }
      }

    } else if (
      (!empty($time_slot->course_session_information)
        and isset($time_slot->course_session_information->courseid)
        and $time_slot->is_occupied
        and $time_slot->is_occupied_by_course_in_prev_time_slot == false)
      or $time_slot->is_not_allow_change
    ) {
      return $this->check_duplicate_course_at_same_time($course, $time_slot, $time_slot_array);
    }

    return [];
  }

  public function check_course_session_of_course_in_range_start_time_to_end_time($course, $time_slot, $time_slot_array)
  {
    $start_date_of_course = $course->startdate;
    $end_date_of_course = $course->enddate;
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    if (
      $time_slot->date >= $start_date_of_course
    ) {
      return [];

    }

    return [
      'error_type' => 9,
      'error_decription' => 'This time slot have not in range start date to end date of course for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
      'time_slot_conflict_array' => $time_slot_conflict_array,
      'number_time_slot_conflict' => $number_time_slot_conflict
    ];
  }

  public function check_only_one_course_session_of_same_course_study_on_day($course, $time_slot, $time_slot_array)
  {
    $putted_course_array = $this->putted_course;
    $putted_course_time_slot_infor = [];
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    foreach ($putted_course_array as $putted_course) {
      if ($putted_course['courseid'] == $course->courseid) {
        $putted_course_time_slot_infor = $putted_course['putted_time_slot_array'];
        break;
      }
    }

    if (empty($putted_course_time_slot_infor)) {
      return [];
    }

    $count_putted_course_time_slot = count($putted_course_time_slot_infor);
    for ($i = 0; $i < $count_putted_course_time_slot; $i++) {
      if (date("D, d-m-Y", $putted_course_time_slot_infor[$i]->date) == date("D, d-m-Y", $time_slot->date)) {
        return [
          'error_type' => 10,
          'error_decription' => 'duplicate course session of same course on one day for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      }
    }

    return [];
  }

  public function get_class_room_teaching_by_teacher($teacherid, $date_timestamp)
  {
    $class_room_teaching_by_teacher_array = [];
    $time_slot_array_length = count($this->time_slot_array);

    for ($i = 0; $i < $time_slot_array_length; $i++) {
      if (
        $this->time_slot_array[$i]->date > $date_timestamp
        and date("D, d-m-Y", $this->time_slot_array[$i]->date) != date("D, d-m-Y", $date_timestamp)
      ) {
        break;
      }

      if (date("D, d-m-Y", $this->time_slot_array[$i]->date) == date("D, d-m-Y", $date_timestamp)) {
        if (!empty($this->time_slot_array[$i]->course_session_information)) {
          $current_class_room_teacher_array = $this->time_slot_array[$i]->course_session_information->editting_teacher_array;
          foreach ($current_class_room_teacher_array as $key => $teacher) {
            if ($teacher->id == $teacherid) {
              $class_room_teaching_by_teacher_array[] = $this->time_slot_array[$i]->course_session_information;
            }
          }
        }
      }
    }

    return $class_room_teaching_by_teacher_array;
  }

  public function check_duplicate_teacher_is_teaching_for_two_difference_class_in_same_time($editting_teacher_array, $course, $time_slot)
  {
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    $total_class_room_teaching_by_teacher_array = [];
    foreach ($editting_teacher_array as $key => $teacher) {
      $total_class_room_teaching_by_teacher_array += $this->get_class_room_teaching_by_teacher($teacher->id, $time_slot->date);
    }

    foreach ($total_class_room_teaching_by_teacher_array as $key => $class_room) {
      if (
        $class_room->course_session_start_time <= $time_slot->session
        and $class_room->course_session_end_time >= $time_slot->session + $course->class_duration
      ) {
        return [
          'error_type' => 12,
          'error_decription' => 'duplicate teacher is teaching for two difference class in same time for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      } else if (
        $class_room->course_session_start_time <= $time_slot->session
        and $class_room->course_session_end_time > $time_slot->session
      ) {
        return [
          'error_type' => 12,
          'error_decription' => 'duplicate teacher is teaching for two difference class in same time for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      } else if (
        $class_room->course_session_start_time <= $time_slot->session + $course->class_duration
        and $class_room->course_session_end_time > $time_slot->session + $course->class_duration
      ) {
        return [
          'error_type' => 12,
          'error_decription' => 'duplicate teacher is teaching for two difference class in same time for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
          'time_slot_conflict_array' => $time_slot_conflict_array,
          'number_time_slot_conflict' => $number_time_slot_conflict
        ];
      }
    }

    return [];
  }

  public function check_time_gap_to_goto_class_between_two_difference_physical_address($course, $time_slot, $editting_teacher_array)
  {
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    $total_class_room_teaching_by_teacher_array = [];
    foreach ($editting_teacher_array as $key => $teacher) {
      $total_class_room_teaching_by_teacher_array += $this->get_class_room_teaching_by_teacher($teacher->id, $time_slot->date);
    }

    foreach ($total_class_room_teaching_by_teacher_array as $key => $class_room) {
      if ($time_slot->room != $class_room->room) {
        if (
          $time_slot->ward != $class_room->ward
          or $time_slot->district != $class_room->district
          or $time_slot->province != $class_room->province
        ) {
          if ($class_room->course_session_end_time + TIME_GAP_FOR_GOTO_CLASS_BETWEEN_TWO_PHYSICAL_ADDRESS > $time_slot->session) {
            return [
              'error_type' => 13,
              'error_decription' => 'Not enough time gap for go to between 2 physical address for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
              'time_slot_conflict_array' => $time_slot_conflict_array,
              'number_time_slot_conflict' => $number_time_slot_conflict
            ];

          }
        }
      }
    }

    return [];
  }

  public function check_session_is_not_allow_change($time_slot, $course)
  {
    $time_slot_conflict_array = [];
    $number_time_slot_conflict = 0;

    if ($time_slot->is_not_allow_change) {
      return [
        'error_type' => 14,
        'error_decription' => 'This time slot is not allow change for put one course section with information course id: ' . $course->courseid . ' - ' . 'course name: ' . $course->shortname,
        'time_slot_conflict_array' => $time_slot_conflict_array,
        'number_time_slot_conflict' => $number_time_slot_conflict
      ];
    }

    return [];
  }

  public function pseudo_put_course_to_time_slot($course, $time_slot, $time_slot_array)
  {
    $errors_array_in_this_time_slot = [];

    // kiểm tra từng điều kiện tại time-slot và với $time_slot_array
    // Chỉ cần kiểm tra được có một lỗi nào đó mà time_slot tại đó phải bỏ qua thì return luôn 
    // không cần kiểm tra các lỗi phía sau nữa
    $errors_array_in_this_time_slot[] = $this->check_holiday($time_slot, $course);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_forbidden_session($time_slot, $course);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_session_is_not_allow_change($time_slot, $course);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_course_session_of_course_in_range_start_time_to_end_time($course, $time_slot, $time_slot_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_enough_time_slot_range_to_put_course($course, $time_slot, $time_slot_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_class_session_during_in_one_session_of_day($course, $time_slot, $time_slot_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_time_gap_and_check_fix_class_address_between_course_session_of_course($course, $time_slot, $time_slot_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_only_one_course_session_of_same_course_study_on_day($course, $time_slot, $time_slot_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_duplicate_teacher_is_teaching_for_two_difference_class_in_same_time($course->editting_teacher_array, $course, $time_slot);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_time_gap_to_goto_class_between_two_difference_physical_address($course, $time_slot, $course->editting_teacher_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_duplicate_course_at_same_time($course, $time_slot, $time_slot_array);
    $count = count($errors_array_in_this_time_slot);
    if (!empty($errors_array_in_this_time_slot[$count - 1])) {
      return $errors_array_in_this_time_slot;
    }

    $errors_array_in_this_time_slot[] = $this->check_class_session_during_over_max_teaching_time($course);
    return $errors_array_in_this_time_slot;
  }

  public function check_position($time_slot, $course)
  {
    $errors_array_in_this_time_slot = $this->pseudo_put_course_to_time_slot($course, $time_slot, $this->time_slot_array);

    if (empty($errors_array_in_this_time_slot)) {
      return true;
    }

    $count_errors_array_in_this_time_slot = count($errors_array_in_this_time_slot);
    $count_empty = 0;
    foreach ($errors_array_in_this_time_slot as $error) {
      if (empty($error)) {
        $count_empty++;
      }
    }

    if ($count_empty == $count_errors_array_in_this_time_slot) {
      return true;
    }

    return false;
  }

  public function is_time_slot_not_allow_change($time_slot)
  {
    if ($time_slot->is_not_allow_change) {
      return true;
    }

    return false;
  }

  public function deep_copy_putted_course_information_array($putted_course)
  {
    if (empty($putted_course)) {
      return [];
    }

    $result_copy_putted_course = [];
    foreach ($putted_course as $putted_course_infor) {
      $courseid = $putted_course_infor['courseid'];
      $putted_time_slot_array = [];
      foreach ($putted_course_infor['putted_time_slot_array'] as $time_slot) {
        $putted_time_slot_array[] = $time_slot->get_copy();
      }
      $result_copy_putted_course[] = ['courseid' => $courseid, 'putted_time_slot_array' => $putted_time_slot_array];
    }
    return $result_copy_putted_course;
  }
  public function add_new_putted_course_to_putted_course_array($course, $time_slot)
  {
    if (empty($this->putted_course)) {
      $this->putted_course[] = ['courseid' => $course->courseid, 'putted_time_slot_array' => [$time_slot]];
      return;
    } else {
      $count_element = count($this->putted_course);
      for ($i = 0; $i < $count_element; $i++) {
        if ($this->putted_course[$i]['courseid'] == $course->courseid) {
          $this->putted_course[$i]['putted_time_slot_array'][] = $time_slot;
          return;
        }
      }

      $this->putted_course[] = ['courseid' => $course->courseid, 'putted_time_slot_array' => [$time_slot]];
    }
  }

  public function delete_putted_course_from_putted_course_array($course, $time_slot)
  {
    if (empty($this->putted_course)) {
      return;
    }

    $count_element = count($this->putted_course);
    for ($i = 0; $i < $count_element; $i++) {
      if ($this->putted_course[$i]['courseid'] == $course->courseid) {
        foreach ($this->putted_course[$i]['putted_time_slot_array'] as $key => $putted_time_slot) {
          if ($putted_time_slot->time_slot_index == $time_slot->time_slot_index) {
            unset($this->putted_course[$i]['putted_time_slot_array'][$key]);
            $this->putted_course[$i]['putted_time_slot_array'] = array_values($this->putted_course[$i]['putted_time_slot_array']);
            return;
          }
        }
      }
    }

  }

  public function put_course_to_time_slot($course, $time_slot)
  {
    foreach ($this->time_slot_array as $current_time_slot) {
      if ($current_time_slot->time_slot_index == $time_slot->time_slot_index) {

        $this->time_slot_array[$time_slot->time_slot_index]->course_session_information = new course_session_information(
          $course->courseid,
          $course->shortname,
          $course->class_duration,
          $time_slot->session,
          $course->class_duration + $time_slot->session,
          $course->editting_teacher_array,
          null,
          $time_slot->date,
          $time_slot->room,
          $time_slot->room,
          $time_slot->floor,
          $time_slot->building,
          $time_slot->ward,
          $time_slot->district,
          $time_slot->province,
          $course->total_course_section,
          $course->number_course_session_weekly,
          $course->stt_course,
          $time_slot->room_number,
          $course->first_put_successfully_in_holiday_flag,
          $course->first_put_successfully_in_is_not_allow_change_session_flag,
          $course->time_gap_to_skip_holiday_and_goto_next_course_session,

        );
        $this->time_slot_array[$time_slot->time_slot_index]->is_occupied = true;
        $this->time_slot_array[$time_slot->time_slot_index]->is_occupied_by_course_in_prev_time_slot = false;

        $this->add_new_putted_course_to_putted_course_array($course, $this->time_slot_array[$time_slot->time_slot_index]);

        // create log for debug 
        $value_string = "Put course with courseid: " . $course->courseid . " with stt_course: " . $course->stt_course . " and course name " . $course->shortname . " class duration = " . $course->class_duration . " to time_slot: " . $time_slot->time_slot_index . "\n";
        if ($this->file_handle) {
          fwrite($this->file_handle, $value_string);
        }

        // Thực hiện việc đánh dấu các ô liên tiếp trong time_slot_array là đã bị chiếm dụng nếu có một course nào đó có độ dài > 1
        if ($course->class_duration > 1) {
          $count_time_slot_array = count($this->time_slot_array);
          for ($i = $time_slot->time_slot_index + 1; $i < $time_slot->time_slot_index + $course->class_duration; $i++) {
            if ($i >= $count_time_slot_array) {
              break;
            }

            $this->time_slot_array[$i]->is_occupied = true;
            $this->time_slot_array[$i]->is_occupied_by_course_in_prev_time_slot = true;

            // create log for debug 
            $value_string = "Put course to time_slot: " . $this->time_slot_array[$i]->time_slot_index . "\n";
            if ($this->file_handle) {
              fwrite($this->file_handle, $value_string);
            }

          }

        }

        break;
      }
    }
  }

  public function unlocate_course_from_time_slot($course_param, $time_slot_param, $time_slot_contain_errors)
  {
    $unlocate_course_array = [];
    $errors = $time_slot_contain_errors['errors'];
    foreach ($errors as $error) {
      if (empty($error)) {
        continue;
      }

      $time_slot_conflict_array = $error['time_slot_conflict_array'];
      foreach ($time_slot_conflict_array as $current_time_slot) {
        foreach ($this->time_slot_array as $time_slot) {
          if ($time_slot->time_slot_index == $current_time_slot->time_slot_index) {

            if (!empty($time_slot->course_session_information)) {
              $courseid = $time_slot->course_session_information->courseid;
              $stt_course = $time_slot->course_session_information->stt_course;
              foreach ($this->course_array as $course) {
                if ($course->courseid == $courseid and $course->stt_course == $stt_course) {
                  $unlocate_course_array[] = clone $course;

                  $this->delete_putted_course_from_putted_course_array($course, $time_slot);

                  break;
                }
              }

              // create log for debug 
              $value_string = "Unllocate course with courseid: " . $courseid . " with stt_course: " . $stt_course . " and course name " . $time_slot->course_session_information->course_name . " class duration = " . $time_slot->course_session_information->course_session_length . " from time_slot: " . $time_slot->time_slot_index . "\n";
              if ($this->file_handle) {
                fwrite($this->file_handle, $value_string);
              }

            }

            if (empty($time_slot->course_session_information)) {
              // create log for debug 
              $value_string = "Unllocate course from time_slot: " . $time_slot->time_slot_index . "\n";
              if ($this->file_handle) {
                fwrite($this->file_handle, $value_string);
              }
            }

            $this->time_slot_array[$time_slot->time_slot_index]->course_session_information = null;
            $this->time_slot_array[$time_slot->time_slot_index]->is_occupied = false;
            $this->time_slot_array[$time_slot->time_slot_index]->is_occupied_by_course_in_prev_time_slot = false;

          }
        }
      }
    }
    return $unlocate_course_array;
  }

  public function is_time_slot_need_to_skip($time_slot_contain_errors)
  {
    if ($this->is_time_slot_not_allow_change($this->time_slot_array[$time_slot_contain_errors['original_time_slot_index']])) {
      return true;
    }

    $errors = $time_slot_contain_errors['errors'];
    foreach ($errors as $error) {
      if (empty($error)) {
        continue;
      }

      switch ($error['error_type']) {
        case 0:
        case 1:
        case 2:
        case 3:
        case 4:
        case 6:
        case 7:
        case 8:
        case 9:
        case 10:
        case 11:
        case 12:
        case 13:
        case 14:
          return true;
      }
    }

    return false;
  }

  //Định nghĩa hàm so sánh tùy chỉnh với hai tiêu chí
  public static function multi_criteria_sort($a, $b)
  {
    // Tiêu chí 1 (chính): So sánh số lượng lỗi
    $error_count_a = count($a['errors']);
    $error_count_b = count($b['errors']);

    $primary_comparison = $error_count_a <=> $error_count_b;

    // Nếu số lượng lỗi khác nhau, trả về kết quả ngay
    if ($primary_comparison !== 0) {
      return $primary_comparison;
    }

    // Tiêu chí 2 (phụ): Nếu số lượng lỗi bằng nhau, so sánh tổng số phần tử bị lỗi
    $total_conflict_a = 0;
    foreach ($a['errors'] as $error) {
      if (empty($error)) {
        continue;
      }
      $total_conflict_a += $error['number_time_slot_conflict'];
    }

    $total_conflict_b = 0;
    foreach ($b['errors'] as $error) {
      if (empty($error)) {
        continue;
      }
      $total_conflict_b += $error['number_time_slot_conflict'];
    }

    return $total_conflict_a <=> $total_conflict_b;
  }

  public function swap_course($course, $level_recursive, $number_of_call_recursive, $do_recursive)
  {
    $best_time_slot_array = [];
    foreach ($this->time_slot_array as $time_slot) {
      $slot_info = [
        'original_time_slot_index' => $time_slot->time_slot_index,
        'errors' => $this->pseudo_put_course_to_time_slot($course, $time_slot, $this->time_slot_array),
      ];
      $best_time_slot_array[] = $slot_info;
    }

    // Sử dụng usort() để sắp xếp mảng cái mảng này là toàn bộ mảng.
    usort($best_time_slot_array, [$this, 'multi_criteria_sort']);

    $put_success = false;
    foreach ($best_time_slot_array as $time_slot_contain_errors) {
      // cần tiến hành xử lý và bỏ qua các vị trí cho lỗi tiết cấm, ngày cấm, không đủ tiết để đặt, ... ở đây
      // dựa vào error_type để kiểm tra.
      if ($this->is_time_slot_need_to_skip($time_slot_contain_errors)) {
        continue;
      }

      $old_time_slot_array = $this->deep_copy_time_slot_array($this->time_slot_array);
      $old_putted_course = $this->deep_copy_putted_course_information_array($this->putted_course);
      $unlocate_course_array = [];
      $time_slot = $this->time_slot_array[$time_slot_contain_errors['original_time_slot_index']];

      // hàm unlocate_course_from_time_slot này sẽ truy cập đến vị trí time slot theo time slot index và tiến hành gỡ bỏ các lỗi 
      // các lỗi đã được lưu trong time slot contain errors.
      // clone here
      $unlocate_course_array = $this->unlocate_course_from_time_slot($course, $time_slot, $time_slot_contain_errors);

      $this->put_course_to_time_slot($course, $time_slot);

      if (!$do_recursive) {
        return true;
      }

      $put_success = $this->recursive_swap_algorithm(
        $unlocate_course_array,
        $level_recursive + 1,
        $number_of_call_recursive + 1
      );

      if ($put_success) {
        return true;
      } else {
        $this->time_slot = $this->deep_copy_time_slot_array($old_time_slot_array);
        $this->putted_course = $this->deep_copy_putted_course_information_array($old_putted_course);
      }
    }

    if (!$put_success) {
      return false;
    }
  }
  public function recursive_swap_algorithm($course_array, $level_recursive, $number_of_call_recursive)
  {
    if ($level_recursive > $this->max_level_recursive and $number_of_call_recursive > $this->max_number_of_call_recursive) {
      return false;
    }

    foreach ($course_array as $course) {
      $available_time_slot_array = [];
      foreach ($this->time_slot_array as $time_slot) {
        if ($this->check_position($time_slot, $course)) {
          $available_time_slot_array[] = $time_slot->get_copy();
          break;
        }
      }

      if (!empty($available_time_slot_array)) {
        $index = rand(0, count($available_time_slot_array) - 1);
        $this->put_course_to_time_slot($course, $available_time_slot_array[$index]);
      } else {
        $success_flag = $this->swap_course(
          $course,
          $level_recursive,
          $number_of_call_recursive,
          true
        );

        if (!$success_flag) {
          $this->swap_course(
            $course,
            $level_recursive,
            $number_of_call_recursive,
            false
          );
        }
      }
    }

    if ($this->is_put_all_course_into_time_slot($course_array)) {
      return true;
    }

    return false;
  }

  /**
   * Summary of generate_time_table.
   * Hàm này thực hiện việc khởi tạo time table và trực tiếp gọi hàm đệ quy để tạo timetable.
   * @return void 
   */
  public function generate_time_table()
  {
    // Bước 1: Sort the activities, most difficult first. 
    // Not critical step, but speeds up the algorithm maybe 10 times or more.

    array_multisort(
      array_column($this->course_array, 'total_course_section'),
      SORT_DESC,
      SORT_REGULAR,
      array_column($this->course_array, 'class_duration'),
      SORT_DESC,
      SORT_REGULAR,
      array_column($this->course_array, 'number_course_session_weekly'),
      SORT_DESC,
      SORT_REGULAR,
      array_column($this->course_array, 'number_student_on_course'),
      SORT_DESC,
      SORT_REGULAR,
      $this->course_array
    );

    // Bước 2: Try to place each activity (A_i) in an allowed time slot, following the above order, one at a time.
    // Search for an available slot (T_j) for A_i, in which this activity can be placed respecting the constraints.
    // If more slots are available, choose a random one. If none is available, do recursive swapping:
    $suscess_flag = $this->recursive_swap_algorithm(
      $this->course_array,
      0,
      0
    );

    if ($this->file_handle) {
      fwrite($this->file_handle, "Generate time table success: " . $suscess_flag . "\n");
    }

  }

  public function get_teachers_in_course($course)
  {
    global $DB;
    // get teacher and non teacher information from database and save to teacher_and_non_teacher_array.
    $sql = "SELECT DISTINCT (user.id) id, user.firstname, user.lastname, user.email, role.shortname
            from {user} user
            join {role_assignments} ra on ra.userid = user.id
            join {role} role on role.id = ra.roleid
            join {context} context on context.id = ra.contextid
            join {course} course on course.id = context.instanceid
            where course.id != 1
              and course.id = :courseid
              and (role.shortname = 'teacher' or role.shortname = 'editingteacher')
              and context.contextlevel = 50";
    $params = ['courseid' => $course->courseid];
    $teacher_and_non_teacher_array = $DB->get_records_sql($sql, $params);

    if (empty($teacher_and_non_teacher_array)) {
      return [];
    }

    // xóa admin khỏi danh sách teacher của course
    $admins = get_admins();
    foreach ($admins as $admin) {
      foreach ($teacher_and_non_teacher_array as $key => $teacher) {
        if ($teacher->id == $admin->id) {
          unset($teacher_and_non_teacher_array[$key]);
        }
      }
    }

    $teacher_and_non_teacher_array = array_values($teacher_and_non_teacher_array);

    if (empty($teacher_and_non_teacher_array)) {
      return [];
    }

    return $teacher_and_non_teacher_array;
  }

  public function get_courses_not_schedule()
  {
    // Lấy ra các course mà chưa được tạo lịch học 
    global $DB;
    $courses_not_schedule = [];
    $courses_not_schedule_sql = "SELECT distinct c.id courseid, 
                                        c.category, 
                                        c.shortname, 
                                        c.startdate, 
                                        c.enddate, 
                                        c.visible, 
                                        cc.class_duration, 
                                        cc.number_course_session_weekly, 
                                        cc.number_student_on_course, 
                                        tcl.total_course_section
                                FROM {local_course_calendar_course_section} cs
                                RIGHT JOIN {course} c on cs.courseid = c.id
                                left join {local_course_calendar_course_config_for_calendar} cc on cc.courseid = c.id
                                left join {local_course_calendar_total_course_lesson} tcl on tcl.courseid = c.id
                                WHERE cs.courseid is null 
                                      and c.id != 1 
                                      and c.visible = 1 
                                      and c.enddate >= UNIX_TIMESTAMP(NOW())";
    $params = [];
    $courses_not_schedule = $DB->get_records_sql($courses_not_schedule_sql, $params);

    // Tạo dữ liệu course_list bao gồm những course ta sẽ cần xếp thời khóa biểu và cần sắp xếp chúng theo thứ tự ưu tiên
    // Thứ tự ưu tiên là thời gian diễn ra buổi học (số tiết học trên một buổi)
    // Số buổi học trên một tuần
    // Phải tạo thêm course_list để lưu là vì khi chúng ta thực hiện thêm course vào trong thời khóa biểu sẽ cần xóa course đó ra 
    // và khi cần unlocated course thì cần nơi để lưu trở lại.
    // và ta cũng cần sort lại mảng course này để sắp xếp lại theo thứ tự ưu tiên
    // nếu ta làm trên mảng chính luôn có thể sẽ gây sai sót dữ liệu.

    // và cũng vì cái $course_not_schedule đang được đánh index theo cột đầu tiên là courseid ta không biết chắc các id này có theo thứ tự không
    // nó sẽ khó cho quá trình duyệt qua mảng sẽ gây thiếu sót, khó khăn khi chỉ có thể dùng mỗi foreach.

    $course_array = [];
    $index = 0;
    foreach ($courses_not_schedule as $course) {
      // xử lý danh sách course này để thêm vào các buổi học của cùng một course .
      // Sao cho danh sách course này sẽ gồm các course * tổng số buổi học của course đó
      $teacher = $this->get_teachers_in_course($course);
      $course->stt_course = $index;
      $course->editting_teacher_array = $teacher;
      $course->first_put_successfully_in_holiday_flag = false;
      $course->first_put_successfully_in_is_not_allow_change_session_flag = false;
      $course->time_gap_to_skip_holiday_and_goto_next_course_session = 0;
      $course_array[] = clone $course;

      if (empty($course->class_duration)) {
        // CHECK HERE
        $course->class_duration = CLASS_DURATION_OF_COURSE_SESSION_OF_COURSE;
      }

      if (empty($course->number_course_session_weekly)) {
        // CHECK HERE
        $course->number_course_session_weekly = NUMBER_COURSE_SESSION_WEEKLY;
      }

      if (empty($course->number_student_on_course)) {
        // CHECK HERE
        $course->number_student_on_course = NUMBER_STUDENT_ON_COURSE;
      }

      if (empty($course->total_course_section)) {
        // CHECK HERE
        $course->total_course_section = TOTAL_COURSE_SESSION_OF_COURSE;
      }

      if ($course->total_course_section > 1) {
        for ($j = 0; $j < $course->total_course_section - 1; $j++) {
          $index++;
          $course->stt_course = $index;
          $course->editting_teacher_array = $teacher;
          $course->first_put_successfully_in_holiday_flag = false;
          $course->first_put_successfully_in_is_not_allow_change_session_flag = false;
          $course->time_gap_to_skip_holiday_and_goto_next_course_session = 0;
          $course_array[] = clone $course;
        }
      }

      $index++;
    }

    return $course_array;
  }

  public function get_available_rooms()
  {
    global $DB;
    // Lấy ra các phòng học sẵn có của trung tâm
    $available_rooms = $DB->get_records('local_course_calendar_course_room');

    // Lưu lại các room vào mảng có index theo thứ tự từ 0->n
    $room_array = [];
    foreach ($available_rooms as $room) {
      $room_array[] = clone $room;
    }

    return $room_array;
  }

  public function get_teacher_and_non_teacher_array()
  {
    global $DB;
    // get teacher and non teacher information from database and save to teacher_and_non_teacher_array.
    $sql = "SELECT DISTINCT (user.id) id, user.firstname, user.lastname, user.email, role.shortname
            from {user} user
            join {role_assignments} ra on ra.userid = user.id
            join {role} role on role.id = ra.roleid
            join {context} context on context.id = ra.contextid
            join {course} course on course.id = context.instanceid
            where course.id != 1
                    and (role.shortname = 'teacher' or role.shortname = 'editingteacher')
                    and context.contextlevel = 50";
    $params = [];
    $teacher_and_non_teacher_array = $DB->get_records_sql($sql, $params);

    return $teacher_and_non_teacher_array;
  }

  public function get_number_day()
  {
    return (int) $this->number_day;
  }

  public function print_time_table()
  {
    global $DB;
    $number_room = $this->number_room;
    $number_class_sessions = $this->number_class_sessions;
    $number_day = $this->number_day;
    $time_slot_array = $this->time_slot_array;

    // --- In bảng thời khóa biểu ---
    echo "<!DOCTYPE html>";
    echo "<html lang='vi'>";
    echo "<head>";
    echo "    <meta charset='UTF-8'>";
    echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "    <title>Bảng Thời Khóa Biểu</title>";
    echo "    <style>";
    echo "        table {";
    echo "            width: 100%;";
    echo "            border-collapse: collapse;";
    echo "            margin-bottom: 20px;";
    echo "        }";
    echo "        th, td {";
    echo "            border: 1px solid #ccc;";
    echo "            padding: 8px;";
    echo "            text-align: center;";
    echo "            vertical-align: top;"; // Căn trên cho nội dung dài
    echo "        }";
    echo "        th {";
    echo "            background-color: #f2f2f2;";
    echo "        }";
    echo "        .room-header {";
    echo "            background-color: #e0e0e0;";
    echo "            font-weight: bold;";
    echo "        }";
    echo "        .day-header {";
    echo "            background-color: #f9f9f9;";
    echo "            font-weight: bold;";
    echo "        }";
    echo "        .forbidden-session {";
    echo "            background-color: #FF0000";
    echo "            font-weight: bold;";
    echo "        }";

    echo "    </style>";
    echo "</head>";
    echo "<body>";

    echo "<h2>Thời Khóa Biểu</h2>";

    echo "<table>";
    // Hàng tiêu đề cho các ngày
    echo "<thead>";
    echo "<tr>";
    echo "<th>Phòng / Ngày</th>"; // Góc trên bên trái
    for ($j = 0; $j < $number_room; $j++) {
      echo "<th class='room-header'>Phòng " . ($j) . "</th>"; // Cột đầu tiên là tên phòng

    }
    echo "</tr>";
    echo "</thead>";

    // Nội dung bảng
    echo "<tbody>";
    $time_slot_index = 0;
    for ($i = 0; $i < $number_day; $i++) {
      echo "<tr>";
      echo "<td class='day-header'>" . date("D, d-m-Y", $i * 24 * 60 * 60 + $this->earliest_start_date_timestamp) . "</td>";
      for ($j = 0; $j < $number_room; $j++) {
        echo "<td>";
        // Duyệt qua các buổi học trong ngày và phòng hiện tại
        $tiet = 1;
        for ($k = 0; $k < $number_class_sessions; $k++) {
          // Hiển thị nội dung buổi học.
          // Bạn có thể format lại ở đây để hiển thị thông tin chi tiết hơn.
          if (!empty($time_slot_array[$time_slot_index]->course_session_information)) {
            $editting_teacher_array = $time_slot_array[$time_slot_index]->course_session_information->editting_teacher_array;
            $editting_teacher_name_array = [];
            foreach ($editting_teacher_array as $teacher) {
              $editting_teacher_name_array[] = $teacher->firstname . " " . $teacher->lastname;
            }

            echo "<div>" . "Tiết " . $tiet . "</div>";
            // echo "<div>" ."courseid: " . $session_data->courseid . "</div>";
            echo "<div>"
              . "Course id: "
              . $time_slot_array[$time_slot_index]->course_session_information->courseid . "\n"
              . " Stt course session: "
              . $time_slot_array[$time_slot_index]->course_session_information->stt_course . "\n"
              . " Course name: "
              . $time_slot_array[$time_slot_index]->course_session_information->course_name . "\n"
              . " Primary teacher id: "
              . implode(', ', $editting_teacher_name_array) . "\n"
              // . " Secondary teacher id: "
              // . $time_slot_array[$time_slot_index]->course_session_information->non_editting_teacher_array
              . "</div>";
          }
          if (
            $time_slot_array[$time_slot_index]->is_not_allow_change
            or !empty($this->check_holiday($time_slot_array[$time_slot_index], $this->course_array[0]))
            // or !empty($this->check_forbidden_session($time_slot_array[$time_slot_index], $this->course_array[0]))
          ) {
            echo "<div class='forbidden-session' style ='background-color: #FF0000;'>" . "Tiết: " . $tiet . "</div>";
          }

          echo "<hr style='border-top: 1px dashed #eee; margin: 5px 0;'>"; // Đường kẻ phân cách các buổi

          $tiet++;
          $time_slot_index++;
        }
        echo "</td>";
      }
      echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

    echo "</body>";
    echo "</html>";
  }

  public function edit_sync_start_date_and_end_date_for_course(&$course_array)
  {
    $course_array_length = count($course_array);
    $course = $course_array[0];
    for ($i = 0; $i < $course_array_length; $i++) {
      $course = $course_array[$i];
      if (date("D", $course->startdate)) {
        for ($j = 1; $j < 8; $j++) {
          $temp_start_date = $course->startdate + $j * 24 * 60 * 60;
          if (date("D", $temp_start_date) == "Mon") {
            $course->startdate = strtotime(
              date("d-m-Y", $temp_start_date) . " " . "0:00"
            );
            break;
          }
        }
      }

      if (date("D", $course->enddate)) {
        for ($j = 1; $j < 8; $j++) {
          $temp_end_date = $course->enddate + $j * 24 * 60 * 60;
          if (date("D", $temp_end_date) == "Sun") {
            $course->enddate = strtotime(
              date("d-m-Y", $temp_end_date) . " " . "0:00"
            );
            break;
          }
        }
      }
    }
  }

  public function get_earliest_start_day($course_array)
  {
    $earliest_start_date = $course_array[0]->startdate;
    foreach ($course_array as $course) {
      if ($earliest_start_date > $course->startdate) {

        $earliest_start_date = $course->startdate;

      }
    }
    return $earliest_start_date;
  }

  public function get_latest_end_day($course_array)
  {
    $latest_end_date = $course_array[0]->enddate;
    foreach ($course_array as $course) {
      if ($latest_end_date < $course->enddate) {
        $latest_end_date = $course->enddate;
      }
    }
    return $latest_end_date;

  }

  public function previous_process_edit_time_slot_array(&$time_slot_array, $room_array)
  {
    global $DB;
    // tiến hành xử lý việc đặt trước chỗ cho các khóa học hay các sự kiện đã được đặt trước đó 
    // các time_slot tại đây không được thay đổi trong suốt quá trình đặt lịch.
    $courses_schedule = [];
    $courses_schedule_sql = "SELECT cs.id,
                                    c.id courseid, 
                                    c.category,
                                    cr.id course_room_id, 
                                    c.shortname, 
                                    c.startdate, 
                                    c.enddate, 
                                    c.visible, 
                                    cc.class_duration, 
                                    cc.number_course_session_weekly, 
                                    cc.number_student_on_course, 
                                    tcl.total_course_section, 
                                    cs.class_begin_time, 
                                    cs.class_end_time
                                FROM {local_course_calendar_course_section} cs
                                RIGHT JOIN {course} c on cs.courseid = c.id
                                join {local_course_calendar_course_config_for_calendar} cc on cc.courseid = c.id
                                left join {local_course_calendar_total_course_lesson} tcl on tcl.courseid = c.id
                                left join {local_course_calendar_course_room} cr on cr.id = cs.course_room_id
                                WHERE cs.courseid is not null 
                                      and c.id != 1 
                                      and c.visible = 1 
                                      and c.enddate >= UNIX_TIMESTAMP(NOW())";
    $params = [];
    $courses_schedule = $DB->get_records_sql($courses_schedule_sql, $params);

    // đánh dấu các tiết đã bị chiếm dụng và thuật toán xếp thời khóa biểu sẽ không thay đổi các vị trí này khi xếp.

    $count_time_slot_array = count($time_slot_array);
    $time_slot = $time_slot_array[0];
    for ($i = 0; $i < $count_time_slot_array; $i++) {
      $time_slot = $time_slot_array[$i];
      foreach ($courses_schedule as $course) {
        if ($room_array[$time_slot->room]->id == $course->course_room_id) {
          $class_start = $course->class_begin_time;
          $class_end = $course->class_end_time;
          if (date("d-m-Y", $class_start) == date("d-m-Y", $time_slot->date)) {
            $time_slot_start_session_timestamp = strtotime(
              date("d-m-Y", $time_slot->date) . " " . AVAILABLE_CLASS_SESSIONS[$time_slot->session]
            );

            $time_slot_end_session_timestamp = $time_slot_start_session_timestamp;
            if ($time_slot->session + 1 < count(AVAILABLE_CLASS_SESSIONS)) {
              $time_slot_end_session_timestamp = strtotime(
                date("d-m-Y", $time_slot->date) . " " . AVAILABLE_CLASS_SESSIONS[$time_slot->session + 1]
              );
            } else {
              $time_slot_end_session_timestamp = strtotime(
                date("d-m-Y", $time_slot->date) . " " . AVAILABLE_CLASS_SESSIONS[$time_slot->session]
              ) + TIME_SLOT_DURATION;
            }

            if (
              $class_start < $class_end
              and $class_end <= $time_slot_start_session_timestamp
              and $time_slot_start_session_timestamp < $time_slot_end_session_timestamp
            ) {
              $time_slot->is_not_allow_change = false;
              break;
            } else if (
              $time_slot_end_session_timestamp <= $class_start
              and $class_start < $class_end
              and $time_slot_start_session_timestamp < $time_slot_end_session_timestamp
            ) {
              $time_slot->is_not_allow_change = false;
              break;
            } else {
              $time_slot->is_not_allow_change = true;
              break;
            }
          }
        }
      }
    }
  }

  public function write_log()
  {
    if ($this->file_handle) {
      $value = "**************************This is system config informations: ***************************************\n";
      fwrite($this->file_handle, $value);

      $date = "";
      foreach (DATES as $d) {
        $date . $d . ', ';
      }
      $date . "\n";
      fwrite($this->file_handle, $date);

      $session = "";
      foreach (AVAILABLE_CLASS_SESSIONS as $s) {
        $session . $s . ', ';
      }
      $session . "\n";
      fwrite($this->file_handle, $session);

      fwrite($this->file_handle, "One session is " . TIME_SLOT_DURATION / 60 . " minute \n");
      fwrite($this->file_handle, "Max continue class session is " . MAX_CLASS_DURATION . " sessions \n");
      fwrite($this->file_handle, "Number course session of one course weekly is " . NUMBER_COURSE_SESSION_WEEKLY . "\n");
      fwrite($this->file_handle, "Time gap between course session of same course " . TIME_GAP_BETWEEN_COURSE_SESSION_OF_SAME_COURSE . "\n");

      $value = "***************************************This is time table informations: ***************************************\n";
      fwrite($this->file_handle, $value);

      fwrite($this->file_handle, "Number courses didn't schedule: " . count($this->course_array) . "\n");
      fwrite($this->file_handle, "Number room: " . $this->number_room . "\n");
      fwrite($this->file_handle, "Number teacher and non teacher: " . count($this->teacher_and_non_teacher_array) . "\n");
      fwrite($this->file_handle, "Schedule in time range with start date: " . date("D, d-m-Y", $this->earliest_start_date_timestamp) . "\n");
      fwrite($this->file_handle, "Schedule in time range with end date: " . date("D, d-m-Y", $this->latest_end_date_timestamp) . "\n");
      fwrite($this->file_handle, "Number day in time range: " . $this->number_day . "\n");
      fwrite($this->file_handle, "Number session in one day of one room: " . $this->number_class_sessions . "\n");

      $value = "***************************************This is course array informations: ***************************************\n";
      fwrite($this->file_handle, $value);
      $course_infor = "";
      foreach ($this->course_array as $course) {
        $course_infor .= "Course id: " . $course->courseid . " "
          . "STT Course session " . $course->stt_course . " "
          . "Course category " . $course->category . " "
          . "Course name " . $course->shortname . " "
          . "Course start date " . date("D, d-m-Y", $course->startdate) . " "
          . "Course end date " . date("D, d-m-Y", $course->enddate) . " "
          . "Visible " . $course->visible . " "
          . "Course session duration " . $course->class_duration . " "
          . "Number course session weekly " . $course->number_course_session_weekly . " "
          . "Number student on course " . $course->number_student_on_course . " "
          . "Total course session of course " . $course->total_course_section . "\n";
      }
      fwrite($this->file_handle, $course_infor);

      $value = "***************************************This is teacher and non teacher array informations: ***************************************\n";
      fwrite($this->file_handle, $value);
      $teacher_and_non_teacher_infor = "";
      foreach ($this->teacher_and_non_teacher_array as $teacher) {
        "(user.id) id, user.firstname, user.lastname, user.email, role.shortname";
        $teacher_and_non_teacher_infor .= "Teacher id: " . $teacher->id . " "
          . "Teacher name " . $teacher->firstname . " " . $teacher->lastname . " "
          . "Teacher email " . $teacher->email . " "
          . "Teacher role name " . $teacher->shortname . "\n";
      }
      fwrite($this->file_handle, $teacher_and_non_teacher_infor);
      $value = "***************************************This is room array informations: ***************************************\n";
      fwrite($this->file_handle, $value);
      $room_infor = "";
      foreach ($this->room_array as $room) {
        "";
        $room_infor .= "Room id: " . $room->id . " "
          . "Room number " . $room->room_number . " "
          . "Room floor " . $room->room_floor . " "
          . "Room building " . $room->room_building . " "
          . "Room ward address" . $room->ward_address . " "
          . "Room district address" . $room->district_address . " "
          . "Room province address " . $room->province_address . " "
          . "\n";
      }
      fwrite($this->file_handle, $room_infor);
    }
  }

  /**
   * Summary of create_automatic_calendar_by_recursive_swap_algorithm
   * Hàm này thực hiện việc truy xuất các dữ liệu cần thiết cho việc tạo thời khóa biểu và tiến hành gọi hàm 
   * generate_time_table() để tạo thời khóa biểu.
   * @return time_table_generator
   */
  public function create_automatic_calendar_by_recursive_swap_algorithm()
  {
    global $CFG;
    // ghi log for debug
    $file_path = $CFG->dirroot . "/local/course_calendar/log/debug.txt";
    $file_handle = fopen($file_path, "w");

    // --- Bắt đầu đo thời gian ---
    $start_time = microtime(true);

    $course_array = $this->get_courses_not_schedule();
    $unlocate_course_array = $this->get_courses_not_schedule();
    $available_rooms = $this->get_available_rooms();
    $teacher_and_non_teacher_array = $this->get_teacher_and_non_teacher_array();


    // các biến được chuẩn bị cho việc tạo mảng time slot
    $number_courses_not_schedule = count($course_array);
    $number_room = count($available_rooms);
    $number_class_sessions = count(STT_CLASS_SESSIONS);

    // ngày bắt đầu là ngày học của khóa học bắt đầu sớm nhất và nó phải là t2 của tuần kế tiếp
    // ngày kết thúc là ngày kết thúc của khóa học kết thúc trễ nhất và nó là chủ nhật kế tiếp gần nhất
    // Các khóa học khác cũng đều chỉnh sửa ngày bắt đầu là thứ 2 đầu tuần kế tiếp và ngày kết thúc như vậy. 

    // Điều này làm cho các khóa học đều bắt đầu từ đầu tuần kế tiếp. 
    // Tạo điều kiện thuận lợi cho tính lương, và công tác sau này.

    $this->edit_sync_start_date_and_end_date_for_course($course_array);

    $earliest_start_date_timestamp = $this->get_earliest_start_day($course_array);
    $latest_end_date_timestamp = $this->get_latest_end_day($course_array);
    $number_day = $this->compute_number_day_between_start_day_and_end_day($earliest_start_date_timestamp, $latest_end_date_timestamp) + 1;

    $time_slot_array = [];
    $time_slot_index = 0;

    for ($i = 0; $i < $number_day; $i++) {
      for ($j = 0; $j < $number_room; $j++) {
        for ($k = 0; $k < $number_class_sessions; $k++) {
          $time_slot_array[] = new time_slot(
            $j,
            strtotime(
              date("d-m-Y", $i * 24 * 60 * 60 + $earliest_start_date_timestamp) . " " . AVAILABLE_CLASS_SESSIONS[$k]
            ),
            $k,
            null,
            $time_slot_index,
            false,
            false,
            false,
            $available_rooms[$j]->room_number,
            $available_rooms[$j]->room_floor,
            $available_rooms[$j]->room_building,
            $available_rooms[$j]->ward_address,
            $available_rooms[$j]->district_address,
            $available_rooms[$j]->province_address,
          );
          $time_slot_index++;
        }
      }
    }

    // gọi database ở đây để đánh dấu các time_slot đã được các khóa học trước hoặc các sự kiện trên hệ thống đặt chỗ trước.
    $this->previous_process_edit_time_slot_array($time_slot_array, $available_rooms);

    // hai biến này dùng để giới hạn lại số lần gọi đệ quy để xử lý bài toán.
    // độ sâu tối đa mà thuật toán có thể gọi đến là 16 - theo hướng dẫn của giải thuật fet application - file doc
    // Số lần có thể gọi đệ quy là bằng 2 * số hoạt động có thể có - trong bài toán này số hoạt động có thể có là $number_course_not_schedule.
    $level_recursive = 0;
    $max_level_recursive = MAX_LEVEL_RECURSIVE;
    $number_of_call_recursive = 0;
    $max_number_of_call_recursive = 2 * $number_courses_not_schedule;
    $putted_course = [];

    // Khởi tạo calendar kết quả
    // Tạo calendar bằng giải thuật recursive swap
    // Trả về kết quả.
    $time_table = new time_table_generator(
      $course_array,
      $available_rooms,
      $time_slot_array,
      $number_room,
      $number_day,
      $number_class_sessions,
      $max_level_recursive,
      $max_number_of_call_recursive,
      $number_of_call_recursive,
      $teacher_and_non_teacher_array,
      $level_recursive,
      $earliest_start_date_timestamp,
      $latest_end_date_timestamp,
      $putted_course,
      $unlocate_course_array,

    );

    $time_table->set_file_handle_for_write_log($file_handle);
    $time_table->write_log();

    $time_table->generate_time_table();
    $time_table->insert_teacher_and_non_teacher_to_time_table();

    // --- Kết thúc đo thời gian ---
    $end_time = microtime(true);
    // Tính thời gian đã trôi qua (đơn vị: giây)
    $execution_time = $end_time - $start_time;

    // Chuẩn bị nội dung log
    $log_content = "Giải thuật bắt đầu lúc: " . date("Y-m-d H:i:s", (int) $start_time) . "\n";
    $log_content .= "Giải thuật kết thúc lúc: " . date("Y-m-d H:i:s", (int) $end_time) . "\n";
    $log_content .= "Tổng thời gian chạy: " . number_format($execution_time, 4) . " giây\n\n";
    // Ghi nội dung vào file
    fwrite($file_handle, $log_content);

    fclose($file_handle);

    return $time_table;
  }

}
