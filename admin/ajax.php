<?php

namespace App\Admin;

use App\AjaxController;

require_once "../vendor/autoload.php";


$ajax= new AjaxController();

if($ajax->checkAjax()){
    $ajaxData= (object) $_POST['ajaxData'];

    echo call_user_func(array($ajax,$ajaxData->functionName),$ajaxData->data);
}



