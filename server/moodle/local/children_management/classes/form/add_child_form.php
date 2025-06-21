<?php

namespace local_children_management\form;

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class add_child_form extends \moodleform {
    // Add elements to form.
    public function definition() {
        // A reference to the form is stored in $this->form.
        // A common convention is to store it in a variable, such as `$mform`.
        $mform = $this->_form; // Don't forget the underscore!
        // Add elements to your form.
        $mform->addElement('text', 'studentid', get_string('studentid', 'local_children_management'));

        // Set type of element.
        $mform->setType('studentid', PARAM_TEXT);

        // Default value.
        $mform->setDefault('studentid', defaultValue: '');

        // Add elements to your form.
        $mform->addElement('text', 'username', get_string('username'));

        // Set type of element.
        $mform->setType('username', PARAM_TEXT);

        // Default value.
        $mform->setDefault('username', '');

        // Add elements to your form.
        $mform->addElement('text', 'firstname', get_string('firstname'));

        // Set type of element.
        $mform->setType('firstname', PARAM_TEXT);

        // Default value.
        $mform->setDefault('firstname', '');
        // Add elements to your form.
        $mform->addElement('text', 'lastname', get_string('lastname'));

        // Set type of element.
        $mform->setType('lastname', PARAM_TEXT);

        // Default value.
        $mform->setDefault('lastname', '');

         // Add elements to your form.
        $mform->addElement('text', 'email', get_string('email'));

        // Set type of element.
        $mform->setType('email', PARAM_EMAIL);

        // Default value.
        $mform->setDefault('email', '');

         // Add elements to your form.
        $mform->addElement('text', 'phone', get_string('phone'));

        // Set type of element.
        $mform->setType('phone', PARAM_TEXT);

        // Default value.
        $mform->setDefault('phone', '');
       
        // Add elements to your form.
        $mform->addElement('text', 'OTP', get_string('OTP', 'local_children_management'));

        // Set type of element.
        $mform->setType('OTP', PARAM_TEXT);

        // Default value.
        $mform->setDefault('OTP', '');
       
        $this->add_action_buttons();
        
    }

    // Custom validation should be added here.
    function validation($data, $files) {
        return [];
    }
}
