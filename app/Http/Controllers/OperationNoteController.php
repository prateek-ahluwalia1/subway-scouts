<?php

namespace App\Http\Controllers;

use App\Http\Resources\GetAllOperationNotes;
use App\Http\Resources\ShowNoteResource;
use App\Models\OperationNotes;
use App\Models\User;
use App\Models\JobRoster;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OperationNoteController extends Controller
{
  
    public function getAllNotes(Request $request)
    {
        $superadmin = User::where('id', $request->admin_id)->first();
        if($superadmin->userType == 'super-admin' && $superadmin->is_super_admin == 1 && $superadmin->is_received_support == 1 && $superadmin->status == 'active'){
            $inbox = OperationNotes::latest()->select('id', 'title', 'note', 'mark_as_read', 'updated_at', 'created_at', DB::raw("'".$request->admin_id."' AS admin_id"))->get();
            if($inbox){
                $ib = GetAllOperationNotes::collection($inbox);
            }
            $send_msg = OperationNotes::latest()->select('id', 'title', 'note', 'mark_as_read', 'updated_at', 'created_at', DB::raw("'".$request->admin_id."' AS admin_id"))->get();
            if($send_msg){
                $s_m = GetAllOperationNotes::collection($send_msg);
            }
            return response()->json(['success' => true, 'inbox' => $ib, 'send_msg' => $s_m]);
        }else{
            $inbox = OperationNotes::whereJsonContains('send_to', $request->admin_id)->select('id', 'title', 'note', 'mark_as_read', 'updated_at', 'created_at', DB::raw("'".$request->admin_id."' AS admin_id"))->latest()->get();
            if($inbox){
                $ib = GetAllOperationNotes::collection($inbox);
            }
            $send_msg = OperationNotes::where('created_by', $request->admin_id)->select('id', 'title', 'note', 'mark_as_read', 'updated_at', 'created_at', DB::raw("'".$request->admin_id."' AS admin_id"))->latest()->get();
            if($send_msg){
                $s_m = GetAllOperationNotes::collection($send_msg);
            }
            return response()->json(['success' => true, 'inbox' => $ib, 'send_msg' => $s_m]);
        }
       
    }

    public function showNotes(Request $request)
    {
        $note = OperationNotes::where('id', $request->id)->first();
        $note->user_id = $request->user_id;
        if($note){
            $n =  new ShowNoteResource($note);
            return response()->json(['success' => true, 'data' => $n]);
        }else{
            return response()->json(['success' => false, 'data' => '']);
        }
    }
    public function store(Request $request)
    {
        $operation_notes = new OperationNotes();
        $operation_notes->send_to = json_encode($request->send_to);
        $operation_notes->title = $request->title;
        $operation_notes->created_by = $request->admin_id;
        $operation_notes->note = $request->note;
        $operation_notes->save();
        jobRosterActions($request->admin_id, 'add_note', $operation_notes->id, 'operation_notes');
        return response()->json(['success' => true, 'msg' => 'Operation note created!']);
    }


    public function markAsRead(Request $request)
    {
        $markAsRead = OperationNotes::whereIn('id', $request->id)->get();
        if ($markAsRead->isNotEmpty()) {
            foreach ($markAsRead as $key => $value) {
                $markAsReadArray = json_decode($value->mark_as_read);
                $markAsReadArray[] = $request->admin_id;
                $value->mark_as_read = json_encode($markAsReadArray);
                $value->update();    
            }
            return response()->json(['success' => true, 'msg' => 'Operation note Read!']);
        } else {
            return response()->json(['success' => false, 'msg' => 'No records found']);
        }     
    }   

    public function deleteOperationNotes(Request $request)
    {
        $deleted = JobRoster::where('id', $request->id)->update(['operation_notes' => NULL]);

        if ($deleted) {
            return response()->json(['success' => true, 'msg' => 'Operation note(s) deleted successfully!']);
        } else {
            return response()->json(['success' => false, 'msg' => 'No operation notes found to delete.']);
        }
    }

}
