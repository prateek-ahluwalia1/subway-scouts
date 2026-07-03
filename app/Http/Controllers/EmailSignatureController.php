<?php

namespace App\Http\Controllers;

use App\Http\Resources\EditEmailSignature;
use App\Models\EmailSignature;
use Illuminate\Http\Request;

class EmailSignatureController extends Controller
{
    public function storeEmailSignature(Request $request)
    {
        $emailSignature = new  EmailSignature();
        $emailSignature->admin_id = $request->admin_id;
        $emailSignature->title = $request->title;
        $emailSignature->body = $request->body;
        $emailSignature->save();
        jobRosterActions($request->admin_id, 'add_email_signature', $emailSignature->id, 'email_signatures');
        return response()->json(['message' => 'E-mail signature created' , 'success' => true]);
    }

    public function getAllEmailSignature(Request $request)
    {
        $getAllEmailSignatures = EmailSignature::where('admin_id', $request->admin_id)->get();
        return response()->json([ 'success' => true, 'data' => $getAllEmailSignatures , 'code' => 200 ]);
    }

    public function getAllEmailSignatureTitleAndID(Request $request)
    {
        $getAllEmailSignatures = EmailSignature::where('admin_id', $request->admin_id)->select('id','title','body')->get();
        return response()->json([ 'success' => true, 'data' => $getAllEmailSignatures , 'code' => 200 ]);
    }

    public function editEmailSignature(Request $request)
    {
        $emailSignature = EmailSignature::where('id', $request->id)->first();
        $eS = (new EditEmailSignature($emailSignature));
        return response()->json([ 'success' => true, 'data' => $eS , 'code' => 200 ]);
    }

    public function updateEmailSignature(Request $request)
    {
        $emailSignature =  EmailSignature::where('id', $request->id)->first();
        $old_data = $emailSignature;
        if($emailSignature){
            $emailSignature->admin_id = $request->admin_id;
            $emailSignature->title = $request->title;
            $emailSignature->body = $request->body;
            $emailSignature->save();
            jobRosterActions($request->admin_id, 'update_email_signature', $emailSignature->id, 'email_signatures', $old_data);
            return response()->json(['message' => 'E-mail signature updated' , 'success' => true]);
        }else{
            return response()->json(['message' => 'Record not found!' , 'success' => false]);
        }
    }

    public function deleteEmailSignature(Request $request)
    {
      $emailSignature = EmailSignature::where('id', $request->id)->first();
      $old_data = $emailSignature;
      if(!empty($emailSignature)){
         $emailSignature->delete();
         jobRosterActions($request->admin_id, 'delete_email_signature', $emailSignature->id, 'email_signatures', $old_data);
        //  logging('delete', $request->id, 'login_user', 'Guard Documents');
         return response()->json(['message' => "E-mail signature deleted" ,  'code' => 200, 'success' => true]);
      }else{
         return response()->json(['message' => "Email Signature Not Found" ,  'code' => '404', 'success' => 'true'],404);
      }
    }
}
