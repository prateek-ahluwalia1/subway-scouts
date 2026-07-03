<?php

namespace App\Http\Controllers\crm;

use App\Http\Controllers\Controller;
use App\Models\crm\CardComments;
use App\Models\Stage;
use App\Models\crm\CardStages;
use App\Models\CustomerComment;
use Illuminate\Http\Request;
use DB;
use GuzzleHttp\Promise\Create;

class StageController extends Controller
{

    function addUpdateStage(Request $request)
    {
        $added = false;
        $list = $request->list;
            if ($list['id'] != null && $list['id'] != '') {
                $added = Stage::where('id', $list['id'])->update(['title' => $list['title'], 'updated_at' => date('Y-m-d H:i:s')]);
                jobRosterActions($request->admin_id, 'update_stage', $list['id'], 'stages');
            }else{
                $added = Stage::insert(['scrumboard_id' => $list['boardId'], 'title' => $list['title'], 'created_at' => date('Y-m-d H:i:s')]);
                $id = DB::getPdo()->lastInsertId();
                jobRosterActions($request->admin_id, 'add_stage', $id , 'stages');
            }
        if ($added) {
            return response()->json(['success' => true, 'message' => 'Scrumboard stage added/updated successfully.']);
            
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add/update scrumboard!']);
        }
    }

    function getStages(Request $request)
    {
        $stages = Stage::where('scrumboard_id',  $request->boardId)->orderBy('created_at', 'asc')->select('id', 'scrumboard_id as boardId', 'title', 'created_at')->get();
        if (count($stages) > 0) {
            return response()->json(['success' => true, 'message' => 'Stages Found.', 'data' => $stages]);
        }else{
            return response()->json(['success' => false, 'message' => 'No stage found!', 'data' => $stages]);
        }
    }

    function deleteStage(Request $request)
    {
        $stage = Stage::where('id',  $request->id)->delete();
        if ($stage) {
            jobRosterActions($request->admin_id, 'add_stage', $stage->id, 'stages');
            return response()->json(['success' => true, 'message' => 'Stages deleted successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to delete stage!']);
        }
    }

    function addAndUpdateCard(Request $request)
    {
        $inserted = CardStages::insert([
            'stage_id' => $request->card['listId'],
            'customer_id' => $request->card['customer_id'],
            'admin_id' => $request->card['admin_id'],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $id = DB::getPdo()->lastInsertId();
        if ($inserted) {
            jobRosterActions($request->card['admin_id'], 'add_stage', $id, 'stages');
            return response()->json(['success' => true, 'message' => 'Stages added successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add card!']);
        }
    }

    public function updateCardPosition(Request $request)
    {
        $updated = 0;
        foreach ($request->cards as $key => $c) {
            CardStages::where('id', $c['id'])->update(['stage_id' => $c['listId']]);
            $updated++;
            jobRosterActions($request->admin_id, 'add_stage', $c['id'], 'stage_cards');
        }
        if ($updated > 0) {
            return response()->json(['success' => true, 'message' => 'Card update successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to update card!']);
        }
    }

public function updateCardSalePerson(Request $request)
    {
        if($request->has('comment') && $request->comment != '')
        {
            return $this->addComment($request);
        }else{
        if ($request->has('saleperson_id') && $request->saleperson_id != '') {
            $data['saleperson_id'] = $request->saleperson_id;
        }
        if ($request->has('admin_id') && $request->admin_id != '') {
            $data['admin_id'] = $request->admin_id;
        }
        if ($request->has('description') && $request->description != '') {
            $data['description'] = $request->description;
        }
        $updated = CardStages::where('id', $request->id)->update($data);
        if ($updated > 0) {
            jobRosterActions($request->admin_id, 'update_stage_card', $request->id, 'stage_cards');
            return response()->json(['success' => true, 'message' => 'Card update successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to update card!']);
        }
        }
    }

public function addComment(Request $request)
{
    $inserted = DB::table('card_comments')->insert([
        'card_id' => $request->id,
        'comment' => $request->comment,
        'admin_id' => $request->admin_id,
        'created_at' => date('Y-m-d H:i:s')]);

    if ($inserted) {

            jobRosterActions($request->admin_id, 'add_card_comments', $request->id, 'card_comments');

            return response()->json(['success' => true, 'message' => 'Comment added successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add comment!']);
        }
}

public function getComment(Request $request)
{
    $comments = CardComments::where('card_id', $request->id)->with('admin')->get();
    if ($comments) {
            return response()->json(['success' => true, 'message' => 'Comment retrieve successfully.', 'data' => $comments]);
        }else{
            return response()->json(['success' => false, 'message' => 'No comment found!', 'data' => $comments]);
        }
}
public function addCustomerComment(Request $request)
{
    $inserted = CustomerComment::Insert([
        'customer_id' => $request->customer_id,
        'comment' => $request->comment,
        'user_id' => $request->admin_id,
        'created_at' => date('Y-m-d H:i:s')]);

    if ($inserted) {

            jobRosterActions($request->admin_id, 'add_customer_comments', $request->customer_id, 'customer_comments');

            return response()->json(['success' => true, 'message' => 'Comment added successfully.']);
        }else{
            return response()->json(['success' => false, 'message' => 'Fail to add comment!']);
        }
}

public function getCustmerComment($customer_id)
{
    $comments = CustomerComment::where('customer_id', $customer_id)->with('User')->get();
    if ($comments) {
            return response()->json(['success' => true, 'message' => 'Comment retrieve successfully.', 'data' => $comments]);
        }else{
            return response()->json(['success' => false, 'message' => 'No comment found!', 'data' => $comments]);
        }
}
public function getData(Request $request)
{
    $data = CardStages::with(['comments' => function($que){
        $que->with(['admin']);
    }])->where('id', $request->id)->first();
    if ($data) {
            return response()->json(['success' => true, 'message' => 'card retrieve successfully.', 'data' => $data]);
        }else{
            return response()->json(['success' => false, 'message' => 'No card found!', 'data' => $data]);
        }
}

}
