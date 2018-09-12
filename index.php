<?php
require_once 'include/init.php';

// Create and run home view
$view = new TemplateView('index');
$closed = True;
if ($closed) {
	$view = new TemplateView('closed');
}

$view->run();
