<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Controllers;

$url='user';
$controllerClass=Controllers\user\UserController::class;

Route::match(array('GET','POST'),$url.'/registration1', [$controllerClass, 'registration']);
