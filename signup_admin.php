<?php
require_once 'include/init.php';
require_once 'include/form.php';
require_once 'include/SignupForm.class.php';


/** Renders and processes CRUD operations for the Signup Model */
class SignupAdminView extends ModelView
{
    protected $views = ['update', 'list'];
    protected $template_base_name = 'templates/signup/signup_admin';

    /** 
     * Run the page, but only for logged in committee members. 
     * Non-admins are only allowed to see a list of their redirects
     */
    public function run_page() {
        if (!cover_session_logged_in())
            throw new HttpException(401, 'Unauthorized', sprintf('<a href="%s" class="btn btn-primary">Login and get started!</a>', cover_login_url()));
        else if (!cover_session_in_committee(ADMIN_COMMITTEE))
            throw new HttpException(403, 'You need to be IntroCee to see this page!');
        else
            return parent::run_page();
    }

    /** Create and returns the form to use for create and update */
    protected function get_form() {
        $form = new SignupForm('registration', false);
        // Signup form is slightly optimized for non-admin use
        $form->add_field('status',  new SelectField('Status', $this->get_model()::$status_options));
        $form->delete_field('mentor');
        return $form;
    }

    /** Maps a valid form to its database representation */
    protected function process_form_data($data) {
        // Convert booleans to tinyints
        $data['vegetarian'] = empty($data['vegetarian']) ? 0 : 1;
        $data['accept_terms'] = empty($data['accept_terms']) ? 0 : 1;
        $data['accept_costs'] = empty($data['accept_costs']) ? 0 : 1;
        
        parent::process_form_data($data);   
    }
}

// Create and run subdomain view
$view = new SignupAdminView('signup_admin', 'Registrations', get_model('Registration'));
$view->run();
