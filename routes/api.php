<?php

use App\Http\Controllers\Api\AchievementTimelineController;
use App\Http\Controllers\Api\AchievementTrackerController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\TeamMembersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\BaseSettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::namespace('Api')->group(function () {

    /* ---------------------------------SHOSPACE ROUTES---------------------------------- */

    Route::post('users', [UsersController::class, 'store']);
    Route::get('achievers-list', [UsersController::class, 'getAchieversList']);
    Route::post('sub-achievers-list', [UsersController::class, 'getSubachieversList']);
    Route::get('get-user', [UsersController::class, 'getUser']);
    Route::post('add-users', [UsersController::class, 'addUser']);
    Route::post('get-template', [UsersController::class, 'getTemplate']);
    Route::post('list/introducers', [TeamMembersController::class, 'getIntoducers']);
    Route::post('add/team-member', [TeamMembersController::class, 'addTeammember']);
    Route::post('list/team-members', [TeamMembersController::class, 'listTeammembers']);
    Route::post('get/team-members', [TeamMembersController::class, 'listTeammembers']);
    Route::get('fetch-user-details', [TeamMembersController::class, 'getUserByPhoneNumber']);
    Route::get('get-introducer', [TeamMembersController::class, 'getIntroducer']);

    Route::get('list/achievement', [AchievementTimelineController::class, 'getAchievementTitles']);
    Route::post('add/achievement-dream', [AchievementTimelineController::class, 'addAchievementDream']);
    Route::get('list/achievement-dream', [AchievementTimelineController::class, 'listAchievementDream']);
    Route::get('delete/achievement-dream', [AchievementTimelineController::class, 'deleteAchievementDream']);
    Route::get('list/featured-dream', [AchievementTimelineController::class, 'listFeaturedDreams']);


    /* -----------------------------PROSPACE ROUTES---------------------------------------------- */
    //Achievemt Tracker API
    Route::get('get/achievers-list', [AchievementTrackerController::class, 'getAchieversList']);
    Route::get('get/achievement-titles', [AchievementTrackerController::class, 'achievementTitle']);
    Route::post('add/achievements', [AchievementTrackerController::class, 'addAchievements']);
    Route::post('get/achievement-tracker', [AchievementTrackerController::class, 'getAchievementTracker']);
    Route::post('add/achievement-target', [AchievementTrackerController::class, 'addachievementTarget']);
    Route::get('list/team-achievements', [AchievementTrackerController::class, 'listTeamachievements']);
    Route::post('achievement-tracker/previous_months', [AchievementTrackerController::class, 'getPreviousmonnthtracker']);
    Route::post('five-in-one-achievement-tracker', [AchievementTrackerController::class, 'getFiveInOneAchievementTracker']);
    Route::get('edit-achievement', [AchievementTrackerController::class, 'editAchievements']);
    Route::get('delete-achievement', [AchievementTrackerController::class, 'deleteAchievements']);

    //Permissions API
    Route::post('request/permission', [PermissionController::class, 'addPermission']);
    Route::post('get/permission', [PermissionController::class, 'getPermission']);
    Route::get('permission', [PermissionController::class, 'updateStatus']);

    //Edit Team Member
    Route::get('edit/team-member', [TeamMembersController::class, 'editTeammember']);
    Route::post('update/team-member', [TeamMembersController::class, 'updateTeammember']);
    Route::get('delete/team-member', [TeamMembersController::class, 'deleteTeammember']);
});
