<?php
require_once 'include/init.php';

// Create and run home view
$view = new TemplateView('Organisation', 'committee');
$view->run();
