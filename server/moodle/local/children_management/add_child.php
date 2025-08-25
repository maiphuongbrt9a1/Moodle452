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
 * TODO describe file index
 *
 * @package    local_children_management
 * @copyright  2025 Võ Mai Phương <vomaiphuonghhvt@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->dirroot . '/local/children_management/lib.php');
require_once($CFG->dirroot . '/local/dlog/lib.php');
require_once($CFG->dirroot . '/local/children_management/classes/form/add_child_form.php');

try {
    require_login();
    require_capability('local/children_management:edit', context_system::instance());

    global $CFG, $USER, $DB, $PAGE;
    $PAGE->requires->js('/local/children_management/js/lib.js');
    $PAGE->set_url(new moodle_url('/local/children_management/add_child.php', []));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('children_management_title', 'local_children_management'));
    $PAGE->set_heading(get_string('children_management_add_child_form_heading', 'local_children_management'));
    $PAGE->requires->css('/local/children_management/style/style.css');


    // Instantiate the myform form from within the plugin.
    $mform = new \local_children_management\form\add_child_form();
    $toform = '';

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        // If there is a cancel element on the form, and it was pressed,
        // then the `is_cancelled()` function will return true.
        // You can handle the cancel operation here.
        redirect(new moodle_url('/local/children_management/index.php'));
    } else if ($fromform = $mform->get_data()) {
        // When the form is submitted, and the data is successfully validated,
        // the `get_data()` function will return the data posted in the form.

        // find children information from user table in system.
        // if have student information then insert information to children_and_parent_information table
        // if not information about student then return add new child fail because don't have this account in system

        // Check and search student information 
        // prepare search condition
        $studentid_search_query = trim($fromform->studentid);
        $email_search_query = trim($fromform->email);

        $params = [
            'searchparamid' => $studentid_search_query,
            'search_param_email' => $email_search_query
        ];

        // Process the search query.
        $sql = "SELECT *
            FROM {user} user
            WHERE   (
                        user.id = :searchparamid
                        and user.email = :search_param_email
                    )";

        $student = $DB->get_record_sql($sql, $params);

        if (!$student) {
            redirect(
                new moodle_url('/local/children_management/add_child.php', []),
                'error: This student with student ID: ' . $studentid_search_query . ' and student email: ' . $email_search_query . ' was not found.',
                0,
                \core\output\notification::NOTIFY_ERROR
            );
        } else {
            if (
                $DB->get_record('children_and_parent_information', ['parentid' => $USER->id, 'childrenid' => $student->id])
            ) {
                redirect(
                    new moodle_url('/local/children_management/add_child.php', []),
                    'error: This student has been added.',
                    0,
                    \core\output\notification::NOTIFY_ERROR
                );
            }

            $SESSION->add_child_form_parentid = $USER->id;
            $SESSION->add_child_form_childrenid = $fromform->studentid;
            $SESSION->add_child_form_createtime = time();
            $SESSION->add_child_form_lastmodifytime = time();
            $SESSION->add_child_form_otp_code = rand(10000000, 99999999);
            $SESSION->add_child_form_otp_code_expiration_time = time() + 5 * 60;
            $SESSION->add_child_form_email = $fromform->email;
            $step2 = true;

            // xử lý việc gửi otp code 
            $to = $student;
            $from = get_admin();
            $subject = 'OTP Authentication for add new children';
            $message = 'Your OTP is: ' . $SESSION->add_child_form_otp_code;

            if (email_to_user($to, $from, $subject, $message)) {
                $msg = "success";
            } else {
                $msg = 'error';
                redirect(
                    new moodle_url('/local/children_management/add_child.php', []),
                    'error: Can not send email that contains otp code to student. Please contact to manager of center.',
                    0,
                    \core\output\notification::NOTIFY_ERROR
                );
            }
            redirect(
                new moodle_url('/local/children_management/otp.php', ['step2' => $step2]),
                'You must complete the verification process for add new children.' . " Children ID: " . $fromform->studentid,
                0,
                \core\output\notification::NOTIFY_INFO
            );
        }

    } else {
        // Set anydefault data (if any).
        // Xử lý việc lấy lại OTP code ở đây.
        $mform->set_data($toform);

        // Display the form.
    }

    echo $OUTPUT->header();

    $mform->display();

    echo $OUTPUT->footer();

} catch (Exception $e) {
    echo "<pre>";
    var_dump($e->getTrace());
    echo "</pre>";

    throw new \moodle_exception('error', 'local_children_management', '', null, $e->getMessage());
}
