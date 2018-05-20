<?php
require_once 'include/init.php';
require_once 'include/SignupForm.class.php';

/** Renders and processes Signup form */
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
        $form = new SignupForm('signup');

        if (cover_session_logged_in()){
            $member = get_cover_session();
            $form->populate_fields([
                'type' => $member->beginjaar < date('Y') ? 'Senior': 'First-year',
                'first_name' => $member->voornaam,
                'surname' => empty($member->tussenvoegsel) ? $member->achternaam : $member->tussenvoegsel . ' ' . $member->achternaam,
                'birthday' => $member->geboortedatum,
                'address' => $member->adres,
                'postal_code' => $member->postcode,
                'city' => $member->woonplaats,
                'email' => $member->email,
                'phone' => $member->telefoonnummer,
                'study_year' => (date('Y') - $member->beginjaar + 1)
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
