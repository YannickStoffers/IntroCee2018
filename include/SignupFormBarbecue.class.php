<?php
require_once 'include/init.php';
require_once 'include/form.php';

/** Renders and processes CRUD operations for the  Model */
class SignupFormBarbecue extends Bootstrap3Form
{
    public function __construct($name, $strict=true){
        $model = get_model('Barbecue');
        $study_options = [['Select your study', ['selected', 'disabled']]];
        $study_options = array_merge($study_options, $model::$study_options);

        $fields = [
            'type'            => new SelectField   ('Type', $model::$type_options),
            'first_name'      => new StringField   ('First name',                             !$strict, ['maxlength' => 255]),
            'surname'         => new StringField   ('Surname',                                !$strict, ['maxlength' => 255]),
            'birthday'        => new DateField     ('Date of birth', 'Y-m-d',                 !$strict),
            'address'         => new StringField   ('Street name + number',                   !$strict, ['maxlength' => 255]),
            'city'            => new StringField   ('Place of residence',                     !$strict, ['maxlength' => 255]),
            'email'           => new EmailField    ('Email',                                  !$strict),
            'iban'            => new StringField   ('IBAN',                                   !$strict, ['maxlength' => 34]),
            'bic'             => new StringField   ('BIC (only for non-Dutch bank accounts)', true,     ['maxlength' => 11]),
            'study'           => new SelectField   ('Field of study', $study_options, !$strict),
            'remarks'         => new TextAreaField ('Comments',                               true,     ['maxlength' => 1024]),
            'vegetarian'      => new CheckBoxField ('I\'m a vegetarian ',                     true),
            'accept_costs'    => new CheckBoxField ('I accept the costs', !$strict),
        ];

        return parent::__construct($name, $fields);
    }

    /** Implement custom validation */
    public function validate() {
        $result = parent::validate();

        // Validate if BIC is set for non-Dutch IBANs.
        if (substr(strtoupper(trim($this->get_value('iban'))), 0, 2) !== 'NL') {
            $bic = $this->get_value('bic');
            if (!isset($bic) || empty(trim($bic))){
                $this->get_field('bic')->errors[] = 'BIC is required for non-Dutch bank accounts';
                $result = false &&  $result;
            }
        }

        return $result;
    }
}
