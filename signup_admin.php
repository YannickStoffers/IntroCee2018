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

    /** Run the list view and make admins and non-admins see a different set of subdomains */
    protected function run_list() {
        $registrations = $this->get_model()->get();
        return $this->render_template($this->get_template(), ['registrations' => $registrations]);
    }

    /** Create and returns the form to use for create and update */
    protected function get_form() {
        $form = new SignupForm('registration', false);
        $form->add_field('status',  new SelectField('Status', $this->get_model()::$status_options, 'registered'));
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
$view = new SignupAdminView('Registrations', 'signup_admin', get_model('Registration'));
$view->run();
