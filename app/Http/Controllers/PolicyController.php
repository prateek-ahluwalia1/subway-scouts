<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePolicyRequest;
use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function store(StorePolicyRequest $request)
    {
        $policy = new Policy();
        $policy->type = $request->type;
        $policy->title = $request->title;
        $policy->description = $request->description;
        $policy->save();
        return response()->json(['message' => "Policy Create Successfully" ,  'code' => '200', 'success' => 'true'],200);
    }

    public function update(StorePolicyRequest $request)
    {
        $policy = Policy::where('id', $request->id)->first();
        $policy->type = $request->type;
        $policy->title = $request->title;
        $policy->description = $request->description;
        $policy->save();
        return response()->json(['message' => "Policy Update  Successfully" ,  'code' => '200', 'success' => 'true'],200);
    }

    public function deletePolicy(Request $request)
    {
        $policy = Policy::where('id', $request->id)->delete();
        return response()->json(['message' => "Policy Delete Successfully" ,  'code' => '200', 'success' => 'true'],200);
    }
}
