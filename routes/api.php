<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'UserController@login');
    
Route::resource('user','UserController');
Route::resource('company','CompanyController');
Route::resource('cleaner','CleanerController');
Route::resource('admin','AdminController');
Route::resource('customer','CustomerController');
Route::resource('supervisor','SupervisorController');
Route::resource('group','GroupController');
Route::resource('group_member','GroupMemberController');
Route::resource('image','ImageController');
Route::resource('comment','CommentController');
Route::resource('job','JobController');
Route::resource('inspection','InspectionController');
Route::resource('history','HistoryController');
Route::resource('inspection_comment','InspectionCommentController');
Route::resource('sale','SaleController');
Route::resource('sales','SalesController');
Route::resource('note','NoteController');
Route::resource('cleaning_company','CleaningCompanyController');
Route::get('user_count', 'UserController@getUserCount');
Route::put('change_password', 'UserController@changePassword');
Route::get('main_page_variables','UserController@getMainPageVariables');
Route::post('complaint','GroupController@getComplaintGroup');
Route::post('billing','GroupController@getBillingGroup');
Route::post('forecast','GroupController@forecast');
Route::get('complaint_candidate','GroupController@getComplainCandidate');
Route::get('billing_candidate','GroupController@getBillingCandidate');
Route::get('my_cleaner','CustomerController@getCleanerByCustomerId');
Route::get('qiniu_token','ImageController@getToken');