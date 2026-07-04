<?php
namespace App\Http\Controllers\Api;

use App\Http\Resources\Notification\NotificationResource;
use App\Http\Resources\Notification\NotificationCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;


class NotificationController extends ApiController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
	public $notification;
    public function __construct(Request $request, Notification $notification) {
        parent::__construct($request);
		$this->notification = $notification;
	}
	
	public function get_notifications(Request $request)
	{
		$req = array();
		$auth_token = $request->input('authToken');
		$req['id'] = $this->auth_model->verify_token($auth_token);

		if($req['id']){
			$status = 'OK';
			$code = 200;
			$message = 'Data received';	
			$data = new Notification();
			$data = $data->get_notifications();
		}
		else{
			$status = 'ERROR';
			$code = 403;
			$message = 'Unauthorized access';
			$data = null;
		}

		$rsp = array(
			'status' => $status,
			'code' => $code,
			'message' => $message,
			'data' => $data
			);

		echo json_encode($rsp);
		
	}

	public function add_notification()
	{

		$req = array();
		$auth_token = trim($this->security->xss_clean($this->input->post('authToken')));
		$req['id'] = $this->auth_model->verify_token($auth_token);

		$req['title'] = trim($this->security->xss_clean($this->input->post('title')));
		$req['message'] = trim($this->security->xss_clean($this->input->post('message')));
		$req['via'] = trim($this->security->xss_clean($this->input->post('via')));
		$req['to'] = trim($this->security->xss_clean($this->input->post('to')));

		if($req['id']){
			$status = 'OK';
			$code = 200;
			$message = 'Data received';	
			$data = $this->notification_model->add_notification($req);
		}
		else{
			$status = 'ERROR';
			$code = 403;
			$message = 'Unauthorized access';
			$data = null;
		}

		$rsp = array(
			'status' => $status,
			'code' => $code,
			'message' => $message,
			'data' => $data
			);

		echo json_encode($rsp);
		
	}	


	public function test_notification()
	{

		
		$test_notification = new Notification(); 
		$test_notification->new_app_send_push_notification(array('page' => 'green-call', 'title' => 'Test notification title', 'message' => 'This is test notification message', 'fcm_token' => '16d18e77-02cd-4b64-accf-516c6695f03b'));
		$test_notification->new_app_send_push_notification(array('page' => 'welfare-call', 'title' => 'Test notification title', 'message' => 'This is test notification message', 'fcm_token' => '16d18e77-02cd-4b64-accf-516c6695f03b'));
		$job_data = array(
			'roster_id' => '111',
			'job_id' => '16',
			'address' => 'DHA Phase 1',
			'coordinates' => '0,0',
			'start' => '',
			'end' => '',
			'booking_id' => '',
			'temp_start' => '',
			'temp_end' => '',
			'job_start_month' => '',
			'job_start_day' => '',
			'job_start_date' => '',
			'Instructions' => ''
		  );
		  $test_notification->new_app_send_push_notification(array('page' => 'asap-job-list', 'title' => 'ASAP Job', 'message' => 'This is test notification message', 'fcm_token' => '16d18e77-02cd-4b64-accf-516c6695f03b', 'job_data' => $job_data));	
	}	

	public function send_green_call_notifications()
	{
		$result = $this->notification->send_green_call_notifications();
		if ($result) {
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Reminder Notification send successfully.'
            ];
            return $this->sendResponse();
        }
		$this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Unauthorized access'
        ];
        return $this->sendResponse();
		// if($result)
		// {
		// 	$status = 'OK';
		// 	$code = 200;
		// 	$message = 'Green call Notification send successfully.';	
		// }
		// else{
		// 	$status = 'ERROR';
		// 	$code = 403;
		// 	$message = 'Unauthorized access';
		// }

		// $rsp = array(
		// 	'status' => $status,
		// 	'code' => $code,
		// 	'message' => $message,
		// 	);

		// echo json_encode($rsp);
	}

	public function send_welfare_notifications()
	{
		$result = $this->notification->send_welfare_notifications();
		if ($result) {
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Welfare Notification send successfully.'
            ];
            return $this->sendResponse();
        }
		$this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Unauthorized access'
        ];
        return $this->sendResponse();
	}

	public function notifications_list(Request $request , $id)
	{

		$notifications = $this->notification->notifications_list($id);
		return new NotificationCollection(NotificationResource::collection($notifications));
	}
	public function notification_data(Request $request , $id)
	{
		$notifications = $this->notification->notifications_data($request->notification_id, $id);
		$this->statusCode = self::STATUS_CODE_200;
		if ($notifications == false) {
			$this->response = [
            'success' => false,
            'message' => 'There is no data against this notification!'
        ];
		}else{
        $this->response = [  
            'success' => true,
            'message' => $notifications
        ];
    	}
        return $this->sendResponse();
	}
	
	public function find_guard()
	{
		$notifications = $this->notification->find_guard();
		if ($notifications) {
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'ASAP Notification send successfully.',
                'job' => $notifications
            ];
            return $this->sendResponse();
        }
		$this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Unauthorized access'
        ];
        return $this->sendResponse();
	}
	public function profile_incomplete_notification()
	{
		$notifications = $this->notification->profile_incomplete_notification();
		if ($notifications) {
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Incomplete profile notification send successfully.'
            ];
            return $this->sendResponse();
        }
		$this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Unauthorized access'
        ];
        return $this->sendResponse();
	}

	public function find_guard_asap()
	{
		$notifications = $this->notification->find_guard_asap();
		if ($notifications) {
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'ASAP Notification send successfully.',
                'job' => $notifications
            ];
            return $this->sendResponse();
        }
		$this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => false,
            'message' => 'Unauthorized access'
        ];
        return $this->sendResponse();
	}

}
