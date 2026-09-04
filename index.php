<?php
declare(strict_types=1);

namespace App;

date_default_timezone_set('Europe/Istanbul');
setlocale(LC_ALL, 'tr_TR.UTF-8');

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Request;
use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();
Routes::register($router);

$request = new Request();
$router->dispatch($request);
