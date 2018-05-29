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

    /** Implement custom validation */
    public function validate() {
        $result = parent::validate();

        // Validate if Student number is set for first years
        if ($this->get_value('type') === 'First-year') {
            $snum = $this->get_value('student_number');
            if (!isset($snum) || empty(trim($snum))){
                $this->get_field('student_number')->errors[] = 'Student number is required';
                $result = false &&  $result;
            }
        }

        // Validate if BIC is set for non-Dutch IBANs.
        if (substr(strtoupper($this->get_value('iban')), 0, 2) !== 'NL') {
            $bic = $this->get_value('bic');
            if (!isset($bic) || empty(trim($bic))){
                $this->get_field('bic')->errors[] = 'BIC is required for non-Dutch bank accounts';
                $result = false &&  $result;
            }
        }

        return $result;
    }
}
