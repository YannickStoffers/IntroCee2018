<?php

/**
 * View: An abstract class to provide a basic view interface
 */
abstract class View
{

    public function __construct() {
        $this->title = $title;
        $this->page_id = $page_id;
    }

    /** Run the view */
    abstract public function run();

    /** Function to redirect the browser to a different location */
    protected function redirect($url, $status_code=302) {
       header('Location: ' . $url, true, $status_code);
       die();
    }
}
