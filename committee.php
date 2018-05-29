<?php
require_once 'include/init.php';

// Create and run home view
$view = new TemplateView('committee', 'Organisation');
$view->run();
