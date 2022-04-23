<?php

use App\Http\Controllers as Controllers;
use Illuminate\Support\Facades\Route;

Route::middleware('logged-user')->group(function () {

    $url = '{companyId}/setup/branches';
    $controllerClass = Controllers\company\setup\BranchesController::class;

    Route::match(['GET', 'POST'], $url . '/initialize', [$controllerClass, 'initialize']);
    Route::match(['GET', 'POST'], $url . '/get-items', [$controllerClass, 'getItems']);
    Route::match(['GET', 'POST'], $url . '/get-item/{itemId}', [$controllerClass, 'getItem']);
    Route::post($url . '/save-item', [$controllerClass, 'saveItem']);

    $url = '{companyId}/setup/company-user-groups';
    $controllerClass = Controllers\company\setup\CompanyUserGroupsController::class;

    Route::match(['GET', 'POST'], $url . '/initialize', [$controllerClass, 'initialize']);
    Route::match(['GET', 'POST'], $url . '/get-items', [$controllerClass, 'getItems']);
    Route::match(['GET', 'POST'], $url . '/get-item/{itemId}', [$controllerClass, 'getItem']);
    Route::post($url . '/save-item', [$controllerClass, 'saveItem']);
    Route::post($url . '/save-role/{itemId}', [$controllerClass, 'saveRole']);

    $url = '{companyId}/company-users';
    $controllerClass = Controllers\company\setup\CompanyUsersController::class;

    Route::match(['GET', 'POST'], $url . '/initialize', [$controllerClass, 'initialize']);
    Route::match(['GET', 'POST'], $url . '/get-items', [$controllerClass, 'getItems']);
    // Route::match(['GET', 'POST'], $url . '/get-item/{itemId}', [$controllerClass, 'getItem']);
    Route::post($url . '/save-items', [$controllerClass, 'saveItems']);

    $url = '{companyId}/invite-company-users';
    $controllerClass = Controllers\company\setup\CompanyUserInvitationsController::class;

    Route::match(['GET', 'POST'], $url . '/initialize', [$controllerClass, 'initialize']);
    Route::match(['GET', 'POST'], $url . '/get-items', [$controllerClass, 'getItems']);
    // Route::match(['GET', 'POST'], $url . '/get-item/{itemId}', [$controllerClass, 'getItem']);
    Route::post($url . '/save-items', [$controllerClass, 'saveItems']);
});
