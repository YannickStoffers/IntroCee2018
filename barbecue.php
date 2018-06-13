<?php
require_once 'include/init.php';
require_once 'include/SignupFormBarbecue.class.php';

/** Renders and processes Signup form */
class BarbecueView extends FormView
{
    protected $model;
    protected $template_base_name = 'templates/signup/signup_barbecue';

    public function __construct(){
        parent::__construct('barbecue', 'Barbecue');
        $this->model = get_model('Barbecue');
    }

    /** Creates and returns the request form */
    protected function get_form() {
        $form = new SignupFormBarbecue('barbecue');

        // Set default value for type
        $form->populate_field('type', 'First-year');

        // Prefill form with data from the website if user is logged in.
        if (cover_session_logged_in()){
            $member = get_cover_session();
            $form->populate_fields([
                'type' => $member->beginjaar < date('Y') ? 'Senior': 'First-year',
                'first_name' => $member->voornaam,
                'surname' => empty($member->tussenvoegsel) ? $member->achternaam : $member->tussenvoegsel . ' ' . $member->achternaam,
                'birthday' => $member->geboortedatum,
                'address' => $member->adres,
                'city' => $member->woonplaats,
                'email' => $member->email,
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
    
    /** Processes the data of a valid form */
    protected function process_form_data($data) {        
        // Convert booleans to tinyints
        $data['vegetarian'] = empty($data['vegetarian']) ? 0 : 1;
        $data['accept_terms'] = empty($data['accept_terms']) ? 0 : 1;
        $data['accept_costs'] = empty($data['accept_costs']) ? 0 : 1;
        
        $this->model->create($data);

        // Send confirmation email
        $success = send_mail(
            ADMIN_EMAIL,
            filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            $this->render_template($this->get_template('email'), $data),
            null,
            [sprintf('Bcc: %s', ADMIN_EMAIL)]
        );

        // Determine whether email has been send successfully
        if (!$success)
            throw new HttpException(500, 'Your registration has been stored in our database, but we failed to send you a confirmation email!');
    }
}


// Create and run home view
$view = new BarbecueView();
$view->run();
