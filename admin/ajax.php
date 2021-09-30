<?php

namespace App\Admin;

use App\AjaxController;

require_once "../vendor/autoload.php";


$ajax = new AjaxController();

if ($ajax->checkAjax()) {
    $ajaxData = $_POST;
    $functionName = $ajaxData['functionName'];
    unset($ajaxData['functionName']);
    echo call_user_func(array($ajax, $functionName), $ajaxData);
}