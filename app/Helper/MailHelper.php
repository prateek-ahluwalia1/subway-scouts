<?php

use Carbon\Carbon;

// use App\Mail\SignupEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

function translateMessage($message){
    App::setLocale(Session::get('current_locale','en'));
    return __($message);
}

function sendMessage($password, $email, $description){
    //dd($email);
    $data = [
        'password' => $password,
        'description' => $description,
        'email' => $email
    ];
    Mail::send('mail.message', $data, function($message) use ($data){
        $message->to($data['email'])->subject(translateMessage(__('emails.subjects.message-send')));
    });
}
function adminMessage($email,$message){
    // dd($email);
    $data = [
        'message' => $message,
        'email' => $email
    ];
    Mail::send('mail.adminMessage', $data, function($message) use ($data){
        $message->to($data['email'])->subject("message");
    });
}

function forgotPassword($email,$token, $database){
    $data = [
        'token' => $token,
        'email' => $email,
        'database' => $database,
    ];
    Mail::send('mail.forgotPassword', $data, function($mail) use ($data){
        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
        $mail->to($data['email'])->subject("Forgot Password");
    });
}

function sendUncoverdShiftToAdmin($records,$email){
    $data = [
        'email' => $email,
        'records' => $records,
    ];
    Mail::send('mail.uncoverd-shifts', $data, function($mail) use ($data){
        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
        $mail->to($data['email'])->subject("UnConverd Shifts");
    });
}




function formEmailLink($email,$form_id){
    // dd($form_id);
    $data = [
        'email' => $email,
        'link' => 'https://app.thescouts.com.au/#/form/'.$form_id
    ];
    Mail::send('mail.formEmailLink', $data, function($mail) use ($data){
        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
        $mail->to($data['email']['email']);
        $mail->subject("Form Link");
    });
}
function formEmailBuiltIn($email, $form_id, $guard_id, $business_id){
    $data = [
        'email' => $email,
        'link' => 'https://app.thescouts.com.au/#/built-in/' . $form_id . '?guard_id=' . $guard_id . '&busi_id=' . $business_id,
    ];
    Mail::send('mail.formEmailLink', $data, function($mail) use ($data){
        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
        $mail->to($data['email']);
        $mail->subject("Form Link");
    });
}



function isEmailVarifay($email,$token,$pass){
    $data = [
        'token' => $token,
        'email' => $email,
        'title' => "Staff Verify Email",
        'password' => $pass
    ];
    
    // Mail::send('mail.isEmailVarifay', $data, function($token) use ($data){
    //     $token->to($data['email'])->subject("Guard varifay Email");
    // });
    Mail::send('mail.isEmailVarifay', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Staff Verify Email");
    });

}

function isAdminEmailVerify($email,$bus_id, $password){
    $data = [
        'email' => $email,
        'business_id' => $bus_id,
        'title' => "Admin Verify Email",
        'password' => $password,
    ];
    Mail::send('mail.isAdminEmailVarifay', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Admin verify email");
    });
}

function isEmailSendSubAdmin($user_name,$msg,$email,$status, $pass, $qr_img){
    $data = [
        'name' => $user_name,
        'msg' => $msg,
        'email' => $email,
        'password' => $pass,
        'qr_img' => $qr_img,
    ];

    $view1 = 'mail.isSubAdminActive';
    $view2 = 'mail.isSubAdminInActive';
    $page = '';

    if($status == 'active'){
        $page = $view1;
    }else{
        $page = $view2;
    }
    Mail::send($page, $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Welcome");
    });
}

function isEmailSendSuperAdmin($msg,$email){
    $data = [
        'msg' => $msg,
        'email' => $email,
    ];
    Mail::send('mail.isSupAdminActiveSubAdmin', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Register admin on AMG Security");
    });
}

function isCustomerCreate($email,$token){
    $data = [
        'token' => $token,
        'email' => $email,
        'title' => "Customer account create email"
    ];
    Mail::send('mail.customer-create-account', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Customer account create email");
    });
}

function isCustomerDelete($email,$token){
    $data = [
        'token' => $token,
        'email' => $email,
        'title' => "Customer account deletion"
    ];
    Mail::send('mail.customer-delete-account', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Customer account deleted");
    });
}

function isContractorDelete($email,$token){
    $data = [
        'token' => $token,
        'email' => $email,
        'title' => "Contractor account deletion"
    ];
    Mail::send('mail.contractor-delete-account', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Contractor account deleted");
    });
}

function isContractorCreate($email,$token){
    $data = [
        'token' => $token,
        'email' => $email,
        'title' => "Contractor account create email"
    ];
    Mail::send('mail.contractor-delete-account', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Contractor account create email");
    });
}

function sendMailToFitness($email,$message,$user_name,$user_email){
    // dd($email);
    $data = [
        'message' => $message,
        'email' => $email,
        'user_name' => $user_name,
        'user_email' => $user_email
    ];
    Mail::send('mail.fitnessMessage', $data, function($message) use ($data){
        $message->to($data['email'])->subject("message");
    });
}

function businessEmailCompaign($email,$subject,$message){
     //dd($email);
    $data = [
        'email'   => $email,
        'message' => $message,
        'subject' => $subject,
    ];
    Mail::send('mail.fitnessMessage', $data, function($message) use ($data){
        $message->to($data['email'])->subject("message");
    });
}

function adminEmailCompaign($email,$subject,$message){
    //dd($email);
   $data = [
       'email'   => $email,
       'message' => $message,
       'subject' => $subject,
   ];
   Mail::send('mail.fitnessMessage', $data, function($message) use ($data){
       $message->to($data['email'])->subject("message");
   });
}

function generalEmails($prams){
    $data = [
        'message' => $prams['message'],
        'token' => $prams['message'],
        'email' => $prams['email'],
        'subject' => $prams['subject']
    ];
    Mail::send('mail.general', $data, function($mail) use ($data){
        $mail->from('no-reply@thescouts.com.au', 'AMG Security');
        $mail->to($data['email'])->subject($data['subject']);
    });
}

function systemEmail($prams)
{
    $data = [
       'email'   => $prams['email'],
       'description' => $prams['message'],
       'subject' => $prams['subject'],
       'attachment' => isset($prams['attachment']) && $prams['attachment'] != '' ? $prams['attachment'] : null
   ];
   Mail::send('mail.systemGeneralEmail', $data, function($message) use ($data){
        $message->from('no-reply@thescouts.com.au', 'AMG Security');
       $message->to($data['email'])->subject($data['subject']);
       if ($data['attachment'] != null) {
           $message->attach($data['attachment']);
       }
   });
}
function crmReminderMail($email,$subject,$message, $datadynamic){
    $data = [
        'email'   => $email,
        'message' => $message,
        'subject' => $subject,
        'data' => $datadynamic
    ];
    Mail::send('mail.crm-reminder-mail', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Reminder - TheScouts");
    });
}
function genericMail($email,$subject,$message, $datadynamic){
    $data = [
        'email'   => $email,
        'message' => $message,
        'subject' => $subject,
        'data' => $datadynamic
    ];
    Mail::send('mail.genericMail', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Assigned New Lead");
        // $token->subject("Assigned New Lead ".$data['leaad_client_name']);
    });
}
function crmLeadCount($email,$subject,$message, $datadynamic){
    $data = [
        'email'   => $email,
        'message' => $message,
        'subject' => $subject,
        'data' => $datadynamic
    ];
    Mail::send('mail.crmLeadCount', $data, function($token)use($data){
        $token->from('no-reply@thescouts.com.au', 'AMG Security')
        ->to($data['email']);
        $token->subject("Crm Lead Report");
        // $token->subject("Assigned New Lead ".$data['leaad_client_name']);
    });
}
// function sendMessage($name, $email, $description){

//     $data = [
//         'name' => $name,
//         'description' => $description,
//         'email' => $email
//     ];
//     Mail::send('mail.message', $data, function($message) use ($data){
//         $message->to($data['email'])->subject(translateMessage(__('emails.subjects.message-send')));
//     });
// }

