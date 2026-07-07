<?php


/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/





$router->group(['prefix' => 'api/v1/'], function () use ($router) {

    //subway app apis
    $router->post('get-today-shifts', 'Api\JobController@getTodayShifts');
    $router->get('get-branches', 'Api\JobController@getBranches');

    $router->post('/signin/{id}', 'Api\JobController@jobSignin');
    $router->post('/signout/{id}', 'Api\JobController@jobSignout');



    $router->post('/guest_login', 'Api\JobController@guestSignin');
    $router->post('/guest_logout/{id}', 'Api\JobController@guestSignout');
    $router->get('/guest_emplyees', 'Api\JobController@guestEmplyess');

    $router->post('/login', 'Api\Auth\UserController@login');
    $router->post('/forgot_password', 'Api\Auth\UserController@forgot_password');
    $router->post('/register', 'Api\Auth\UserController@register');
    $router->post('/register', 'Api\Auth\UserController@register');

    $router->get('/send_green_call_notifications', 'Api\NotificationController@send_green_call_notifications');
    $router->get('/send_welfare_notifications', 'Api\NotificationController@send_welfare_notifications');
    $router->get('/find_guard', 'Api\NotificationController@find_guard');
    
    $router->group(['middleware' => 'check.db'], function () use ($router){
        
    $router->post('/test_notification', 'Api\NotificationController@test_notification');
    $router->get('/profile_incomplete_notification', 'Api\NotificationController@profile_incomplete_notification');
    $router->post('/send_green_call_notifications', 'Api\NotificationController@send_green_call_notifications');
    $router->get('/find_guard_asap', 'Api\NotificationController@find_guard_asap');
    $router->get('/auto_sign_out', 'Api\JobController@auto_sign_out');
    $router->post('/policies/{type}', 'Api\GeneralController@get_policies');
    $router->post('/update-announcement-read-status', 'Api\GeneralController@updateReadStatus');
    $router->post('/demo_businesses_list', 'Api\GeneralController@get_demo_businesses');
    $router->post('BusinessData','Api\GeneralController@BusinessData');
    $router->get('get_chat_user_list', 'Api\GeneralController@get_chat_user_list');
    $router->post('/upload_image', 'Api\GeneralController@upload_images');
    $router->post('/upload_pdf', 'Api\GeneralController@uploadPdf');
    $router->get('get-current-time/{guard_id}', 'Api\GeneralController@getCurrentTime');

    $router->group(['middleware' => 'auth'], function () use ($router){
        $router->post('/logout', 'Api\Auth\UserController@logout');
        $router->post('/deleteUser', 'Api\Auth\UserController@deleteUser');
        $router->post('/update_profile/{id}', 'Api\Auth\UserController@update_profile');
        $router->post('/update_security_license/{id}', 'Api\Auth\UserController@update_security_license');
        $router->post('/get_profile_info/{id}', 'Api\Auth\UserController@get_profile_info');
        $router->post('/update_notification_token/{id}', 'Api\Auth\UserController@update_notification_token');
        $router->post('/notifications_list/{id}', 'Api\NotificationController@notifications_list');
        $router->post('/notification_data/{id}', 'Api\NotificationController@notification_data');
        $router->post('/admin_list', 'Api\JobController@get_admin_list');
        $router->post('/verifyGuardDocument', 'Api\Auth\UserController@verifyGuardDocument');
        $router->post('/getGuardPaysheet/{id}', 'Api\Auth\UserController@getGuardPaysheet');
        $router->post('/submit-guard-questionnaire', 'Api\GeneralController@submitQNA');
        $router->get('/get-questionnaire/{guard_id}', 'Api\GeneralController@getQNA');

        $router->post('/about-us', 'Api\Auth\UserController@aboutUs'); // added by faizan..

        $router->group(['prefix' => 'job'], function () use ($router) {
            $router->post('scan-QRCode/{id}', 'Api\JobController@scanQR');

            $router->post('/guard_sos_call/{id}', 'Api\JobController@guard_sos_call');
            $router->post('/guard/jobs/{type}/{duration}', 'Api\JobController@getGuardJobs');
            $router->post('/asap/jobs', 'Api\JobController@getAsapJobs');
            $router->post('/other/jobs/{type}/{duration}/{id}', 'Api\JobController@getJobs');
            $router->post('/{id}', 'Api\JobController@jobDetail'); 
            $router->post('/jobDetails/{id}', 'Api\JobController@jobSpecificDetail');
            $router->put('/confirm/{id}', 'Api\JobController@confirmJob');
            $router->put('/reject/{id}', 'Api\JobController@rejectJob');
            $router->post('/signin/{id}', 'Api\JobController@jobSignin'); //
            $router->post('/guard_location_at_job/{id}', 'Api\JobController@saveGuardLocation');
            $router->post('/signout/{id}', 'Api\JobController@jobSignout'); //
            $router->post('/report_incident_old/{id}', 'Api\JobController@report_incident');
            $router->post('/report_new_incident/{id}', 'Api\JobController@report_new_incident');
            $router->post('/foot-patrol-report/{id}', 'Api\JobController@footPatrolReport');
            $router->post('/report_incident/{id}', 'Api\JobController@report_incident_new');
            $router->post('/incident_report/{id}', 'Api\JobController@report_incident_new'); //using
            $router->post('/leave_request/{id}', 'Api\JobController@leave_request');
            $router->post('/update_leave_request/{action}/{id}', 'Api\JobController@update_leave_request');
            $router->post('/get_report_incidents/{id}', 'Api\JobController@get_incident_reports');
            $router->post('/get_report_foot_patrol/{id}', 'Api\JobController@get_foot_patrol_reports');
            $router->post('/get_leave_requests/{id}', 'Api\JobController@get_leave_requests');
            $router->post('/green_call_coordinates/{id}', 'Api\JobController@green_call_coordinates');
            $router->post('/welfare_call/{id}', 'Api\JobController@welfare_call');
            $router->post('/asap_jobs/accept/{id}', 'Api\JobController@accept_asap_job');
            $router->post('/asap_jobs/reject/{id}', 'Api\JobController@reject_asap_job');
            $router->post('/confirm_task/{id}', 'Api\JobController@confirm_task');
            $router->post('/start_task/{id}', 'Api\JobController@start_task');
            $router->post('/end_task/{id}', 'Api\JobController@end_task');
            $router->post('/break/{id}', 'Api\JobController@start_break');
            $router->post('/end_break/{id}', 'Api\JobController@end_break');
            $router->post('/check_welfare_call/{id}', 'Api\JobController@check_welfare_call');
            
        });

        $router->group(['prefix' => 'customer'], function () use ($router) {
            $router->post('/requests_list/{id}', 'Api\CustomerController@requests_list');
            $router->post('/add_request/{id}', 'Api\CustomerController@add_request');

        });
        
        $router->group(['prefix' => 'general'], function () use ($router) {
            $router->post('/list_tutorials', 'Api\GeneralController@list_tutorials');
            $router->post('/list_inductions', 'Api\GeneralController@list_inductions');
            $router->post('/list_about_us', 'Api\GeneralController@list_about_us');
            $router->post('/uplaod_tutorial_image/{id}', 'Api\GeneralController@uplaod_tutorial_image');
            $router->post('/read_induction_status/{id}', 'Api\GeneralController@read_induction_status');
            $router->post('/uplaod_induction_image/{id}', 'Api\GeneralController@uplaod_induction_image');
            $router->post('/get_guard_payslips', 'Api\GeneralController@getGuardPayslips');
        });


        $router->group(['prefix' => 'inbox'], function () use ($router) {
            $router->post('/get_messages/{id}', 'Api\InboxController@get_messages');
            $router->post('/send_message/{id}', 'Api\InboxController@send_message'); 
        });

    });
});
# STATIC API FOR CHECK ONLY VERSION FOR MOBILE APP
$router->get('/app-version', 'Api\GeneralController@appVersion'); 

 });




/*$router->group(['prefix' => 'api/v1/'], function ($app) {
    $router->get('/login', function () use ($router) {
        return $router->app->version();
    });

});*/
