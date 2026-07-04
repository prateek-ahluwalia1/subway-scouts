<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CustomerController extends ApiController
{

    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function requests_list(Request $request, $id)
    {
    	$customer_requests = DB::table('customer_requests')->where(['customer_id' => $id])->select('customer_requests.*')->get();

    	$data = array();
    	foreach ($customer_requests as $req) {
    		$data[] = array(
    			'id' => $req->id,
    			'guards' => $req->guards,
    			'required_date' => date('d-m-Y',$req->required_date),
    			'required_time' => date('H:i',$req->required_date),
    			'request_date' => $req->request_date, 
                'notes' => $req->notes,
                'site_name' => $req->site_name,
                'site_address' => $req->site_address,
                'contact_no' => $req->contact_no,
                'time' => $req->time,
    			'notes' => $req->notes,
                'date_start' => date('d-m-Y',$req->date_start),
                'date_end' => date('d-m-Y',$req->date_end)

    		);
    	}
    	$this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Retrieved list',
                'list' => $data
            ];
            return $this->sendResponse();
    }

    public function add_request(Request $request, $id)
    {
    	$this->setValidationRules(['guards' => 'required', 'date' => 'required']);
        if ($this->isValidRequest()) {
            $this->response = ['success' => false, 'error' => $this->getErrors()];
            $this->statusCode = self::STATUS_CODE_200;
            return $this->sendResponse();
        }
        $date = $request->input('date');
        $date_start = $request->input('date_start');
        $date_end = $request->input('date_end');

        $data = array(
            'customer_id' => $id,
            'guards' => $request->input('guards'),
            'required_date' => strtotime($date),
            'status' => 'active',
            'notes' => $request->input('notes'),
            'site_name' => $request->input('site_name'),
            'site_address' => $request->input('site_address'),
            'contact_no' => $request->input('contact_no'),
            'time' => $request->input('time'),
            'date_start' => strtotime($date_start),
            'date_end' => strtotime($date_end)
        );

            $result = DB::table('customer_requests')->insert($data);
            if($result){
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Successfully submited your request.',
            ];
        }else{
            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => false,
                'message' => 'Fail to submit your request!',
            ];
        }
            return $this->sendResponse();
    }

}