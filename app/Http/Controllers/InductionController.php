<?php

namespace App\Http\Controllers;

use App\Http\Resources\GetAllInductionResource;
use App\Models\Induction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InductionController extends Controller
{
    // code  provided by usman bhatti..
    function addInduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'induction'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            if ($request->induction_id == null) {
                $ann = new  Induction();
                $ann->send_by = $request->admin_id;
                $ann->send_to = json_encode(array());
                $ann->title = $request->title;
                $ann->html_body = $request->induction;
                $ann->save();
                return response()->json([
                    'status' => true,
                    'success' => 'Induction Created',
                ]);
            } else {
                DB::table('inductions')
                    ->where('id', $request->induction_id)
                    ->update(['title' => $request->title, 'html_body' => $request->induction]);
                return response()->json([
                    'status' => true,
                    'success' => 'Induction Updated',
                ], 201);
            }
        }
    }


    function editInduction(Request $request)
    {
        $editInduction = Induction::where('id', '=', $request->induction_id)->get();
        return response()->json(['data' => $editInduction ,  'code' => 200, 'success' => true]);
    }

    public function getAllInduction()
    {
        $getAll = Induction::all();
        $gA = GetAllInductionResource::collection($getAll);
        return response()->json(['data' => $gA ,  'code' => 200, 'success' => true]);
    }

    public function deleteInduction(Request $request)
    {
        $deleteInduction = Induction::where('id', $request->id)->first();
        if($deleteInduction){
            $deleteInduction->delete();
            return response()->json(['msg' => 'Induction Record Deleted' ,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['msg' => 'Record Not Found!' ,  'code' => 404, 'success' => false]);
        }
    }
}
