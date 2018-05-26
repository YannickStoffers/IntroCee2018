<?php
require_once 'include/init.php';
require_once 'include/form.php';

/** Renders and processes CRUD operations for the  Model */
class SignupForm extends Bootstrap3Form
{
    public function __construct($name, $strict=true){
        $model = get_model('Registration');

        $fields = [
            'type'            => new SelectField   ('Type', $model::$type_options),
            'student_number'  => new StringField   ('Student number',                         true,     ['maxlength' => 8]),
            'first_name'      => new StringField   ('First name',                             !$strict, ['maxlength' => 255]),
            'surname'         => new StringField   ('Surname',                                !$strict, ['maxlength' => 255]),
            'birthday'        => new DateField     ('Date of birth', 'Y-m-d',                 !$strict),
            'address'         => new StringField   ('Street name + number',                   !$strict, ['maxlength' => 255]),
            'postal_code'     => new StringField   ('Postal code',                            !$strict, ['maxlength' => 255]),
            'city'            => new StringField   ('Place of residence',                     !$strict, ['maxlength' => 255]),
            'email'           => new EmailField    ('Email',                                  !$strict),
            'phone'           => new StringField   ('Phone number',                           !$strict, ['maxlength' => 100]),
            'emergency_phone' => new StringField   ('Emergency contact',                      !$strict, ['maxlength' => 100]),
            'iban'            => new StringField   ('IBAN',                                   !$strict, ['maxlength' => 34]),
            'bic'             => new StringField   ('BIC (only for non-Dutch bank accounts)', true,     ['maxlength' => 11]),
            'study'           => new SelectField   ('Field of study', $model::$study_options, !$strict),
            'study_year'      => new NumberField   ('How many years have you been studying?', true,     ['min' => 1, 'max' => 9]),
            'remarks'         => new TextAreaField ('Comments',                               true,     ['maxlength' => 1024]),
            'mentor'          => new CheckBoxField ('I\'m a mentor',                          true),
            'vegetarian'      => new CheckBoxField ('I\'m a vegetarian ',                     true),
            'accept_terms'    => new CheckBoxField ('I have read and accepted the terms and conditions', !$strict),
            'accept_costs'    => new CheckBoxField ('I accept the costs', !$strict),
        ];

        return parent::__construct($name, $fields);
    }
}
