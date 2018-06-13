<?php
require_once 'include/models/Model.class.php';

class barbecue extends Model
{
    public static $type_options = ['First-year','Senior'];
    public static $study_options = ['Artificial Intelligence','Computing Science','Other'];
    public static $status_options = ['registered','cancelled'];

    public function __construct($db) {
        parent::__construct($db, 'barbecue');
    }
}
