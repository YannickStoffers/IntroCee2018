<?php
require_once 'include/utils.php';
require_once 'include/View.class.php';

if (!defined('ERROR_TEMPLATE'))
    define('ERROR_TEMPLATE', 'templates/error.phtml');

/**
 * TemplateView: A class to manage a view based on a template
 */
class TemplateView extends View
{
    // The template to use for the content of this page (defaults to /templates/<page_id>.phtml)
    protected $template;

    // The title of the page
    protected $title;

    // The ID of the page
    protected $page_id;

    public function __construct($title, $page_id='') {
        $this->title = $title;
        $this->page_id = $page_id;
    }

    /** Run the view */
    public function run() {
        try {
            echo $this->run_page();
        } catch (Exception $e) {
            echo $this->run_exception($e);
        } catch (TypeError $e) {
            echo $this->run_exception($e);
        }
    }

    /** Run the view */
    protected function run_page() {
        return $this->render_template($this->get_template());
    }

    /** Handle exceptions encountered during running */
    protected function run_exception($e){
        if ($exception instanceof HttpException){
            $html_message = $e->getHtmlMessage();
            $status = $e->getCode();
        } else {
            $html_message = null;
            $status = 500;
        }
        
        http_response_code($status);

        return $this->render_template(ERROR_TEMPLATE, [
            'title' => 'Error',
            'exception' => $e,
            'status' => $status,
            'message' => $e->getMessage(),
            'html_message' => $html_message,
            'exception' => $e,
        ]);
    }

    /** Render a template */
    protected function render_template($template, array $context=[]) {
        $default_context = [
            'title' => $this->title,
            'page_id' => $this->page_id,
        ];
        $templ = new Template($template, array_merge($default_context, $context));
        return $templ->render();
    }

    /** Returns the name of the template to use */
    protected function get_template($view_name='') {
        if (isset($this->template))
            return $this->template;
        if (empty($view_name))
            return sprintf('templates/%s.phtml', $this->page_id);
        return sprintf('templates/%s_%s.phtml', $this->page_id, $view_name);
    }
}
