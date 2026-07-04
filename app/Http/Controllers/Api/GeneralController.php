<?php
namespace App\Http\Controllers\Api;


use App\Models\Guard;
use App\Models\GuardQuestionnaireDetails;
use Carbon\Carbon;
use App\Models\Questionnaire;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class GeneralController extends ApiController
{

    public $currentUser;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function list_tutorials() {
        $logged_user_id[] = ($this->currentUser) ? $this->currentUser->id * 1 : '';
        $tutori = [];
        $tutorials = DB::table('announcements')->whereJsonContains('send_to', $logged_user_id)->get();
        foreach ($tutorials as $key => $tut) {
            $is_in = false;
                // if ($tut->selected_guards != '') {
                //     $guards = json_decode($tut->selected_guards, true);
                //     foreach ($guards as $index => $guard_id) {
                //         if ($logged_user_id == $guard_id) {
                //             $is_in = true;
                //         }
                //     }
                // }
                // if ($is_in != true) {
                //     unset($tutorials[$key]);
                // }else{
            $tutori[] = $tut;
                // }
        }


        $this->statusCode = self::STATUS_CODE_200;
        if (count($tutorials) == 0) {
            $this->response = [
                'success' => false,
                'message' => 'No announcements found!',
                'tutorials' => $tutori,
                'logged_user_id' => $logged_user_id
            ];
        }else{
         $this->response = [
            'success' => true,
            'message' => 'List of tutorials.',
            'tutorials' => $tutori
        ]; 
    }
    return $this->sendResponse();


}
function get_chat_user_list()
{
    $chat_user = DB::table('app_basic_settings')->first();
    $this->statusCode = self::STATUS_CODE_200;
    if (empty($chat_user)) {
        $this->response = [
            'success' => false,
            'message' => 'No chat user found',
            'users' => array()
        ];
    }else{
     $this->response = [
        'success' => true,
        'message' => 'List of chat user',
        'users' => array(
            'admin_user' => DB::table('administrators')->where('id', $chat_user->admin_user)->select('id', 'name')->first(),
            'operations_user' => DB::table('administrators')->where('id', $chat_user->operations_user)->select('id', 'name')->first(),
            'hr_user' => DB::table('administrators')->where('id', $chat_user->hr_user)->select('id', 'name')->first(),
            'payroll_user' => DB::table('administrators')->where('id', $chat_user->payroll_user)->select('id', 'name')->first(),
        )
    ]; 
}
return $this->sendResponse();
}

public function list_inductions() {

    $logged_user_id = ($this->currentUser) ? $this->currentUser->id * 1 : '';
    $inductions = DB::table('inductions')->whereJsonContains('send_to', $logged_user_id)->get();
    $new_induction = array();
    foreach ($inductions as $key => $induction) {
        $induction->selected_guards = '[]';
        $induction->send_by_list = null;
        //$induction->image = '';
        $induction->status = 'active';

                $image = DB::table('induction_image')->where(['guard_id' => $logged_user_id, 'induction_id' => $induction->id])->orderBy('id', 'desc')->first();
                if (!empty($image)) {
                    $induction->image = 'https://'.request()->getHttpHost().'/uploads/'.$image->image;
                }else{
                    $induction->image = '';
                }


                // $is_in = false;
                // if ($induction->selected_guards != '') {
                //     $guards = json_decode($induction->selected_guards, true);
                //     if (is_array($guards)) {
                //     foreach ($guards as $index => $guard_id) {
                //         if ($logged_user_id == $guard_id) {
                //             $is_in = true;
                //         }
                //     }
                //     }else{
                //         if ($induction->selected_guards == $logged_user_id) {
                //             $is_in = true;
                //         }
                //     }
                // }
                // if ($is_in == true) {
        $new_induction[] = $induction;
                // }
    }

    $this->statusCode = self::STATUS_CODE_200;
    if (count($new_induction) == 0) {
        $this->response = [
            'success' => false,
            'message' => 'No inductions found!',
            'tutorials' => $new_induction
        ];
    }else{
        $this->response = [
            'success' => true,
            'message' => 'List of inductions.',
            'inductions' => $new_induction
        ];
    }
    return $this->sendResponse();
}


public function list_about_us() {

    $about_us_list = DB::table('about_company')->get();

    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'List of about us.',
        'about_us_list' => $about_us_list
    ];
    return $this->sendResponse();
}

private function uploader_base64($file) {
    try {
            // $destinationPath =  rtrim(app()->basePath('public/uploads/'), '');
    //         try{
        $public_path =  rtrim(app()->basePath('public/'), '');
        $public_path = str_replace('portal/public', '', $public_path);
        $public_path = str_replace('apis/public', '', $public_path);
        $destinationPath = $public_path.'uploads/';
    // }catch(Exception $e){
        // $destinationPath =  rtrim('../../uploads/');
        // }

        $newName = Str::random(25);

        $fileName = $newName . '.jpg';

        
        $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $file));
        file_put_contents($destinationPath.$fileName, $file);

        return $fileName;
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
    public function uploadPdf(Request $request)
    {
        $base64Data = $request->input('image');
        $public_path =  rtrim(app()->basePath('public/'), '');
        $public_path = str_replace('portal/public', '', $public_path);
        $public_path = str_replace('apis/public', '', $public_path);
        $destinationPath = $public_path.'uploads/';
        if (!empty($base64Data)) {
            // Extract the Base64 data and decode it
            list($type, $data) = explode(';', $base64Data);
            list(, $data) = explode(',', $data);
            $decodedData = base64_decode($data);

            // Generate a unique filename
            $newName = Str::random(25);
            $fileName = $newName . time() . '.pdf';
            file_put_contents($destinationPath . $fileName, $decodedData);

            // You can also store the file path in a database if needed
            // File::create(['path' => $filename]);

            $this->statusCode = self::STATUS_CODE_200;
            $this->response = [
                'success' => true,
                'message' => 'Image successfully uploaded.',
                'image' => $fileName,
                'file_path' => 'https://'.request()->getHttpHost().'/uploads/'.$fileName
            ];
            return $this->sendResponse();
        }

        return response()->json(['message' => 'No data to upload.']);
    }

function upload_images(Request $request)
{
    $media = '';
    // foreach($request->images as $image){
        $media = $this->uploader_base64($this->request->input('image'));
    // }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Image successfully uploaded.',
        'image' => $media,
        'file_path' => 'https://'.request()->getHttpHost().'/uploads/'.$media
    ];
    return $this->sendResponse();
}

public function uplaod_tutorial_image(Request $request, $id)
{
    $field = 'image'; $media = '';
    $media = $this->uploader_base64($this->request->input($field));
    $result = DB::table('tutorial_image')->insert(array(
        'guard_id' => $id,
        'tutorial_id' => $request->input('tutorial_id'),
        'image' => $media
    ));
    if ($result) {
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Image successfully added.'
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'Fail to uplaod image!'
    ];
    return $this->sendResponse();
}


public function uplaod_induction_image(Request $request, $id)
{
    $field = 'image'; $media = '';
    $media = $this->uploader_base64($this->request->input($field));
    $already = DB::table('induction_image')->where(['guard_id' => $id,'induction_id' => $request->input('induction_id')])->first();
    if (empty($already)) {
        $result = DB::table('induction_image')->insert(array(
            'guard_id' => $id,
            'induction_id' => $request->input('induction_id'),
            'image' => $media
        ));
    }else{
        $result = DB::table('induction_image')->where([
            'guard_id' => $id,
            'induction_id' => $request->input('induction_id')
        ])->update(['image' => $media]);
    }
    if ($result) {
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Image successfully added.'
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'Fail to uplaod image!'
    ];
    return $this->sendResponse();
}

// read_induction_status

public function read_induction_status(Request $request, $id)
{

 $results= DB::table('induction_seen_status')->where('induction_id',$request->induction_id)->where('guard_id',$id)->where('status','seen')->first();
 if(empty($results)){
    $result = DB::table('induction_seen_status')->insert(array(
        'guard_id' => $id,
        'induction_id' => $request->input('induction_id'),
        'status' => 'seen'
    ));

    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Read successfully .'
    ];
    return $this->sendResponse();
}
else{
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => true,
        'message' => 'Read successfully .'
    ];
    return $this->sendResponse();
}

}



public function get_policies(Request $request, $type)
{
    $policy = DB::table('policies')->where('type', $type)->first();
    if ($policy) {
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Policy Found',
            'policy' => $policy
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'No policy found!',
        'policy' => null
    ];
    return $this->sendResponse();
}

public function get_demo_businesses()
{
    // $demo_businesses = DB::connection('mysql2')->table('business_data')->where('hide', 1)->select('business_data.*')->get();
    $demo_businesses = DB::table('business_data')->first();
        // $demo_businesses = DB::connection('mysql2')->table('business_data')->where('business_type', '=', 'demo')->select('business_data.id', 'business_data.title', 'business_data.domain', 'business_data.unique_id')->get();

    if (count($demo_businesses) > 0) {
        $demo = array();
        foreach ($demo_businesses as $db) {
            $demo[] = array(
                'title' => $db->title,
                'subTitle' => $db->sub_title,
                'logo' => $db->logo != '' ? 'https://'.request()->getHttpHost().'/uploads/'.$db->logo : "",
                'bundleID' => $db->bundle_id,
                'appName' => $db->title,
                'oneSignalAppID' => $db->app_id,
                'oneSignalServerKey' => $db->server_key,
                'oneSignalSenderID' => $db->one_signal_sender_id,
                'domain'=> $db->domain,
                'id' => $db->id,
                'businessType' => $db->business_type,
                'uniqueID' => $db->unique_id
            );
        }
        $this->statusCode = self::STATUS_CODE_200;
        $this->response = [
            'success' => true,
            'message' => 'Demo business Found.',
            'data' => $demo
        ];
        return $this->sendResponse();
    }
    $this->statusCode = self::STATUS_CODE_200;
    $this->response = [
        'success' => false,
        'message' => 'No business found!',
        'data' => null
    ];
    return $this->sendResponse();
}



public function getCurrentTime($guard_id){
    $guard = Guard::find($guard_id);
    if($guard){
       $timezone = new DateTimeZone('Australia/'.$guard->state);
       $date = new DateTime('now', $timezone);
       return response()->json([
          'success' => true,
          'time' => $date->format('d-m-Y H:i')
       ]);
    }else{
       return response()->json([
          'success' => false,
          'message' => 'Guard not found'
       ]);
    }
 }

 public function getQNA($guard_id)
 {
    $assignedQuestionnaire = GuardQuestionnaireDetails::where(['guard_id'=> $guard_id])->orderBy('created_at', 'DESC')->get();
    if(count($assignedQuestionnaire) <= 0){
         return response()->json([
        'success' => true,
        'data' => 'Induction Not Found',
         ],500);
    }   
            
    foreach ($assignedQuestionnaire as $key => $question) {
        $questionnaire = Questionnaire::find($question['questionnaire_id']);
        $question->title = $questionnaire->title; 
        $question->questionnaire = json_decode($questionnaire['questionnaire']);
        
        if($question){
            if($question->marks >= 80){
                $assignedQuestionnaire[$key]->status = 'passed';
            }else{
                $assignedQuestionnaire[$key]->status = 'failed';
            }
        }else{
            $assignedQuestionnaire[$key]->status = 'pending';
        }

    }
    if(!empty($assignedQuestionnaire)){
        return response()->json([
            'success' => true,
            'data' => $assignedQuestionnaire
        ]);
    }else{
        return response()->json([
        'success' => true,
        'data' => 'Induction Not Found',
         ]);

    }
    
 }
 
 public function submitQNA(Request $request){
    //   return $request->questionnaire_id;
    //  $previousData = GuardQuestionnaireDetails::where(['guard_id'=> $request->guard_id, 'questionnaire_id'=>$request->questionnaire_id])->first();
    //  if($previousData){
    //      $previousData->delete();
    //  }
     $guardQNADetails = GuardQuestionnaireDetails::find($request->questionnaire_id);
    //  $guardQNADetails->guard_id = $request->guard_id;
    //  $guardQNADetails->questionnaire_id = $request->questionnaire_id;
     $guardQNADetails->marks = $request->marks;
     $guardQNADetails->update();
     $guardQNADetails->refresh();
     $guard = Guard::find($request->guard_id);
     $testDetails = Questionnaire::find($guardQNADetails->questionnaire_id);
     if($request->marks >= 80){
         $config_title = config('custom.title');
         $from = 'no-reply@thescouts.com.au';
         $headers  = 'MIME-Version: 1.0' . "\r\n";
         $subject = 'Congratulations! You have passed the test successfully.';
         $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
         $headers .= 'From: '.$from."\r\n".
         'Reply-To: '.$from."\r\n" .
         'X-Mailer: PHP/' . phpversion();
         $image1 = 'https://apis.thescouts.com.au/marketing/AmgCert.jpg';
         $image3 = 'https://amg.247staffingsolutions.com.au/apis/public/mail/induction_card.jpg';
         $pdf_message = '<html>
         <head>
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <style>
           body {
      margin: 0;
      position: relative;
      height: 100vh;
      background-image: url('.$image1.');
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

                .text-content {
                    text-align: center;
                }

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
                    margin-bottom:30px;
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

                /* Media Query for Mobile Devices */
                @media screen and (max-width: 768px) {
                    .certificate {
                    font-size: 20px;
                    }

                    .officer-info {
                    font-size: 16px;
                    }

                    .compliance-list {
                    font-size: 12px;
                    }

                    .footer {
                    flex-direction: column;
                    text-align: center;
                    }

                    .footer p {
                    font-size: 11px;
                    }

                    .induction-date {
                    margin-top: 10px;
                    }
                }
                </style>
         </head>
         <body>
           <div class="main"
             <div class="text-content">
               <p class="certificate">'.$testDetails->title.'</p>
               <p class="recipient">This certificate is awarded to:</p>
               <p class="officer-info">Officer Name: <br>'.$guard->first_name.'</p>
                <p class="compliance-list">
                  ';
                foreach (json_decode($testDetails->sub_heading) as $item) {
                    $pdf_message .= '<p style="font-size: 11px;padding:0px;margin:0px">' . $item . '</p>';
                }
                $pdf_message .='
                </p>
                <div class="footer">
                    <p>Authorised for Service by <br> AMG Compliance Team</p>
                    <p class="induction-date"><b>Induction Date</b> <br> '.$guardQNADetails->updated_at->format('l, F j, Y').'</p>
                </div>
             </div>
           </div>
           </div>
         </body>
         </html>';
         $dompdf = new Dompdf();
         $options = new Options();
         $options->set('isRemoteEnabled', true);
         $dompdf->setOptions($options);
         // $html = view('induction_certificate');
         $dompdf->loadHtml($pdf_message);
         // Render the PDF
         $dompdf->render();
         // Get the generated PDF content
         $pdfContent = $dompdf->output();
         // Save the PDF to the public directory
         $destinationPath =  rtrim('../../uploads/');
         $cleanedName = str_replace(' ', '_', $guard->name);
         $fileName = $cleanedName.time().'.pdf';
         $pdfPath = $destinationPath.$fileName;
         file_put_contents($pdfPath, $pdfContent);
         $finalPdfPath = 'https://'.$_SERVER['HTTP_HOST'].'/uploads/'.$fileName;
          $mail_message = '<html>
          <head>
          <style>
              .container {
              align-items: center;
              padding: 20px;
              }
              .text-content {
              text-align: center;
              }
              .certificate {
              color: #4C5163;
              font-size: 29px;
              font-family: Trebuchet MS;
              font-weight: bold;
              }
              .recipient {
              color: #4C5163;
              font-size: 14.2px;
              }
              .officer-info {
              font-size: 22px;
              font-family: Montserrat;
              font-weight: bold;
              }
              .compliance-list {
              font-size: 14.2px;
              color: #4C5163;
              }
              .footer {
              display: flex;
              justify-content: space-between;
              margin-top: 15%;
              margin-left: -65%;
              margin-right: -65%;
              }
              .footer p {
              font-size: 13px;
              color: #4C5163;
              }
              .induction-date {
              font-size: 14px;
              text-align: right;
              }
              /* Media Query for Mobile Devices */
              @media screen and (max-width: 768px) {
              .container {
                  padding: 10px;
              }
              .certificate {
                  font-size: 20px;      }
              .officer-info {
                  font-size: 16px;
              }
              .compliance-list {
                  font-size: 12px;      }
              .footer {
                  flex-direction: column;
                  text-align: center;
                  margin-top: 10px;
              }
              .footer p {
                  font-size: 11px;
              }
              .induction-date {
              }
              }
          </style>
          </head>
          <body style="margin: 0;display: flex;justify-content: center;align-items: center;background-position: center center;">
          <div style="align-items: center;position: absolute; bottom: 45%;left:25%;padding: 20px;">
              <div style="text-align:center">
              <p style="color: #4C5163;font-size: 29px;font-family: Trebuchet MS;font-weight: bold;">Congratulations! You have passed the test successfully.</p>
              <p class="recipient">Now you can download the certificate from this link: <a href="'.$finalPdfPath.'">Certificate</a></p>
              </div>
          </div>
          </body>
          </html>';
	//   $to = 'abdulsamad.idenbrid@gmail.com';
        //   mail($to, $subject, $mail_message, $headers);
            // $data = [
            //     'subject' => $subject,
            //     // 'message' => $mail_message,
            // ];

            $guardQNADetails->certificate_path = $finalPdfPath;
            $guardQNADetails->expiry_date = Carbon::now()->addMonth(6)->format('Y-m-d');


            $guardQNADetails->update();

            // Mail::html($mail_message, function ($mail_message) use ($data) {
            //     $mail_message->to('shahbazkhan062@gmail.com')
            //             ->subject($data['subject']);
            // });
            // $toGuard = $guard->email;
            // Mail::html($mail_message, function ($mail_message) use ($data,$toGuard) {
            //     $mail_message->to($toGuard)
            //             ->subject($data['subject']);
            // });
        //   mail($toGuard, $subject, $mail_message, $headers);
     }


     return response()->json([
         'success' => true,
     ]);
 }
 public function appVersion(){
    return response()->json([
        'success' => true,
        'version' => '0.0.36' 
    ]); 
 }

 // updat announcement notification 
 function updateReadStatus(Request $request)
    {
        if ($request->type == 'announcement') {
            // Using Query Builder to check if record exists in announcement_history
            $announcementHistory = DB::table('announcement_history')
                ->where('guard_id', $request->guard_id)
                ->where('announcement_id', $request->id)
                ->first();

            if ($announcementHistory) {
                // Update read_status in announcement_history
                DB::table('announcement_history')
                    ->where('guard_id', $request->guard_id)
                    ->where('announcement_id', $request->id)
                    ->update(['read_status' => 1]);

                return response()->json(['code' => 200, 'success' => true]);
            } else {
                return response()->json(['code' => 404, 'success' => false]);
            }

        } else {
            // Using Query Builder to check if record exists in induction_history
            $inductionHistory = DB::table('induction_history')
                ->where('guard_id', $request->guard_id)
                ->where('induction_id', $request->id)
                ->first();

            if ($inductionHistory) {
                // Update read_status in induction_history
                DB::table('induction_history')
                ->where('guard_id', $request->guard_id)
                ->where('induction_id', $request->id)
                ->update(['read_status' => 1]);

                return response()->json(['code' => 200, 'success' => true]);
            } else {
                return response()->json(['code' => 404, 'success' => false]);
            }
        }
    }

    public function getGuardPayslips(Request $request)
    {
        try {
            
            $guardIds = $request->guard_id ?? [];

            $query = DB::table('guard_payslips')
                ->join('guards', 'guard_payslips.guard_id', '=', 'guards.id')
                ->where('status', 1)
                ->select(
                    'guard_payslips.*',
                    DB::raw("CONCAT(guards.first_name, ' ', guards.last_name) as guard_name")
                );

            if (!empty($guardIds)) {
                $query->whereIn('guard_payslips.guard_id', (array) $guardIds);
            }

            $payslips = $query->get();

            return response()->json([
                'status'   => true,
                'message'  => 'Guard payslips retrieved successfully',
                'data'     => $payslips,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


}
