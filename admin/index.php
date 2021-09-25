<?php
namespace App\Admin;
use App\Config;
use App\SlideController;
use App\SQLiteConnection;

error_reporting(E_ALL);

require_once "../vendor/autoload.php";


try{
    $slides= new SlideController();

    echo "<pre>";
    var_export($slides->getSlides());
    echo "<pre>";

} catch (\Exception $e){
    echo $e->getMessage();
}