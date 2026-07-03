<?php

namespace App\Http\Controllers;

use App\Http\Resources\CurrentWeekUnCoverdShiftResource;
use App\Http\Resources\MarkAsReadResource;
use App\Models\JobRoster;
use App\Models\OperationNotes;
use App\Models\ToDo;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ToDoController extends Controller
{
    public function index(){}
    // public function (){}
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $todos = ToDo::create($request->all());
        return response()->json([
            'success' => true,
            'message' => "New Journal posted",
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $start = Carbon::now()->startOfWeek()->toDateString(); 
        $start = date('Y-m-d 00:00', strtotime($start));

        $end = Carbon::now()->endOfWeek()->toDateString();
        $end = date('Y-m-d 23:59', strtotime($end));
        //uncoverd shift
         $shifts = JobRoster::where('start', '>=', $start)
        ->where('start', '<=', $end)->where(function($q){
            $q->whereNull('guard_id');
            $q->orWhere('guard_id', '=', '');
            $q->where('shift_type', '!=', 'template');
        })->with('site')->get();
        $sh = CurrentWeekUnCoverdShiftResource::collection($shifts);
        $notes = OperationNotes::whereJsonContains('send_to', (int)$id)->select('id', 'title', 'note', 'created_by', 'mark_as_read', DB::raw("'".$id."' AS admin_id"))->get();
        $mark = MarkAsReadResource::collection($notes);
        return response()->json([
            'success' => true,
            'data' => Todo::where('user_id', $id)->with('User')->get(),
            'mark' => $mark,
            'unconverd_shifts' => $sh
        ], 200); 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $todos = ToDo::find($id);
        if($todos){
            $request->merge(['status' => 'new']);
            ToDo::where('id', $id)->update($request->all());
            return response()->json([
                'success' => true,
                'message' => "Journal Updated",
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'message' => "Journal Not Found",
            ], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $todo = ToDo::find($id);
        if($todo){
            $todo->delete();
            return response()->json(['status'=>true,'msg'=>'Post Deleted'],201);
        }else{
            return response()->json(['status'=>false],403);
        }
    }
    public function changeStatus($id){
        $todo = ToDo::find($id);
        if($todo){
            $todo->status = $todo->status == 'completed' ? 'new' : 'completed';
            $todo->update();
            return response()->json(['status'=>true,'msg'=>'Status Changed Successfully'],200);
        }else{
            return response()->json(['status'=>false],403);
        }
    }
}
