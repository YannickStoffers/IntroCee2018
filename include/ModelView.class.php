<?php
require_once 'include/utils.php';
require_once 'include/FormView.class.php';


/**
 * ModelView: An abstract class to manage CRUD(L) actions for a Model object.
 * Separates reading a single object and listing multiple objects for clarity.
 */
abstract class ModelView extends FormView
{
    // The names of the available views (override to limit actions)
    protected $views = ['create', 'read', 'update', 'delete', 'list'];

    // The default view to run if $_GET['view'] is not provided
    protected $default_view = 'list';

    // The base name of the template to use (defaults to 'templates/<page_id>')
    protected $template_base_name;

    protected $_view;

    public function __construct($title, $page_id='', $model=null) {
        $this->model = $model;
        parent::__construct($title, $page_id);

        if (!isset($_GET['view']))
            $this->_view = $this->default_view;
        else if (in_array($_GET['view'], $this->views))
            $this->_view = $_GET['view'];
        else
            report_error('View is unknown!', 400);
    }

    /** Runs the correct function based on the $_GET['view'] parameter */
    public function run_page() {
        if ($this->_view === 'create')
            return $this->run_create();
        else if ($this->_view === 'read')
            return $this->run_read();
        else if ($this->_view === 'update')
            return $this->run_update();
        else if ($this->_view === 'delete')
            return $this->run_delete();
        else if ($this->_view === 'list')
            return $this->run_list();
        else
            report_error('View is unknown!', 400);
    }

    /** Runs the create view */
    protected function run_create() {
        $form = $this->get_form();
        return $this->run_form($form);
    }

    /** Runs the read view */
    protected function run_read() {
        $object = $this->get_object();
        return $this->render_template($this->get_template(), ['object' => $object]);
    }

    /** Runs the update view */
    protected function run_update() {
        $form = $this->get_form();
        if ($_SERVER['REQUEST_METHOD'] === 'GET')
            $form->populate($this->get_object());
        return $this->run_form($form);
    }

    /** Runs the delete view */
    protected function run_delete() {
        $object = $this->get_object();

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->get_model()->delete_by_id($object['id']);
            $this->redirect($this->get_success_url());
        }

        return $this->render_template($this->get_template(), ['object' => $object]);
    }

    /** Runs the list view */
    protected function run_list() {
        return $this->render_template($this->get_template(), ['objects' => $this->get_model()->get()]);
    }

    /** Processes the form data for create and update */
    protected function process_form_data($data) {
        if ($this->_view === 'create')
            $this->get_model()->create($data);
        else if ($this->_view === 'update')
            $this->get_model()->update_by_id($this->get_object()['id'], $data);
    }

    /** Returns the Model object to use for the view */
    protected function get_model() {
        if (!isset($this->model))
            die('Please define the model property or override the get_model method!');
        return $this->model;
    }

    /** Returns the object referenced to by the $_GET['id'] parameter */
    protected function get_object() {
        static $object = null;

        if (!isset($_GET['id']))
            report_error('Please provide an ID!', 400);

        if ($object !== null)
            return $object;

        $object = $this->get_model()->get_by_id($_GET['id']);

        if (empty($object))
            report_error('No object found for id', 404);

        return $object;
    }

    /** Returns the Form object to use for create and update */
    protected function get_form() {
        if (!isset($this->form))
            die('Please define the form property or override the get_form method!');
        return $this->form;
    }

    /** Returns the url to redirect to after (successful) create, update or delete */
    protected function get_success_url() {
        $parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        return $parts[0];
    }

    /** Returns the name of the template to use to render the current view */
    protected function get_template($view_name='') {
        if (empty($view_name))
            $view_name = $this->_view;

        if (isset($this->template_base_name))
            return sprintf('%s_%s.phtml', $this->template_base_name, $view_name);

        return sprintf('templates/%s_%s.phtml', $this->page_id, $view_name);
    }
}
