<?php

namespace App\Admin;

require_once "../vendor/autoload.php";

use App\UsersControler;

$users = new UsersControler();
$out = '{';
if ($_POST) {
    $u = ['userName' => $_POST['userName'], 'password' => $_POST['password']];
    try {
        $users->login($u);
    } catch (\Exception $e) {
        $out.='"error":"'.$e->getMessage().'"';
    }
} else {
    $out.='"error":"Giriş Yapılırken bir hata oluştu."';
}
echo $out."}";



