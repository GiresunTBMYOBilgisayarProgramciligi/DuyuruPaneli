<?php

namespace App\Admin;

use App\AjaxController;

require_once "../vendor/autoload.php";


$ajax = new AjaxController();

if ($ajax->checkAjax()) {
    if ($_POST) $ajaxData = $_POST;
    if ($_GET) $ajaxData = $_GET;
    if(isset($ajaxData)){
        $functionName = $ajaxData['functionName'];
        unset($ajaxData['functionName']);
        echo call_user_func(array($ajax, $functionName), $ajaxData);
    }
}