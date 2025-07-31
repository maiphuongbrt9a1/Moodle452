<?php

namespace local_children_management\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class add_child_form extends \moodleform
{
    // Add elements to form.
    public function definition()
    {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!
        // Add elements to your form.
        $mform->addElement('text', 'studentid', get_string('studentid', 'local_children_management'));

        // Set type of element.
        $mform->setType('studentid', PARAM_INT);
        $mform->addRule('studentid', 'Student ID is missing.', 'required', null, 'client');

        // Add elements to your form.
        $mform->addElement('text', 'email', get_string('email'));

        // Set type of element.
        $mform->setType('email', PARAM_EMAIL);
        $mform->addRule('email', 'Email is missing.', 'required', null, 'client');

        $this->add_action_buttons(true, "Get OTP Code");

    }

    // Custom validation should be added here.
    function validation($data, $files)
    {
        return [];
    }
}


class add_child_form_step_2 extends \moodleform
{
    // Add elements to form.
    public function definition()
    {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!

        // Add elements to your form.

        $mform->addElement('text', 'OTP', get_string('OTP', 'local_children_management'));

        // Set type of element.
        $mform->setType('OTP', PARAM_INT);
        $mform->addRule('OTP', 'OTP code is required', 'required', null, 'client');

        // Get the courseid from the URL.
        $step_2_flag = optional_param('step2', false, PARAM_BOOL);

        // Add a hidden field to store the courseid.

        $mform->addElement('hidden', 'step2', $step_2_flag);
        $mform->setType('step2', PARAM_BOOL);


        $this->add_action_buttons(true, "Check OTP");

    }

    // Custom validation should be added here.
    function validation($data, $files)
    {
        return [];
    }
}
