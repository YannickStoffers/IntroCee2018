<?php
require_once 'include/models/Model.class.php';

class Registration extends Model
{
    public static $type_options = ['First-year','Senior','Mentor','Board','HEROcee','IntroCee','PhotoCee'];
    public static $study_options = ['Artificial Intelligence','Computing Science','Other'];

    public function __construct($db) {
        parent::__construct($db, 'registrations');
    }
}
