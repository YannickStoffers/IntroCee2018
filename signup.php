<?php
require_once 'include/init.php';
require_once 'include/form.php';

/** Renders and processes CRUD operations for the  Model */
class SignupView extends FormView
{
    protected $model;
    protected $template_base_name = 'templates/signup/signup';

    public function __construct(){
        parent::__construct('Sign up', 'signup');
        $this->model = get_model('Registration');
    }

    /** Creates and returns the request form */
    protected function get_form() {
        // Create fields
        $study_year_options = [];
        for ( $i = 1; $i <= 9; $i++ )
            $study_year_options['_'.$i] = $i > 1 ? $i . ' years': $i . ' year';

        $fields = [
            'type'            => new SelectField   ('Type', ['First-year', 'Senior'], 'First-year'),
            'student_number'  => new StringField   ('Student number', true),
            'first_name'      => new StringField   ('First name'),
            'surname'         => new StringField   ('Surname'),
            'birthday'        => new DateField     ('Date of birth', 'Y-m-d'),
            'address'         => new StringField   ('Street name + number'),
            'postal_code'     => new StringField   ('Postal code'),
            'city'            => new StringField   ('Place of residence'),
            'email'           => new EmailField    ('Email'),
            'phone'           => new StringField   ('Phone number'),
            'emergency_phone' => new StringField   ('Emergency contact'),
            'iban'            => new StringField   ('IBAN'),
            'bic'             => new StringField   ('BIC (only for non-Dutch bank accounts)', true),
            'study'           => new SelectField   ('Field of study', $this->model::$study_options),
            'study_year'      => new SelectField   ('I have been studying for ', $study_year_options, null, true),
            'remarks'         => new TextAreaField ('Comments', true),
            'mentor'          => new CheckBoxField ('I\'m a mentor', true),
            'vegetarian'      => new CheckBoxField ('I\'m a vegetarian ', true),
            'accept_terms'    => new CheckBoxField ('I have read and accepted the terms and conditions'),
            'accept_costs'    => new CheckBoxField ('I accept the costs'),
        ];

        $form = new Bootstrap3Form('signup', $fields);

        if (cover_session_logged_in()){
            $member = get_cover_session();
            $form->prefill_values([
                'type' => $member->beginjaar < date('Y') ? 'Senior': 'First-year',
                'first_name' => $member->voornaam,
                'surname' => empty($member->tussenvoegsel) ? $member->achternaam : $member->tussenvoegsel . ' ' . $member->achternaam,
                'birthday' => $member->geboortedatum,
                'address' => $member->adres,
                'postal_code' => $member->postcode,
                'city' => $member->woonplaats,
                'email' => $member->email,
                'phone' => $member->telefoonnummer,
                'study_year' => '_'. (date('Y') - $member->beginjaar + 1)
            ]);
        }

        return $form;
    }

    /** Renders response indicating whether the valid form was successfully processed (or not) */
    protected function form_valid($form){
        try {
            $this->process_form_data($form->get_values());
            $context = ['status' =>  'success'];
        } catch (Exception $e) {
            $context = [
                'status' => 'error', 
                'message' => $e->getMessage()
            ];
        }
        return $this->render_template($this->get_template('form_processed'), $context);
    }

    /** Renders an invalid form */
    protected function form_invalid($form) { 
        return $this->render_template($this->get_template('form'), ['form' => $form]);
    }
    
    /** Processes the data of a valid form */
    protected function process_form_data($data) {
        $data['study_year'] = trim($data['study_year'], '_');
        
        // There is no field called mentor in the database, so remove it
        if ($data['mentor'])
            $data['type'] = 'Mentor';
        unset($data['mentor']);

        // Convert booleans to tinyints
        $data['vegetarian'] = empty($data['vegetarian']) ? 0 : 1;
        $data['accept_terms'] = empty($data['accept_terms']) ? 0 : 1;
        $data['accept_costs'] = empty($data['accept_costs']) ? 0 : 1;
        
        $this->model->create($data);

        // Notify admins
        $success = send_mail(
            ADMIN_EMAIL,
            filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            $this->render_template($this->get_template('email'), $data),
            null,
            [sprintf('Bcc: %s', ADMIN_EMAIL)]
        );

        // Determine wether email has ben send succesfully
        if (!$success)
            throw new Exception('Your registration has been stored in our database, but we failed to send you a confirmation email!');
    }
}


// Create and run home view
$view = new SignupView();
$view->run();
