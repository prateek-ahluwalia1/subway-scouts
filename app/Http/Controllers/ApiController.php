<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected $response = [];
    protected $statusCode;
    protected $request;
    protected $validator;
    protected $rules;
    public $currentUser;
    const STATUS_CODE_200 = 200;
    const STATUS_CODE_201 = 201;
    const STATUS_CODE_400 = 400;
    const STATUS_CODE_401 = 401;
    const STATUS_CODE_404 = 404;

    public function __construct(Request $request) {
        $this->request = $request;
        $this->currentUser =  $this->request->get('user');
    }

    protected function isValidRequest () {
        $this->validator = $this->getValidationFactory()->make($this->request->all(), $this->rules);
        return $this->validator->fails();
    }

    protected function getErrors () {
        return $this->validator->errors()->all();
    }

    protected function setValidationRules($rules) {
        $this->rules = $rules;
    }

    protected function sendResponse() {
        return response()->json($this->response,$this->statusCode);
    }
}
