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

/**
 * Summary of TIME_ZONE
 * @var string Define the timezone for the course calendar. 'Asia/Ho_Chi_Minh'
 */
const TIME_ZONE = 'Asia/Ho_Chi_Minh';
/**
 * Summary of MAX_CALENDAR_NUMBER
 * @var int Số lượng thời khóa biểu ban đầu của quần thể
 */
const MAX_CALENDAR_NUMBER = 50;

/**
 * Summary of MAX_STEP_OF_CROSSOVER_OPERATIONS
 * @var int Số lượng bước lai ghép tối đa trong một thế hệ.
 */
const MAX_STEP_OF_CROSSOVER_OPERATIONS = 20;

// CÀI ĐẶT THÔNG TIN CẤU HÌNH CHO PLUGIN LOCAL COURSE CALENDAR
// CÀI ĐẶT CÁC LUẬT RÀNG BUỘC CHO XỬ LÝ THỜI KHÓA BIỂU
// GIẢI THUẬT ĐỂ GIẢI LÀ GIẢI THUẬT DI TRUYỀN

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
 * @var array define date of week ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
 */
const DATES= ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];


/**
 * Summary of AVAILABLE_CLASS_SESSIONS
 * @var array define available class sesstions ['7:30','8:15','9:00', '9:45', '10:30', '11:15', '13:30', '14:15', '15:00', '15:45', '17:30', '18:15', '19:00', '19:45', '20:30', '21:15']
 */
const AVAILABLE_CLASS_SESSIONS = ['7:30','8:15','9:00', '9:45', '10:30', '11:15', '13:30', '14:15', '15:00', '15:45', '17:30', '18:15', '19:00', '19:45', '20:30', '21:15'];
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
const STT_CLASS_SESSIONS = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15];
const START_MORNING = '7:30';
const END_MORNING = '12:00';
const START_AFTERNOON = '13:30';
const END_AFTERNOON = '16:30';
const START_EVENING = '17:30';
const END_EVENING = '22:00';

/**
 * Summary of TIME_SLOT_DURATION
 * @var int Thời gian mỗi tiết học là 45 phút
 */
const TIME_SLOT_DURATION = 45*60;

/**
 * Summary of CLASS_DURATION
 * @var int Thời gian mỗi ca học là 90 phút (2 tiết học x 45 phút)
 */
const CLASS_DURATION = 90 * 60; 
/**
 * Summary of number_course_session_weekly
 * @var int
 */
const NUMBER_COURSE_SESSION_WEEKLY = 2;

class course_session_information {
  public $courseid;
  public $course_name;
  public $course_session_length;
  public $course_session_start_time;
  public $course_session_end_time;

  public $editting_teacherid;
  public $non_editting_teacherid;

  public $date;
  public $room;

  public function __construct($courseid = null, 
                              $course_name = null, 
                              $course_session_length = null, 
                              $course_session_start_time = null,
                              $course_session_end_time = null, 
                              $editting_teacherid = null,
                              $non_editting_teacherid = null,
                              $date = null,
                              $room = null)
  {
    $this->courseid = $courseid;
    $this->course_name = $course_name;
    $this->course_session_length = $course_session_length;
    $this->course_session_start_time = $course_session_start_time;
    $this->course_session_end_time = $course_session_end_time;
    $this->editting_teacherid = $editting_teacherid;
    $this->non_editting_teacherid = $non_editting_teacherid;
    $this->date = $date;
    $this->room = $room;
  }

  public function set_value($courseid = null, 
                              $course_name = null, 
                              $course_session_length = null, 
                              $course_session_start_time = null,
                              $course_session_end_time = null, 
                              $editting_teacherid = null,
                              $non_editting_teacherid = null,
                              $date = null,
                              $room = null)
  {
    $this->courseid = $courseid;
    $this->course_name = $course_name;
    $this->course_session_length = $course_session_length;
    $this->course_session_start_time = $course_session_start_time;
    $this->course_session_end_time = $course_session_end_time;
    $this->editting_teacherid = $editting_teacherid;
    $this->non_editting_teacherid = $non_editting_teacherid;
    $this->date = $date;
    $this->room = $room;
  }
}

function deep_clone_array($array) {
  $clone = array();
  $number_room = count($array);
  $number_day = count(DATES);
  $number_class_sessions = count(AVAILABLE_CLASS_SESSIONS);

  for ($i=0; $i < $number_room; $i++) { 
      $clone[] = [];
      for ($j = 0; $j < $number_day; $j++) {
        $clone[$i][] = [];
        for ($k=0; $k < $number_class_sessions; $k++) { 
          $clone[$i][$j][] = new course_session_information
          (
            $array[$i][$j][$k]->courseid,
            $array[$i][$j][$k]->course_name,
            $array[$i][$j][$k]->course_session_length,
            $array[$i][$j][$k]->course_session_start_time,
            $array[$i][$j][$k]->course_session_end_time,
            $array[$i][$j][$k]->editting_teacherid,
            $array[$i][$j][$k]->non_editting_teacherid,
            $array[$i][$j][$k]->date,
            $array[$i][$j][$k]->room
          );
          
        }
      }
    }
  return $clone;
}

// BÊN DƯỚI LÀ CÁC RÀNG BUỘC VỀ THỜI GIAN CỦA LỚP HỌC
/**
 * Summary of UT_HT1
 * HT1. Các lớp - môn học phải được dạy trọn vẹn trong một buổi của một ngày trong tuần (một Lớp Môn học không được cắt ra thành các tiết cuối buổi sáng và đầu buổi chiều hay cuối ngày này và đầu buổi sáng hôm sau).
 * @var int
 */
const UT_HT1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT1
/**
 * Summary of check_class_duration
 * HT1. Các lớp - môn học phải được dạy trọn vẹn trong một buổi của một ngày trong tuần (một Lớp Môn học không được cắt ra thành các tiết cuối buổi sáng và đầu buổi chiều hay cuối ngày này và đầu buổi sáng hôm sau).
 * @param mixed $class_start_time Start time of class
 * @return boolean true if start time and end time are in one session (Morning session/ Afternoon session or evening session)
 */
function is_class_duration_in_one_session($class_start_time, $class_duration = CLASS_DURATION) {
  $class_end_time = (int)$class_start_time + $class_duration;
  
  $class_start_time = date('H:i', $class_start_time);
  $class_end_time = date('H:i', $class_end_time);
  
  $start_morning = date('H:i', strtotime('7:30'));
  $end_morning = date('H:i', strtotime('12:00'));
  
  $start_afternoon = date('H:i', strtotime('13:30'));
  $end_afternoon = date('H:i', strtotime('16:30'));

  $start_everning = date('H:i', strtotime('17:30'));
  $end_everning = date('H:i', strtotime('22:00'));

  if ($class_start_time >= $start_morning and $class_end_time <= $end_morning) {
    return true;
  }
  else if ($class_start_time >= $start_afternoon and $class_end_time <= $end_afternoon) {
    return true;
  }
  else if ($class_start_time >= $start_everning and $class_end_time <= $end_everning) {
    return true;
  }
  else {
    return false;
  }
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
function is_forbidden_session ($class_start_time, $class_duration = CLASS_DURATION) {
  $class_end_time = (int)$class_start_time + $class_duration;
  
  $class_start_time = date('H:i', $class_start_time);
  $class_end_time = date('H:i', $class_end_time);

  $start_everning = date('H:i', strtotime('17:30'));
  $end_everning = date('H:i', strtotime('22:00'));

  if (
      date('D', $class_start_time) === 'Mon' 
      or date('D', $class_start_time) === 'Tue'
      or date('D', $class_start_time) === 'Wed'
      or date('D', $class_start_time) === 'Thu'
      or date('D', $class_start_time) === 'Fri'
    ) {
      if ($class_start_time >= $start_everning and $class_end_time <= $end_everning) {
        return false;
      }

      return true;

  }

  return !is_class_duration_in_one_session($class_start_time, $class_duration);

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
 * @param mixed $class_start_time class strat time
 * @param mixed $class_duration class duration time
 * @return bool true if this is holiday else false
 */
function is_holiday($class_start_time, $class_duration = CLASS_DURATION) {
  $class_end_time = (int)$class_start_time + $class_duration;
  global $DB;
  $holiday_records = $DB->get_records('local_course_calendar_holiday');
  if (empty($holiday_records)) {
    return false;
  }
  else {
    foreach ($holiday_records as $holiday) {
      if (date('d-m-Y', $class_start_time) === date('d-m-Y', $holiday)) {
        return true;
      }
    }
  }
  return false;
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
function is_class_overtime ($class_start_time, $class_end_time, $system_class_duration= CLASS_DURATION) {
  if ((int)($class_end_time - $class_start_time) >= (int)$system_class_duration) {
    return true;
  }

  return false;
}
/**
   * HT6 Phải tổ chức lớp học đủ số buổi trên tuần tuân theo quy tắc hợp đồng
   * vd môn A học 3 buổi trên 1 tuần thì mỗi tuần phải học đủ 3 buổi.
   * đối với các môn học 2 buổi trên tuần thì tương tự.
   */
const UT_HT6 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT6
/**
 * Summary of check_number_of_course_session_weekly
 * HT6 Phải tổ chức lớp học đủ số buổi trên tuần tuân theo quy tắc hợp đồng
 * vd môn A học 3 buổi trên 1 tuần thì mỗi tuần phải học đủ 3 buổi.
 * đối với các môn học 2 buổi trên tuần thì tương tự.
 * @param mixed $calendar calendar need check 
 * @param mixed $course_id_param course id need check enough session
 * @param mixed $class_duration class duration of course
 * @param mixed $number_course_session_weekly number course session must be taught by teacher on each week. 
 * @return bool true if not enough number course sessions weekly else false.
 */
function is_not_enough_number_of_course_session_weekly($calendar, $course_id_param, $class_duration = CLASS_DURATION, $number_course_session_weekly = NUMBER_COURSE_SESSION_WEEKLY) {
  $number_course_sessions = 0;
  //$calendar[room-ith][session-jth] = [courseid, teacherid];
  $number_room = count($calendar);
  $number_day = count(DATES);
  $number_session = count(AVAILABLE_CLASS_SESSIONS);

  for($i=0; $i < $number_room; $i++) {
    for ($j=0; $j < $number_session; $j++) {
      if(!empty($calendar[$i][$j])) {
        $courseid = $calendar[$i][$j][0];
        $teacherid = $calendar[$i][$j][1];
        if ($courseid == $course_id_param) {
          $number_course_sessions++;
          if (ceil($class_duration / TIME_SLOT_DURATION) > 1) {
            $skip_session = ceil($class_duration / TIME_SLOT_DURATION) - 1;
            $j += $skip_session;
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

 // Các ràng buộc cứng về không gian của lớp học
 /**
  * HP1 Tại mỗi thời điểm một phòng học chỉ được sử dụng cho một lớp - môn học.
  */
const UT_HP1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HP1
// chưa xong đang tìm cách để so sánh cái Class-start-time truyền vào nó có trùng với cái session nào trong tkb và tại đó thì [courseid, teacherid] không rỗng
function is_duplicate_course_at_same_room_at_same_time($calendar, $course_id_param, $class_start_time, $class_duration) {
  // $number_room = count($calendar);
  // $number_session = count($calendar[0]);
  // for($i= 0; $i < $number_room; $i++) {
  //   for($j = 0; $j < $number_session; $j++) {
  //     // chưa xong đang tìm cách để so sánh cái Class-start-time truyền vào nó có trùng với cái session nào trong tkb và tại đó thì [courseid, teacherid] không rỗng.
  //     // vấn đề là cái number session này nó lại là số thứ tự tiết (tiết 1, tiết 2, tiết 3,,.....) nó không phải unixtimestamp không so sánh được với $class_start_time
  //     if(!empty($calendar[$i][$j]) && ) {
        
  //     }
  //   }
  // }
  return false;
}


  /**
   * HP2 sĩ số của một lớp - môn học không được vượt quá sĩ số tối đa của phòng học. mặc định là 25 học sinh một phòng học.
   */
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
 * ST1: Thời khóa biểu của một sinh viên (hay một
 * lớp cứng) nên hạn chế các ngày học cả 2 buổi sáng
 * và chiều;
  */
const UT_ST1 = 1000; // ĐIỂM ƯU TIÊN cho ràng buộc ST1

// ĐÁNH HỆ SỐ CHO VI PHẠM
// học cả hai buổi sáng chiều t7 - cn và có nhiều hơn một buổi chiều của ngày (trong t2-t6)
const VP_ST1_GRAVE_VIOLATION = 4;
// học cả hai buổi sáng chiều t7 - cn 
const VP_ST1_SERIOUS_VIOLATION = 3;
// học cả hai buổi sáng chiều t7 hoặc sáng chiều cn
const VP_ST1_MODERATE_VIOLATION = 2;
// học cả chiều t7 và sáng cn
const VP_ST1_MINOR_VIOLATION = 1;
// không có vi phạm
const VP_ST1_NO_VIOLATION = 0;

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
// ở giữa có 1 tiết trống 
const VP_ST2_MINOR_VIOLATION = 1;
// không có vi phạm 
const VP_ST2_NO_VIOLATION = 0;

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
// giảng viên đi dạy có 3 ca trên buổi nhưng có tiết trống xen giữa (3ca = 3*1h30phut = 4h30phut / 2 buổi )
const VP_ST3_SERIOUS_VIOLATION = 3;
// giảng viên đi dạy có 2 ca và không có tiết trống xen giữa
const VP_ST3_MODERATE_VIOLATION = 2;
// giảng viên đi dạy 3 ca 1 buổi không có tiết trống xen giữa
const VP_ST3_MINOR_VIOLATION = 1;
// không có vi phạm (giảng viên dạy trên 3 ca và buổi là liên tục sáng/ chiều, chiều/tối)
const VP_ST3_NO_VIOLATION = 0;

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
// được xếp vào chiều t2 - t6
const VP_ST4_GRAVE_VIOLATION = 4;
// xếp lịch vào sáng t7
const VP_ST4_SERIOUS_VIOLATION = 3;
// xếp lịch vào sáng cn
const VP_ST4_MODERATE_VIOLATION = 2;
// được xếp vào tối t2 - t6
const VP_ST4_MINOR_VIOLATION = 1;
// được xếp vào chiều hoặc tối t7 hoặc cn
const VP_ST4_NO_VIOLATION = 0;

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

const UT_ST6 = 1000; // ĐIỂM ƯU TIÊN cho ràng buộc ST6

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

 // Các ràng buộc mềm về không gian của lớp học
 /**
  * SP1: Các lớp học - môn học của một buổi học của một sinh viên, giảng viên cần được xếp vào các phòng học gần nhau
  * Điều này giúp sinh viên và giảng viên có thể di chuyển dễ dàng giữa các phòng học trong cùng một buổi học.
  * Ví dụ: nếu một sinh viên có hai lớp học trong cùng một buổi sáng, thì các lớp học này nên được xếp vào các phòng học gần nhau.
  * Và cơ sở học của môn A là CS1 thì cơ sở học của môn B cũng nên là CS1 tại cùng một thời điểm.
  */
const UT_SP1 = 10000; // ĐIỂM ƯU TIÊN cho ràng buộc SP1

// ĐÁNH HỆ SỐ CHO VI PHẠM
// CÁC LỚP HỌC ở hai cơ sở phường xã quận huyện tỉnh khác nhau
const VP_SP1_GRAVE_VIOLATION = 4;
// các phòng học thuộc cùng 1 cơ sở nhưng cách nhau trên hai tòa nhà 
const VP_SP1_SERIOUS_VIOLATION = 3;
// các phòng cùng 1 cơ sở cách nhau một tòa nhà
const VP_SP1_MODERATE_VIOLATION = 2;
// các phòng ở cùng 1 cơ sở nhưng cách nhau 2 tầng
const VP_SP1_MINOR_VIOLATION = 1;
// cùng 1 cơ sở cùng tầng hoặc ở trên 1 tầng.
const VP_SP1_NO_VIOLATION = 0;

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




/**
 * Extend the settings navigation for course calendar.
 * @param settings_navigation $settingsnav The settings navigation object.
 * @param context $context The context of the current page.
 */ 
function local_course_calendar_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/course_calendar:edit_total_lesson_for_course', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $strfoo = get_string('edit_total_lesson_for_course', 'local_course_calendar');
        $url = new moodle_url('/local/course_calendar/edit_total_lesson_for_course.php', array('courseid' => $PAGE->course->id));
        $foonode = navigation_node::create(
            $strfoo,
            $url,
            navigation_node::NODETYPE_LEAF,
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

/**
 * Summary of create_calendar
 * @param array $courses     // courses is array with format [courseid, courseid, courseid,....]
 * @param array $teachers      // teachers is array with format [teacherid, teacherid, teacherid,....]
 * @param array $time_and_addresses // room_time and room address is array with format 
    * [
    * 'room_time_id|room_address_id' , 
    * 'room_time_id|room_address_id' ,
    * 'room_time_id|room_address_id' ,
    * 'room_time_id|room_address_id' ,
    * 'room_time_id|room_address_id' ,...
    * ]
 * @return array
 */
function create_manual_calendar(array $courses, array $teachers, array $times_and_addresses) : array {
    // define global variable
    global $CFG, $PAGE, $DB, $USER;
    // 1 ngày có 16 tiết 7h30 đến 22h tức là 8 ca. 7 ngày một tuần -> tổng tiết = 7*16 = 112 tiết
    // $calendar is 2-dimensional array. Tiết(session)(tiết 1 - tiết 112) - Phòng(room)
    // $calendar[room-ith][session-jth] =  [courseid, teacherid]
    // calendar format 
    /**
        * -----------------------------thứ 2-----------------------------------------------------------Thứ3----------------------------------------------------------------------Thứ4------------------------------------------Thứ5-------------------------------------------------------------------------Thứ6-----------------------------------------------------------------Thứ7----------------------------------------------------------cn----------------------
        * --------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||--------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||--------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||--------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||--------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||--------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||--------Tiết 0-----------------tiết 1.-------.Tiết 14-..tiet15||
*Room 101  courseid                       courseid
*Room 102  courseid                       courseid
*Room 103  courseid                       courseid
*Room 104  courseid                       courseid
*Room 105  courseid                       courseid
*Room 106  courseid                       courseid
*Room 107  courseid                       courseid
*Room 108  courseid                       courseid
*Room 109 courseid                        courseid
*Room 201 courseid                        courseid
*Room 202 courseid                       courseid
     */
    $calendar = [];

    // Consit of 50 calendar [[calendar1], [calendar2], [calendar3]......]
    $fifty_best_calendars = [];
    // process address and time to create $time_and_addresses = [[room_time_id, room_address_id], [room_time_id, room_address_id], [room_time_id, room_address_id], ]
    $times_and_addresses_after_process = [];
    foreach ($times_and_addresses as $time_and_address) {
      $time_and_address = explode('|', $time_and_address);
      $times_and_addresses_after_process[] = $time_and_address;
    }
    // return result in times_and_addresses
    $times_and_addresses = $times_and_addresses_after_process;

    // define course with teacher. Teacher have major that can teach course.
    // teacher major === course category.
    // course-teacher is array with format [[courseid, teacherid], [courseid, teacherid], [courseid, teacherid], ....]
    $course_with_teacher_informations = [];
    foreach ($teachers as $teacher) {
      $teacher_majors = [];
      $teacher_major_ids = [];
      
      // get teacher major by course category.
      $sql_get_teacher_major = "SELECT  distinct (course_categories.id) id, course_categories.name
                              from mdl_user user
                              join mdl_role_assignments ra on ra.userid = user.id
                              join mdl_role role on role.id = ra.roleid
                              join mdl_context context on context.id = ra.contextid
                              join mdl_course course on course.id = context.instanceid
                              join mdl_course_categories course_categories on course.category = course_categories.id
                              where course.id != 1 and user.id = :teacher_id
                                      and (role.shortname = 'teacher' or role.shortname = 'editingteacher')
                                      and context.contextlevel = 50 
                              ORDER BY user.id ASC";
      $params = ['teacher_id' => $teacher];
      $teacher_majors = $DB->get_records_sql($sql_get_teacher_major, $params);

      if (!empty($teacher_majors)) { 
          foreach ($teacher_majors as $major) {
              $teacher_major_ids[] = $major->id;
          }
      }

      // find course with course category === teacher major id
      // scan courses
      foreach ($courses as $course) {
        // get course with courseid and get course category id
        $course_record = $DB->get_record('course', ['id'=> $course], 'category');

        // if course category === teacher major id then insert teacherid and courseid to $course_with_teacher_informations
        if (!empty($course_record)) {
          foreach ($teacher_major_ids as $teacher_major_id) {
            if ($course_record->category == $teacher_major_id) {
              // add course-teacher in $course_with_teacher_informations[]
              $course_with_teacher_informations[] = [$course, $teacher];
              break;
            }
          }
        }
      }
    }
    // init calendar - this is first calendar and it is result calendar.  
    // please fix me to adapt calendar format 
    // note $class_start_time and class_end_time are saved by unixtimestamp not tiết 1, tiết 2, tiết 3, tiết 4.
    // 
    $temp_calendar = [];
    $temp_time_array = [];
    $temp_address_array = [];

    foreach ($times_and_addresses as $time_and_address) {
      $room_time_id = $time_and_address[0];
      $room_address_id = $time_and_address[1];

      $temp_address_array[] = $room_address_id;
      $temp_time_array[] = $room_time_id;

      foreach ($course_with_teacher_informations as $course_with_teacher) {
        $courseid = $course_with_teacher[0];
        $teacherid = $course_with_teacher[1];
        
        $temp_calendar_element = [$room_time_id, $room_address_id , $courseid, $teacherid];
        $temp_calendar[] = $temp_calendar_element;
      }
    }

    foreach(DATES as $date) {
      foreach(STT_CLASS_SESSIONS as $stt_class_session) {

      }
    }

    // Trả về calendar có điểm số vi phạm thấp nhất 
    // mặc định thời khóa biểu đó là thời khóa biểu đầu tiên trong 50 thời khóa biểu tốt nhất được chọn ra
    // $calendar = $fifty_best_calendars[0];
    $calendar = $temp_calendar;
    return $calendar;
}
/**
 * Summary of create_automatic_calendar:
 * Hàm này dùng để lên lịch tự động cho tất cả các khóa học chưa có lịch học.
 * Luồng xử lý: Lấy ra tất cả các khóa học mà chưa có lịch học. Và xếp thời khóa biểu cho khóa học đó.
 * Hàm này chỉ xếp thời khóa biểu cho course. Còn giảng viên khi tạo khóa học đã thêm vào giảng viên vào khóa học rồi thì giảng viên sẽ phải đi dạy theo thời khóa biểu này
 * @return array $calendar là mảng chứa kết quả thời khóa biểu cần cấu trúc thời khóa biểu là $calendar[room][date][sesstion].
 */

function create_automatic_calendar () {
  global $DB;
  $courses_not_schedule_sql = "SELECT c.id courseid, c.category, c.shortname, c.startdate, c.enddate, c.visible
                              FROM {local_course_calendar_course_section} cs
                              RIGHT JOIN {course} c on cs.courseid = c.id
                              WHERE cs.courseid is null and c.id != 1 and c.visible = 1 and c.enddate >= UNIX_TIMESTAMP(NOW())";
  $params = [];
  $courses_not_schedule = $DB->get_records_sql($courses_not_schedule_sql, $params);
  $available_rooms = $DB->get_records('local_course_calendar_course_room');
  
  $courses_not_schedule_courseid_array = [];
  foreach($courses_not_schedule as $course) {
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

  $fifty_calendars = [];
  $calendar = [];

  for ($i=0; $i < $number_room; $i++) { 
    $calendar[] = [];
    for ($j = 0; $j < $number_day; $j++) {
      $calendar[$i][] = [];
      for ($k=0; $k < $number_class_sessions; $k++) { 
        $calendar[$i][$j][] = new course_session_information();
      }
    }
  }

  // init 50 null calendar 
  for ($i=0; $i < MAX_CALENDAR_NUMBER; $i++) { 
    $fifty_calendars[] = deep_clone_array($calendar); 
  }

  // pass value for 50 random calendar
  for($i = 0; $i < MAX_CALENDAR_NUMBER; $i++) {
    $index = 0;
    $used_room_day_session_array = [];
    for($j = 0; $j < NUMBER_COURSE_SESSION_WEEKLY * $number_courses_not_schedule; $j++) {
      // Đảm bảo rằng trong bất kỳ thời khóa biểu khởi tạo mặc định thì với mỗi môn học đều có NUMBER_COURSE_SESSION_WEEKLY.
      // Đảm bảo bằng cách duyệt qua và thêm vào n lần khóa học vào thời khóa biểu với n = NUMBER_COURSE_SESSION_WEEKLY.
      $courseid = $courses_not_schedule_courseid_array[$index];
      if($index === $number_courses_not_schedule - 1) {
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
            $random_session = random_int(0, $number_class_sessions - 1);
          }
        }
      }
      ($fifty_calendars[$i][$random_room][$random_day][$random_session])->set_value(
        $courses_not_schedule[$courseid]->courseid, 
        $courses_not_schedule[$courseid]->shortname, 
        CLASS_DURATION / TIME_SLOT_DURATION, 
        $random_session,
        $random_session + (CLASS_DURATION / TIME_SLOT_DURATION),
        null, 
        null,
        $random_day,
        $random_room
      );
      
      $used_room_day_session_array[] = [$random_room, $random_day, $random_session];
    }
    
  }

  // gen algorithsm
  // fix me again

  return $fifty_calendars[0];
}