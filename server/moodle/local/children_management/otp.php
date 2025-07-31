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
    try {
        $step2 = required_param('step2', PARAM_BOOL);
    } catch (Exception $e) {
        dlog($e->getTrace());
        $params = [];
        $base_url = new moodle_url('/local/children_management/add_child.php', $params);
        redirect($base_url, "You must enter children information.", 0, \core\output\notification::NOTIFY_ERROR);
    }

    global $CFG, $USER, $DB, $PAGE;
    $PAGE->requires->js('/local/children_management/js/lib.js');
    $PAGE->set_url(new moodle_url('/local/children_management/otp.php', []));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('children_management_title', 'local_children_management'));
    $PAGE->set_heading(get_string('children_management_add_child_form_heading', 'local_children_management'));
    $PAGE->requires->css('/local/children_management/style/style.css');


    // Instantiate the myform form from within the plugin.
    $mform = new \local_children_management\form\add_child_form_step_2();
    $toform = '';

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        // If there is a cancel element on the form, and it was pressed,
        // then the `is_cancelled()` function will return true.
        // You can handle the cancel operation here.
        redirect(new moodle_url('/local/children_management/add_child.php'));
    } else if ($fromform = $mform->get_data()) {
        // When the form is submitted, and the data is successfully validated,
        // the `get_data()` function will return the data posted in the form.

        if (time() > $SESSION->add_child_form_otp_code_expiration_time) {
            // xử lý việc quá hạn thời gian ở đây.
            redirect(
                new moodle_url('/local/children_management/add_child.php', []),
                'error: This OTP code is expired. Please get other OTP code.',
                0,
                \core\output\notification::NOTIFY_ERROR
            );
        }

        $otp_code = $SESSION->add_child_form_otp_code;
        if ($fromform->OTP != $otp_code) {
            redirect(
                new moodle_url('/local/children_management/otp.php', ['step2' => true]),
                'error: This OTP code is invalid. Please type valid OTP code.',
                0,
                \core\output\notification::NOTIFY_ERROR
            );
        } else {
            // If have information about student. Add this information to children_and_parent_information table
            $data = new stdClass();
            $data->parentid = $SESSION->add_child_form_parentid;
            $data->childrenid = $SESSION->add_child_form_childrenid;
            $data->createtime = $SESSION->add_child_form_createtime;
            $data->lastmodifytime = $SESSION->add_child_form_lastmodifytime;

            if ($DB->insert_record('children_and_parent_information', $data)) {
                redirect(new moodle_url('/local/children_management/index.php', []), 'Add new children with children ID: ' . $data->childrenid . ' successfully', 0, \core\output\notification::NOTIFY_SUCCESS);

            } else {
                redirect(new moodle_url('/local/children_management/add_child.php', []), 'Error: Add new children with children ID: ' . $data->childrenid . ' failed', 0, \core\output\notification::NOTIFY_ERROR);
            }

        }

    } else {
        // This branch is executed if the form is submitted but the data doesn't
        // validate and the form should be redisplayed or on the first display of the form.

        // Set anydefault data (if any).
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
