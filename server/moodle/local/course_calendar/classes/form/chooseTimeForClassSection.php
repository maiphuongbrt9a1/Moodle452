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
namespace local_course_calendar\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
class chooseTimeForClassSection extends \moodleform
{
    /**
     * Elements of the test form.
     */
    public function definition()
    {
        $duration = 90 * 60;
        $mform = $this->_form;

        // Get the courseid from the URL.
        $courses = optional_param('selected_courses', null, PARAM_INT);

        // Add a hidden field to store the courseid.

        $mform->addElement('hidden', 'selected_courses', $courses);
        $mform->setType('selected_courses', PARAM_INT);

        // Get the teachers from the URL.
        $teachers = optional_param_array('selected_teachers', [], PARAM_INT);

        // Add a hidden field to store the courseid.
        foreach ($teachers as $teacher) {
            $mform->addElement('hidden', 'selected_teachers[]', $teacher);
            $mform->setType('selected_teachers[]', PARAM_INT);
        }

        // Thời gian bắt đầu (Start Date and Time)
        $mform->addElement('date_time_selector', 'starttime', 'Thời gian bắt đầu');
        $mform->setDefault('starttime', time()); // Mặc định là thời gian hiện tại
        $mform->addRule('starttime', get_string('required', 'moodle'), 'required', null, 'client');

        // Thời gian kết thúc (End Date and Time)
        $mform->addElement('date_time_selector', 'endtime', 'Thời gian kết thúc');
        // Mặc định là 1 giờ sau thời gian bắt đầu (hoặc bất kỳ khoảng thời gian hợp lý nào)
        $mform->setDefault('endtime', time() + $duration); // HOURSECS = 3600 giây (1 giờ)
        $mform->addRule('endtime', get_string('required', 'moodle'), 'required', null, 'client');

        $this->add_custom_action_buttons();
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        if (isset($data['starttime']) && isset($data['endtime'])) {
            if ($data['endtime'] < $data['starttime']) {
                $errors['endtime'] = get_string('endtimemustbeafterstarttime', 'local_course_calendar');
            }
        }

        return $errors;
    }

    /**
     * Use this method to a cancel and submit button to the end of your form. Pass a param of false
     * if you don't want a cancel button in your form. If you have a cancel button make sure you
     * check for it being pressed using is_cancelled() and redirecting if it is true before trying to
     * get data with get_data().
     *
     * @param bool $cancel whether to show cancel button, default true
     * @param string $submitlabel label for submit button, defaults to get_string('savechanges')
     */
    public function add_custom_action_buttons($cancel = true, $submitlabel = null)
    {
        if (is_null($submitlabel)) {
            $submitlabel = get_string('findroom', 'local_course_calendar');
        }
        $mform = $this->_form;
        // Only use uniqueid if the form defines it needs to be used.
        $forceuniqueid = false;
        if (is_array($this->_customdata)) {
            $forceuniqueid = $this->_customdata['forceuniqueid'] ?? false;
        }
        // Keep the first action button as submitbutton (without uniqueid) because single forms pages expect this to happen.
        $submitbuttonname = $forceuniqueid && $this::$uniqueid > 0 ? 'submitbutton_' . $this::$uniqueid : 'submitbutton';
        if ($cancel) {
            // When two elements we need a group.
            $buttonarray = [
                $mform->createElement('submit', $submitbuttonname, $submitlabel),
                $mform->createElement('cancel'),
            ];
            $buttonarname = $forceuniqueid && $this::$uniqueid > 0 ? 'buttonar_' . $this::$uniqueid : 'buttonar';
            $mform->addGroup($buttonarray, $buttonarname, '', [' '], false);
            $mform->closeHeaderBefore('buttonar');
        } else {
            // No group needed.
            $mform->addElement('submit', $submitbuttonname, $submitlabel);
            $mform->closeHeaderBefore('submitbutton');
        }

        // Increase the uniqueid so that we can have multiple forms with different ids for the action buttons on the same page.
        if ($forceuniqueid) {
            $this::$uniqueid++;
        }
    }
}