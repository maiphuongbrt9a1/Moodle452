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

defined('MOODLE_INTERNAL') || die();
const TIME_ZONE = 'Asia/Ho_Chi_Minh'; // Define the timezone for the course calendar.

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
  * ngày t2 - t6 có 3 ca dạy
  */


const TIME_SLOT_DURATION = 45; // Thời gian mỗi tiết học là 45 phút
const CLASS_DURATION = 90; // Thời gian mỗi ca học là 90 phút (2 tiết học x 45 phút)

// BÊN DƯỚI LÀ CÁC RÀNG BUỘC VỀ THỜI GIAN CỦA LỚP HỌC
/*  HT1. Các lớp - môn học phải được dạy trọn vẹn
 trong một buổi của một ngày trong tuần (một Lớp
Môn học không được cắt ra thành các tiết cuối buổi
 sáng và đầu buổi chiều hay cuối ngày này và đầu
buổi sáng hôm sau).*/
const UT_HT1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT1
/*
 *  HT2. Số lớp - môn học được quy định tránh
 không được xếp vào một số tiết học cụ thể vào trước 17h30 phút các ngày từ thứ 2 đến thứ 6 
 và thời điểm kết thúc của môn học cuối cùng là vào 22h. 
 t2 - t6 (17h30 - 22h)
*/
const UT_HT2 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT2

 /** 
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
   * HT5 Thời gian học của một lớp học không được vượt quá 1h30 phút liên tục.
   * Điều này có nghĩa là mỗi buổi học không được kéo dài quá 1h30 phút liên tục.
   * Sau mỗi 1h30 phút học, cần có thời gian nghỉ ngơi hoặc chuyển sang môn học khác.
   */
const UT_HT5 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT5

/**
   * HT6 Phải tổ chức lớp học đủ số buổi trên tuần tuân theo quy tắc hợp đồng
   * vd môn A học 3 buổi trên 1 tuần thì mỗi tuần phải học đủ 3 buổi.
   * đối với các môn học 2 buổi trên tuần thì tương tự.
   */
const UT_HT6 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HT6

 // Các ràng buộc cứng về không gian của lớp học
 /**
  * HP1 Tại mỗi thời điểm một phòng học chỉ được sử dụng cho một lớp - môn học.
  */
const UT_HP1 = 1000000000; // ĐIỂM ƯU TIÊN cho ràng buộc HP1

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

function create_calendar(array $courses, array $teachers, array $time_and_addresses) : array {
    
    
    return [];
}
