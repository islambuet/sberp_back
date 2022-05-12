<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers as Controllers;

$url = 'user';
$controllerClass = Controllers\system\user\UserController::class;

Route::match(['GET', 'POST'], $url . '/registration', [$controllerClass, 'registration']);
Route::match(['GET', 'POST'], $url . '/send-otp', [$controllerClass, 'sendOtp']);
Route::post($url . '/recover-password', [$controllerClass, 'recoverPassword']);
Route::match(['GET', 'POST'], $url . '/verify-email', [$controllerClass, 'verifyEmail']);
Route::post($url . '/login', [$controllerClass, 'login']);
Route::match(['GET', 'POST'], $url . '/logout', [$controllerClass, 'logout']);

Route::middleware('logged-user')->group(function () {
    $url = 'user';
    $controllerClass = Controllers\system\user\UserController::class;
    //Route::post($url.'/profile-picture', [$controllerClass, 'profilePicture']);
    Route::post($url . '/change-password', [$controllerClass, 'ChangePassword']);
    Route::match(['GET', 'POST'], $url . '/get-default-menu', [$controllerClass, 'getDefaultMenu']);
    Route::match(['GET', 'POST'], $url . '/get-companies', [$controllerClass, 'getCompanies']);
    Route::match(['GET', 'POST'], $url . '/get-company-menu/{companyId}', [$controllerClass, 'getCompanyMenu']);

    $url = 'user/schedules';
    $controllerClass = Controllers\system\user\UserschedulesController::class;
    Route::match(['GET', 'POST'], $url . '/initialize', [$controllerClass, 'initialize']);
    Route::post($url . '/add-items', [$controllerClass, 'addItems']);
});
