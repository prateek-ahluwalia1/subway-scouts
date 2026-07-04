<?php

namespace App\Http\Controllers;

use App\Models\Guard;
use App\Models\GuardQuestionnaireDetails;
use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function getQNA($guard_id)
    {
        $questions = Questionnaire::get();
        foreach ($questions as $key => $question) {
            $previousData = GuardQuestionnaireDetails::where(['guard_id'=> $guard_id, 'questionnaire_id'=>$question->id])->first();
            if($previousData){
                if($previousData->marks >= 80){
                    $question['status'] = 'passed';
                }else{
                    $question['status'] = 'failed';
                }
            }else{
                $question['status'] = 'pending';
            }

        }
        return response()->json([
            'success' => true,
            'data' => $questions
        ]);
    }
    public function submitQNA(Request $request){
        $previousData = GuardQuestionnaireDetails::where(['guard_id'=> $request->guard_id, 'questionnaire_id'=>$request->questionnaire_id])->first();
        if($previousData){
            $previousData->delete();
        }
        $guardQNADetails = new GuardQuestionnaireDetails();
        $guardQNADetails->guard_id = $request->guard_id;
        $guardQNADetails->questionnaire_id = $request->questionnaire_id;
        $guardQNADetails->marks = $request->marks;
        $guardQNADetails->save();
        $guard = Guard::find($request->guard_id);
        // $testDetails = QuestionAnswer::find($request->test_id);
        // if($request->marks >= 80){
        //     $root = $_SERVER['HTTP_HOST'];
        //     $root = explode('.', $root);
        //     $postfix = 'staffingsolution';
        //     if ($root[0] != 'wwww') {
        //         $postfix = $root[0];
        //     } else {
        //         $postfix = $root[1];
        //     }
        //     $config_title = config('custom.title');
        //     $from = $postfix.'@247staffingsolution.com.au';
        //     $headers  = 'MIME-Version: 1.0' . "\r\n";
        //     $subject = 'Congratulations! You have passed the test successfully.';
        //     $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        //     $headers .= 'From: '.$from."\r\n".
        //     'Reply-To: '.$from."\r\n" .
        //     'X-Mailer: PHP/' . phpversion();
        //     $image1 = 'https://apis.thescouts.com.au/public/marketing/unnamed.jpg';
        //     $image3 = 'https://amg.247staffingsolutions.com.au/apis/public/mail/induction_card.jpg';
        //     $pdf_message = '<html>

        //     <head>
        //     <style>
        //         .container {
        //         align-items: center;
        //         padding: 20px;
        //         }

        //         .text-content {
        //         text-align: center;
        //         }

        //         .certificate {
        //         color: #4c5163;
        //         font-size: 29px;
        //         font-family: Trebuchet MS;
        //         font-weight: bold;
        //         }

        //         .recipient {
        //         color: #4c5163;
        //         font-size: 14.2px;
        //         }

        //         .officer-info {
        //         font-size: 22px;
        //         font-family: Montserrat;
        //         font-weight: bold;
        //         }

        //         .compliance-list {
        //         font-size: 14.2px;
        //         color: #4c5163;
        //         }

        //         .footer {
        //         display: flex;
        //         justify-content: space-between;
        //         margin-top: 15%;
        //         margin-left: -65%;
        //         margin-right: -65%;
        //         }

        //         .footer p {
        //         font-size: 13px;
        //         color: #4c5163;
        //         }

        //         .induction-date {
        //         font-size: 14px;
        //         text-align: right;
        //         }

        //         /* Media Query for Mobile Devices */
        //         @media screen and (max-width: 768px) {
        //         .container {
        //             padding: 10px;
        //         }

        //         .certificate {
        //             font-size: 20px;      }

        //         .officer-info {
        //             font-size: 16px;
        //         }

        //         .compliance-list {
        //             font-size: 12px;      }

        //         .footer {
        //             flex-direction: column;        
        //             text-align: center;
        //             margin-top: 10px;
        //         }

        //         .footer p {
        //             font-size: 11px;
        //         }

        //         .induction-date {
                    
        //         }
        //         }
        //     </style>
        //     </head>

        //     <body style="margin: 0;display: flex;justify-content: center;align-items: center;height: 100vh;background-image: url('.$image1.');background-repeat: no-repeat;background-size: contain;background-position: center center;">
        //     <div style="align-items: center;position: absolute; bottom: 45%;left:25%;padding: 20px;">
        //         <div style="text-align:center">
        //         <p style="color: #4c5163;font-size: 29px;font-family: Trebuchet MS;font-weight: bold;">Certificate of Compliance</p>
        //         <p class="recipient">This certificate is awarded to:</p>
        //         <p class="officer-info">Officer Name: <br>'.$guard->name.'</p>
        //         <p class="compliance-list">'.$testDetails->title.'</p>
        //         </div>
        //     </div>
        //         <table style="width:100%;position: absolute; bottom: 38%;left:15%">
        //             <tr>
        //                 <td><p style="font-size: 11px;display:inline-block;margin-right:40%">Authorised for Service by <br> AMG Compliance Team</p></td>
        //                 <td><p style="display:inline-block;margin-top: 10px;font-size: 11px"><b>Induction Date</b> <br>'.$guardQNADetails->created_at->format('l, F j, Y').'</p></td>
        //             </tr>
        //         </table>
        //     </body>

        //     </html>';
        //     $dompdf = new Dompdf();
        //     $options = new Options();
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf->setOptions($options);
        //     // $html = view('induction_certificate');
        //     $dompdf->loadHtml($pdf_message);
    
        //     // Render the PDF
        //     $dompdf->render();
    
        //     // Get the generated PDF content
        //     $pdfContent = $dompdf->output();
    
        //     // Save the PDF to the public directory
        //     $destinationPath =  rtrim('../../uploads2/');
        //     $cleanedName = str_replace(' ', '_', $guard->name);
        //     $fileName = $cleanedName.time().'.pdf';
        //     $pdfPath = $destinationPath.$fileName;
        //     file_put_contents($pdfPath, $pdfContent);
        //     $finalPdfPath = 'https://'.$_SERVER['HTTP_HOST'].'/uploads2/'.$fileName;
        //     $mail_message = '<html>
        //     <head>
        //     <style>
        //         .container {
        //         align-items: center;
        //         padding: 20px;
        //         }

        //         .text-content {
        //         text-align: center;
        //         }

        //         .certificate {
        //         color: #4c5163;
        //         font-size: 29px;
        //         font-family: Trebuchet MS;
        //         font-weight: bold;
        //         }

        //         .recipient {
        //         color: #4c5163;
        //         font-size: 14.2px;
        //         }

        //         .officer-info {
        //         font-size: 22px;
        //         font-family: Montserrat;
        //         font-weight: bold;
        //         }

        //         .compliance-list {
        //         font-size: 14.2px;
        //         color: #4c5163;
        //         }

        //         .footer {
        //         display: flex;
        //         justify-content: space-between;
        //         margin-top: 15%;
        //         margin-left: -65%;
        //         margin-right: -65%;
        //         }

        //         .footer p {
        //         font-size: 13px;
        //         color: #4c5163;
        //         }

        //         .induction-date {
        //         font-size: 14px;
        //         text-align: right;
        //         }

        //         /* Media Query for Mobile Devices */
        //         @media screen and (max-width: 768px) {
        //         .container {
        //             padding: 10px;
        //         }

        //         .certificate {
        //             font-size: 20px;      }

        //         .officer-info {
        //             font-size: 16px;
        //         }

        //         .compliance-list {
        //             font-size: 12px;      }

        //         .footer {
        //             flex-direction: column;        
        //             text-align: center;
        //             margin-top: 10px;
        //         }

        //         .footer p {
        //             font-size: 11px;
        //         }

        //         .induction-date {
                    
        //         }
        //         }
        //     </style>
        //     </head>

        //     <body style="margin: 0;display: flex;justify-content: center;align-items: center;background-position: center center;">
        //     <div style="align-items: center;position: absolute; bottom: 45%;left:25%;padding: 20px;">
        //         <div style="text-align:center">
        //         <p style="color: #4c5163;font-size: 29px;font-family: Trebuchet MS;font-weight: bold;">Congratulations! You have passed the test successfully.</p>
        //         <p class="recipient">Now you can download the certificate from this link: <a href="'.$finalPdfPath.'">Certificate</a></p>
        //         </div>
        //     </div>
        //     </body>

        //     </html>'; 
        //     $to = 'abdulsamad.idenbrid@gmail.com';
        //     mail($to, $subject, $mail_message, $headers);
        //     $toGuard = $request->guard_email;
        //     mail($toGuard, $subject, $mail_message, $headers);
        // }
        return response()->json([
            'success' => true,
        ]); 
    }
}
