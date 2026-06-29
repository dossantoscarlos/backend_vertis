<?php

use App\Http\Controllers\Api\Auth\AclController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CampaignLocationController;
use App\Http\Controllers\Api\DashboardDataController;
use App\Http\Controllers\Api\DashboardUserController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\FinancialTransactionController;
use App\Http\Controllers\Api\SurveyController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', LoginController::class)->name('api.auth.login');
Route::post('/me/acl', AclController::class)->name('api.me.acl');
Route::get('/dashboard-data', DashboardDataController::class)->name('api.dashboard-data');
Route::apiResource('regions', RegionController::class);
Route::apiResource('campaigns', CampaignController::class);
Route::apiResource('partners', PartnerController::class);
Route::apiResource('locations', CampaignLocationController::class)->parameters([
    'locations' => 'campaignLocation',
]);
Route::apiResource('users', DashboardUserController::class);
Route::apiResource('roles', RoleController::class)->parameters([
    'roles' => 'role',
]);
Route::apiResource('finances', FinancialTransactionController::class)->parameters([
    'finances' => 'financialTransaction',
]);
Route::apiResource('surveys', SurveyController::class)->parameters([
    'surveys' => 'survey',
]);
