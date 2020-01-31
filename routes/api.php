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

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    Route::post('checkSession', 'AuthController@checkSession');
    Route::post('logout', 'AuthController@deleteSession');
    Route::post('updateSession', 'AuthController@updateSession');
    Route::post('getSession', 'AuthController@getSession');

});

Route::group(['prefix' => 'home'], function ($router) {
    Route::post('getExams', 'HomeController@getExams');
    Route::post('getEducations', 'HomeController@getEducations');
    Route::post('getStudents', 'HomeController@getStudents');
    Route::post('checkexam', 'HomeController@checkExam');
    Route::post('makeexam', 'HomeController@startExam');
    Route::post('delInstance', 'HomeController@delInstance');
    Route::post('getStartedInstance', 'HomeController@getStartedInstance');
});

Route::group(['prefix' => 'students'], function ($router) {
    Route::post('getStudents', 'StudentController@index');
    Route::post('getexams', 'StudentController@getExams');
    Route::post('geteducations', 'StudentController@getEducations');
    Route::post('saveStudent', 'StudentController@saveStudent');
    Route::post('viewTakenExam', 'StudentController@viewTakenExam');
    Route::get('getResult/{id}', 'StudentController@getResult');
    Route::get('getExamName/{id}', 'StudentController@getExamName');
    Route::post('deletePlannedExamById', 'StudentController@deletePlannedExamById');
    Route::post('createPlannedExam', 'StudentController@createPlannedExam');
    Route::post('savePlannedExamById', 'StudentController@savePlannedExamById');
});

Route::group(['prefix' => 'examsheet'], function ($router) {
    Route::post('checkcombine', 'ExamSheetController@checkCombine');
    Route::get('getexam/{id}', 'ExamSheetController@getExamById');
    Route::post('saveanswers', 'ExamSheetController@saveAnswers');
    Route::post('submitanswers', 'ExamSheetController@saveAnswers');
    Route::post('saveresults', 'ExamSheetController@saveResults');
    Route::post('getinstance', 'ExamSheetController@getInstance');
    Route::post('endexam', 'ExamSheetController@endExam');
});

Route::group(['prefix' => 'admin'], function ($router) {
    Route::get('getexamstates', 'ExamCreatorController@getExamStates');
    Route::post('geteducations', 'ExamCreatorController@getEducations');
    Route::post('getStudents', 'StudentController@index');
    Route::post('getExams', 'AdminController@getExams');
    Route::post('save', 'ExamCreatorController@save');
    Route::post('newEducation', 'AdminController@newEducation');
    Route::post('getEducations', 'AdminController@getEducations');
    Route::post('getExamById', 'ExamCreatorController@getExamById');
    Route::post('deleteExamById', 'AdminController@deleteExamById');
    Route::post('copyExamById', 'AdminController@copyExamById');
    Route::post('deleteStudentById', 'AdminController@deleteStudentById');
    Route::post('editStudentById', 'AdminController@editStudentById');
    Route::post('deleteEducationById', 'AdminController@deleteEducationById');
    Route::post('editEducation', 'AdminController@editEducation');
    Route::post('upload', 'StudentController@upload');
    Route::post('getPlannedExamsOfStudent', 'StudentController@getPlannedExamsOfStudent');
    //invitation api
    Route::post('inviteUser', 'InvitationController@store');
    Route::get('getInvitations', 'InvitationController@index');
    Route::post('deleteInvitationById', 'InvitationController@deleteInvitationById');
});

Route::group(['prefix' => 'superAdmin'], function ($router) {
    Route::post('getEducations', 'EducationController@getEducations');
    Route::post('deleteEducationById', 'EducationController@deleteEducationById');
    Route::post('newEducation', 'EducationController@newEducation');
    Route::post('saveEducation', 'EducationController@saveEducation');
    Route::post('getUsers', 'AdminController@getUsers');
    Route::post('saveAdmin', 'AdminController@saveAdmin');
    Route::post('deleteAdminById', 'AdminController@deleteAdminById');
    Route::post('getInvitations', 'InvitationController@getInvitations');
    Route::post('deleteInvitationById', 'InvitationController@deleteInvitationById');
    Route::post('newInvitation', 'InvitationController@newInvitation');
    Route::post('saveInvitation', 'InvitationController@saveInvitation');
    Route::post('getRoles', 'InvitationController@getRoles');
    Route::post('sendEmail', 'InvitationController@sendEmail');
});

Route::group(['prefix' => 'ceo'], function ($router) {
    Route::post('getDomains', 'AdminController@getDomains');
    Route::post('newDomain', 'AdminController@newDomain');
    Route::post('deleteDomainById', 'AdminController@deleteDomainById');
    Route::post('saveDomain', 'AdminController@saveDomain');
});

Route::group(['prefix' => 'examcombine'], function ($router) {
    Route::get('getinstances/{id}', 'CombineController@getInstances');
    Route::post('createresult', 'CombineController@createResult');
});

Route::group(['prefix' => 'log'], function($router){
    Route::post('getLog', 'LogController@getLog');
    Route::post('createLog', 'LogController@createLog');
});
