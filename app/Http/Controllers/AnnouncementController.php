<?php

namespace App\Http\Controllers;

use App\Http\Resources\GetAllAnnouncementResource;
use App\Models\Announcement;
use App\Models\InductionHistory;
use App\Models\AnnouncementHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Guard;
use App\Models\Induction;
use App\Models\Questionnaire;
use Dompdf\Dompdf;
use Dompdf\Options;

class AnnouncementController extends Controller
{
    // code by usman bhatti..
    function addAnnouncement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            //'announcement'=>'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        } else {
            if ($request->announce_id == null) {
                $ann = new  Announcement;
                $ann->send_by = $request->admin_id;
                $ann->send_to = json_encode(array());
                $ann->title = $request->title;
                $ann->file = $request->file;
                $ann->html_body = $request->announcement;
                $ann->save();
                return response()->json([
                    'status' => 'Announcement Created',
                    'success' => true,
                ]);
            } else {
                DB::table('announcements')
                    ->where('id', $request->announce_id)
                    ->update(['title' => $request->title, 'html_body' => $request->announcement, 'file'=>$request->file]);
                return response()->json([
                    'status' => 'Announcement Updated',
                    'success' => true,
                ], 201);
            }
        }
    }


    function editAnnounce(Request $request)
    {
        $editAnnounce = Announcement::where('id', '=', $request->announce_id)->get();
        return response()->json($editAnnounce);
    }

    public function getAllAnnouncement()
    {
        $getAll = Announcement::all();
        $gA = GetAllAnnouncementResource::collection($getAll);
        return response()->json(['data' => $gA ,  'code' => 200, 'success' => true]);
    }

    public function deleteAnnouncement(Request $request)
    {
        $deleteAnnouncement = Announcement::where('id', $request->id)->first();
        if($deleteAnnouncement){
            $deleteAnnouncement->delete();
            return response()->json(['message' => 'Announcement deleted' ,  'code' => 200, 'success' => true]);
        }else{
            return response()->json(['message' => 'Record Not Found!' ,  'code' => 404, 'success' => false]);
        }
    }

    public function getAnnouncementhistory(Request $request)
    {
        if($request->type == 'announcement') {
            $AnnouncementHistory = AnnouncementHistory::select(
                'announcement_history.guard_id', 
                'guards.name', 
                'announcement_history.state', 
                \DB::raw('DATE_FORMAT(announcement_history.created_at, "%Y-%m-%d %H:%i") as date'), 
                'announcement_history.read_status'
            )
            ->join('guards', 'announcement_history.guard_id', '=', 'guards.id')
            ->where('announcement_id', $request->id)
            ->groupBy('announcement_history.guard_id')
            ->latest('announcement_history.created_at')
            ->get();
        
            if($AnnouncementHistory->isNotEmpty()) {
                return response()->json([
                    'data' => $AnnouncementHistory,
                    'code' => 200, 
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'msg' => 'Record Not Found!',
                    'code' => 404, 
                    'success' => false
                ]);
            }
        } else {
            $InductionHistory = InductionHistory::select(
                'induction_history.guard_id',
                \DB::raw("CONCAT(guards.first_name, ' ', guards.last_name) as name"),
                'induction_history.state',
                \DB::raw('DATE_FORMAT(guard_questionnaire_details.updated_at, "%Y-%m-%d %H:%i") as date'),
                'induction_history.read_status',
                'guard_questionnaire_details.certificate_path'
            )
            ->join('guards', 'induction_history.guard_id', '=', 'guards.id')
            ->leftJoin('guard_questionnaire_details', function($join) use ($request) {
                $join->on('guard_questionnaire_details.guard_id', '=', 'induction_history.guard_id')
                    ->where('guard_questionnaire_details.questionnaire_id', '=', $request->id);
            })
            ->where('induction_id', $request->id)
            ->groupBy('induction_history.guard_id')
            ->where('guards.guard_status', 'active')
            ->orderByRaw("CONCAT(guards.first_name, ' ', guards.last_name) ASC")
            ->get();

            if ($InductionHistory->isNotEmpty()) {

                return response()->json([
                    'data' => $InductionHistory,
                    'code' => 200,
                    'success' => true
                ]);
            } else {
                return response()->json([
                    'msg' => 'Record Not Found!',
                    'code' => 404,
                    'success' => false
                ]);
            }
        }
    }

    public function shareAnnouncement(Request $request)
    {
        $guards = Guard::whereIn('id', $request->guardIds)->where('notification_token', '!=', '')->get();
        if($request->type == 'announcement')
        {
            $notificaion['title'] = 'New Announcement';
            $notificaion['message'] = 'New Announcement - There is a new announcement.';
            $notificaion['page'] = 'homepage';
            foreach ($guards as $g) {
                $notificaion['notification_token'] = $g['notification_token'];
                send_push_notification($notificaion);
            }

            $Annguards = Guard::whereIn('id', $request->guardIds)->get();

            foreach ($Annguards as $guard) {

                $ann = new  AnnouncementHistory;
                $ann->guard_id = $guard['id'];
                $ann->state = $request->state;
                $ann->announcement_id = $request->id;
                $ann->save();

            }

            $send = Announcement::where('id', $request->id)->update(['send_to' => json_encode($request->guardIds)]);
            if ($send) {
                return response()->json(['message' => 'Announcement Sent' ,  'code' => 200, 'success' => true]);
            }else{
                return response()->json(['message' => 'Fail to send announcement!' ,  'code' => 200, 'success' => false]);
            }

        }else{
            $notificaion['title'] = 'New Induction';
            $notificaion['message'] = 'New Induction - There is a new induction.';
            $notificaion['page'] = 'homepage';
            foreach ($guards as $g) {
                $notificaion['notification_token'] = $g['notification_token'];
                send_push_notification($notificaion);
            }

            $Inguards = Guard::whereIn('id', $request->guardIds)->get();

            foreach ($Inguards as $guard) {

                $induction = new  InductionHistory;
                $induction->guard_id = $guard['id'];
                $induction->state = $request->state;
                $induction->induction_id = $request->id;
                $induction->save();

            }

            $send = Induction::where('id', $request->id)->update(['send_to' => json_encode($request->guardIds)]);
            if ($send) {
                return response()->json(['message' => 'Induction posted' ,  'code' => 200, 'success' => true]);
            }else{
                return response()->json(['message' => 'Fail to send induction!' ,  'code' => 200, 'success' => false]);
            }
        }
    }

    public function updateReadStatus(Request $request)
    {
        if($request->type == 'announcement')
        {
            $announcementHistory = AnnouncementHistory::where('guard_id', $request->guard_id)
            ->where('announcement_id', $request->id)
            ->first();

                if ($announcementHistory) {
                
                $announcementHistory->read_status = 1;
                $announcementHistory->update();

                return response()->json(['code' => 200, 'success' => true]);
            } else {
                return response()->json(['code' => 404, 'success' => false]);
            }

        }else{
            
            $inductionHistory = InductionHistory::where('guard_id', $request->guard_id)
            ->where('induction_id', $request->id)
            ->first();

            if ($inductionHistory) {
                
                $inductionHistory->read_status = 1;
                $inductionHistory->update();

                return response()->json(['code' => 200, 'success' => true]);
            } else {
                return response()->json(['code' => 404, 'success' => false]);
            }
        }
    }

public function regenerateAllCertificates(Request $request)
{
    try {
        // Get all records that have a certificate
        $records = DB::table('guard_questionnaire_details')
        ->whereNotNull('certificate_path')
        ->where('certificate_path', '!=', '')
        ->where('marks', '>=', 80)
->where('guard_id', '=', 460)->where('questionnaire_id', '=', 107)->get();

        if ($records->isEmpty()) {
            return response()->json([
                'msg'     => 'No certificates found to update',
                'code'    => 404,
                'success' => false
            ]);
        }

        $success = 0;
        $failed  = 0;
        $errors  = [];
$written = [];
$pdfPath11= [];

        foreach ($records as $guardQNADetails) {

            try {
                $guard       = Guard::find($guardQNADetails->guard_id);
                $testDetails = Questionnaire::find($guardQNADetails->questionnaire_id);

                // Skip if guard or questionnaire not found
                if (!$guard || !$testDetails) {
                    $failed++;
                    $errors[] = [
                        'questionnaire_id' => $guardQNADetails->id,
                        'reason'           => 'Guard or Questionnaire not found'
                    ];
                    continue;
                }

                // Skip if sub_heading is empty
                if (empty($testDetails->sub_heading)) {
                    $failed++;
                    $errors[] = [
                        'questionnaire_id' => $guardQNADetails->id,
                        'reason'           => 'Questionnaire sub_heading is empty'
                    ];
                    continue;
                }

                $image1 = 'https://apis.thescouts.com.au/marketing/AmgCert.jpg';

                $pdf_message = '<html>
                <head>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <style>
                  body {
                    margin: 0;
                    position: relative;
                    height: 100vh;
                    background-image: url(' . $image1 . ');
                    background-repeat: no-repeat;
                    background-size: contain;
                    background-position: center center;
                  }
                  .main {
                    position: absolute;
                    top: 40%;
                    left: 49%;
                    transform: translate(-50%, -45%);
                    align-items: center;
                    padding: 20px;
                  }
                  .text-content { text-align: center; }
                  .certificate {
                    color: #4c5163;
                    font-size: 29px;
                    font-family: Trebuchet MS;
                    font-weight: bold;
                  }
                  .recipient {
                    color: #4c5163;
                    font-size: 14.2px;
                    margin-top: -18px;
                  }
                  .officer-info {
                    font-size: 22px;
                    font-family: Montserrat;
                    font-weight: bold;
                  }
                  .compliance-list {
                    font-size: 14.2px;
                    color: #4c5163;
                  }
                  .footer {
                    position: absolute;
                    width: 100%;
                    margin-bottom: 30px;
                  }
                  .footer p {
                    font-size: 13px;
                    color: #4c5163;
                    margin-left: -135%;
                    margin-bottom: -35px;
                  }
                  .induction-date {
                    font-size: 14px;
                    color: #4c5163;
                    text-align: right;
                    margin-right: -35%;
                    margin-bottom: -56px;
                  }
                </style>
                </head>
                <body>
                  <div class="main">
                    <div class="text-content">
                      <p class="certificate">' . $testDetails->title . '</p>
                      <p class="recipient">This certificate is awarded to:</p>
                      <p class="officer-info">Officer Name: <br>' . $guard->first_name . '</p>
                      <p class="compliance-list">';

                foreach ($testDetails->sub_heading as $item) {
                    $pdf_message .= '<p style="font-size: 11px;padding:0px;margin:0px">' . $item . '</p>';
                }

                $pdf_message .= '
                      </p>
                      <div class="footer">
                        <p>Authorised for Service by <br> AMG Compliance Team</p>
                        <p class="induction-date"><b>Induction Date</b> <br> '.\Carbon\Carbon::parse($guardQNADetails->updated_at)->format('l, F j, Y').'</p>
                      </div>
                    </div>
                  </div>
                </body>
                </html>';

                // Generate PDF
                $dompdf = new Dompdf();
                $options = new Options();
                $options->set('isRemoteEnabled', true);
                $dompdf->setOptions($options);
                $dompdf->loadHtml($pdf_message);
                $dompdf->render();
                $pdfContent = $dompdf->output();

                // Reuse existing filename from certificate_path URL
                $fileName = basename(parse_url($guardQNADetails->certificate_path, PHP_URL_PATH));

$destinationPath = '/home/www/webroot/scouts-amg-project/app-apis/upload1' . DIRECTORY_SEPARATOR;

// Create directory if it doesn't exist
if (!is_dir($destinationPath)) {
    mkdir($destinationPath, 0755, true);
}
$pdfPath = $destinationPath . $fileName;

$pdfPath11[] = $destinationPath . $fileName;
$written[] = file_put_contents($pdfPath, $pdfContent);

if ($written === false) {
    $failed++;
    $errors[] = [
        'guard_id' => $guardQNADetails->guard_id,
        'path'     => $pdfPath,
        'reason'   => 'file_put_contents failed — check folder permissions'
    ];
    continue;
}

                $success++;

            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'guard_id' => $guardQNADetails->guard_id ?? null,
                    'reason'   => $e->getMessage()
                ];
                continue;
            }
        }

        return response()->json([
            'code'    => 200,
            'success' => true,
            'message' => 'Certificate regeneration completed',
            'summary' => [
                'total'   => $records->count(),
                'success' => $success,
                'failed'  => $failed,
'written' => $written, 
'pdfPath11' => $pdfPath11,
            ],
            'errors'  => $errors  // empty array if all succeeded
        ]);

    } catch (\Exception $e) {
        \Log::error('regenerateAllCertificates error: ' . $e->getMessage());
        return response()->json([
            'msg'     => 'Something went wrong: ' . $e->getMessage(),
            'code'    => 500,
            'success' => false
        ]);
    }
}
}
