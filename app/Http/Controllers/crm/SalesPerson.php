<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\crm\SalePersonModel as Persons;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalesPerson extends Controller
{
    function getSalesPersonList()
    {
        $persons = Persons::get();
        if (count($persons) > 0) {
            return response()->json(['success' => true, 'message' => 'List found.', 'data' => $persons]);
        }else{
            return response()->json(['success' => false, 'message' => 'No list found!', 'data' => $persons]);
        }
    }

    function getSalesPersonDetails($id)
    {
        $persons = Persons::where('id', $id)->first();
        if (!empty($persons)) {
            return response()->json(['success' => true, 'message' => 'Details found.', 'data' => $persons]);
        }else{
            return response()->json(['success' => false, 'message' => 'No detail found!', 'data' => $persons]);
        }
    }

    function addSalesPerson(Request $request)
    {
        $person = new Persons();
        $person->name = $request->name;
        $person->email = $request->email;
        $person->phone = $request->phone;
        $person->password = $request->has('password') ? Hash::make($request->password) : Hash::make('Temp123456');
        $person->save();
        if ($person) {
            return response()->json(['success' => true, 'message' => 'Sales Person added successfully']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add sale person!']);
        }
    }
    function updateSalesPerson(Request $request)
    {
        $person = Persons::where('id', $request->id)->first();
        $person->name = $request->name;
        $person->email = $request->email;
        $person->phone = $request->phone;
        if ($request->has('password') && $request->password != '') {
        $person->password = Hash::make($request->password);
        }
        $person->save();
        if ($person) {
            return response()->json(['success' => true, 'message' => 'Sales Person update successfully']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to update sale person!']);
        }
    }

    function deleteSalesPerson(Request $request, $id)
    {
        $person = Persons::where('id', $id)->delete();
        if ($person) {
            return response()->json(['success' => true, 'message' => 'Sales Person deleted successfully']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to delete sale person!']);
        }
    }


    
}
