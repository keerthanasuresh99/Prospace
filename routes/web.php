<?php

use App\Http\Controllers\BaseSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EventsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Route::get('/', 'LoginController@index')->name('login');

Route::get('/', [LoginController::class, 'index'])->name('login');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::group(['prefix' => 'base-settings'], function () {
        Route::post('/', [BaseSettingsController::class, 'store'])->name('baseSettingsStore');
        Route::post('/update', [BaseSettingsController::class, 'update'])->name('baseSettingsUpdate');
        Route::get('/delete/{id}', [BaseSettingsController::class, 'destroy'])->name('baseSettingsDelete');
        Route::get('/list-achievers', [BaseSettingsController::class, 'getAchieversList'])->name('list-achievers');
        Route::get('/list-sub-achievers', [BaseSettingsController::class, 'getSubAchieversList'])->name('list-sub-achievers');
        Route::post('/add-sub-achievers', [BaseSettingsController::class, 'addSubachiever'])->name('add-sub-achiever');
        Route::get('/delete-sub-achiever', [BaseSettingsController::class, 'deleteSubachiever'])->name('delete-sub-achiever');
        Route::post('/update-subachiever', [BaseSettingsController::class, 'updateSubachiever'])->name('update-subachiever');
        Route::get('/list-templates', [BaseSettingsController::class, 'listTemplates'])->name('list-templates');
        Route::post('/fetch-subachievers', [BaseSettingsController::class, 'fetchSubachievers'])->name('fetch-subachievers');
        Route::post('/add-template', [BaseSettingsController::class, 'addTemplate'])->name('add-template');
        Route::get('edit-template', [BaseSettingsController::class, 'editTemplate'])->name('edit-template');
        Route::post('/update-template', [BaseSettingsController::class, 'updateTemplate'])->name('update-template');
        Route::get('/delete-template', [BaseSettingsController::class, 'deleteTemplate'])->name('delete-template');

        //Route for Event building
        Route::get('/list-event-builders', [EventsController::class, 'listEventbuilders'])->name('list-event-builders');
        Route::post('/event-builders-approve/{user}', [EventsController::class, 'approveEventbuilders'])->name('event-builders.approve');
        Route::post('/event-builders-reject/{user}', [EventsController::class, 'rejectEventbuilders'])->name('event-builders.reject');
    });
});

Route::post('/login', [LoginController::class, 'authenticate'])->name('authenticate');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
