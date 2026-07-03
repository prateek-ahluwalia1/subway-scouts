<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Http\Resources\getAllScrumboardsResource;
use App\Http\Resources\getScrumboardResource;
use App\Models\Scrumboard;
use App\Models\Stage;
use Illuminate\Http\Request;

class ScrumboardController extends Controller
{
public function addAndUpdateScrumboard(Request $request)
{
    $is_chk = 0;
    $scrumboard = Scrumboard::where('id', $request->id)->first();

    if (empty($scrumboard)) {
        $scrumboard = new Scrumboard();
        $is_chk = 1;
    }
    $scrumboard->title = $request->title;
    $scrumboard->description = $request->description;
    $scrumboard->tags = json_encode($request->tags);
    $scrumboard->save();
    if ($is_chk == 1) {
        $defultBoards = ['Contact In Future', 'Lost Lead', 'Won', 'Pending'];
        foreach($defultBoards as $defultBoard){
            Stage::insert(['scrumboard_id' => $scrumboard->id, 'title' => $defultBoard, 'created_at' => date('Y-m-d H:i:s')]);
        }
        jobRosterActions($request->admin_id, 'add_title', $scrumboard->id, 'scrumboards');
        return response()->json(['success' => true, 'message' => 'Scrumboard added successfully']);
    } else {
        jobRosterActions($request->admin_id, 'update_title', $scrumboard->id, 'scrumboards');
        return response()->json(['success' => true, 'message' => 'Scrumboard updated successfully']);
    }
 } 

 function getAllScrumboard()  {
    $scrumboards = Scrumboard::select('id', 'title', 'description', 'tags', 'updated_at')->get();
    $sm  = getAllScrumboardsResource::collection($scrumboards);
    return response()->json(['success' => true, 'data' => $sm]);       
 }

    public function editScrumboard(Request $request)  {

        $scrumboard =  Scrumboard::where('id', $request->id)->select('id','title', 'description', 'tags', 'updated_at')->first();
        if($scrumboard){
              $sm  = new getScrumboardResource($scrumboard);
            return response()->json(['success' => true, 'data' => $scrumboard]);          
        }else{
            return response()->json(['success' => false, 'message' => 'Scrumboard no found!']);        
        } 
    }

   public function deleteScrumboard(Request $request) {
    $scrumboard =  Scrumboard::where('id', $request->id)->select('id','title')->first();
    if($scrumboard){
        $scrumboard->delete();
        jobRosterActions($request->admin_id, 'delete_scrumboard', $scrumboard->id, 'scrumboards');
        return response()->json(['success' => true, 'message' => 'Scrumboard delete successfully']);
    }else{
        return response()->json(['success' => true, 'message' => 'Scrumboard not found successfully']);
    }
   }

   function getScrumboardDetail(Request $request)
   {
        $scrumboard = Scrumboard::with([
            'lists' => function($que) {
                $que->with([
                    'cards' => function($query) {
                        $query->with(['customer', 'salesperson', 'admin'])
                              ->withCount('comments');
                    }
                ]);  
            }
        ])->where('id', $request->boardId)->first();

        if (!empty($scrumboard)) {
            return response()->json(['success' => true, 'message' => 'Scrumboard found successfully.', 'data' => $scrumboard]);
        }else{
            return response()->json(['success' => true, 'message' => 'Scrumboard not found!', 'data' => $scrumboard]);
        }
   }
}
