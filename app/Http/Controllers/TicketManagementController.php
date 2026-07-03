<?php

namespace App\Http\Controllers;

use App\Events\SupportTicketEvent;
use App\Models\TicketDetails;
use App\Models\TicketManagement;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;

class TicketManagementController extends Controller
{
    public function openATicket(Request $request){
        if(isset($request->id)){
            $ticketManagement = TicketManagement::find($request->id);
        }else{
            $ticketManagement = new TicketManagement();
        }
        $ticketManagement->ticket_type = $request->ticket_type;
        $ticketManagement->guard_id = $request->guard_id;
        $ticketManagement->contractor_id = $request->contractor_id;
        $ticketManagement->saleperson_id = $request->saleperson_id;
        $ticketManagement->customer_id = $request->customer_id;
        $ticketManagement->admins_id = $request->admins_id; #ADMIN ID WHO OPEN TICKET AND SPECIAL VARIABLE IS FALSE
        $ticketManagement->name = $request->name;
        $ticketManagement->email = $request->email;
        $ticketManagement->subject = $request->subject;
        $ticketManagement->priority = $request->priority;
        $ticketManagement->message = $request->message;
        $ticketManagement->file = $request->file;
        $ticketManagement->replied_by = $request->replied_by;
        $ticketManagement->status = 'open';
        $ticketManagement->save();
        if(!isset($request->id)){
            $ticketDetails = new TicketDetails();
            $ticketDetails->ticket_management_id = $ticketManagement->id;
            $ticketDetails->guard_id = $request->guard_id;
            $ticketDetails->admin_id = $request->admins_id; #ADMIN ID WHO ANWER THE TICKET AND SPECIAL VARIABLE IS TRUE
            $ticketDetails->contractor_id = $request->contractor_id;
            $ticketDetails->saleperson_id = $request->saleperson_id;
            $ticketDetails->customer_id = $request->customer_id;
            $ticketDetails->admins_id = $request->admins_id; #ADMINs ID WHO OPEN TICKET AND SPECIAL VARIABLE IS FALSE
            $ticketDetails->message = $request->message;
            $ticketDetails->attachments = json_encode($request->attachments);
            $ticketDetails->save();
        }
        # SEND NOTIFICATION TO ALL ADMINS WHICH SPECIAL VARIABLE IS TRUE
        // $admins = User::where('userType', 'super-admin')->where('is_super_admin', 1)->get();
        $admins = User::where('userType', 'super-admin')->where('is_received_support', 1)->where('is_super_admin', 1)->get();
        $ticketCount = TicketManagement::whereIn('status', ['open', 'answered'])->count();
        $currentDate = Carbon::now()->format('d-m-Y');
      
        foreach ($admins as $admin){
            event(new SupportTicketEvent($ticketManagement->name, $ticketManagement->subject, $admin['id'], $ticketCount));
            
            $data = [
                'email' => $admin->email,
                'date' => $currentDate,
                'name' => $ticketManagement->name,
                'subject' => $ticketManagement->subject,
                'ticket' => $ticketManagement->id,
                'reply_by' => '',
                'message' => $ticketManagement->message,
            ];
            
            Mail::send('mail.SendTicketEmail', ['data' => $data], function($mail) use ($data) {
                $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                $mail->to($data['email'])->subject($data['subject']);
            });
           
        }

        $createticket = TicketManagement::where('ticket_management.id', $ticketManagement->id)
        ->leftJoin('users', 'users.id', '=', 'ticket_management.admins_id')
        ->select('ticket_management.admins_id', 'ticket_management.subject', 'ticket_management.status', 'ticket_management.id', 'users.email', 'users.name')
        ->first();

        if($createticket)
        {
            $message = "The ticket has been successfully created and will be entertained with in 24 hours. If submitted after 5 PM during working days (Monday to Friday), it will be addressed on the next working day. Thank you for reaching out.";

            $data = [
                'email' => $createticket->email,
                'date' => $currentDate,
                'name' => $createticket->name,
                'ticket' => $createticket->id,
                'subject' => "Open Ticket",
                'status' => $createticket->status,
                'message' => $message
            ];

                Mail::send('mail.CloseTicketEmail', ['data' => $data], function($mail) use ($data) {
                    $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                    $mail->to($data['email'])->subject($data['subject']);
                });
        }
        return response()->json([
            'success' => true,
            'message' => 'Your ticket has been sent we will contact you soon.'
        ]);
    }
    public function getTickets(Request $request){
        if(isset($request->guard_id) && $request->guard_id != null){
            $tickets = TicketManagement::where('guard_id', $request->guard_id)->latest()->get();
        }else{
            if($request->is_received_support == false){
                $tickets = TicketManagement::where('admins_id', $request->admins_id)->latest()->get();
                foreach ($tickets as $ticket) {
                    $latestTicketDetail = TicketDetails::where('ticket_management_id', $ticket->id)
                    ->latest()
                    ->join('users', 'users.id', '=', 'ticket_details.admin_id')
                    ->select('ticket_details.*', 'users.name as sender_name')
                    ->first();                
                    if ($latestTicketDetail) {
                        $ticket->sender_name = $latestTicketDetail->sender_name;
                    } else {
                        $ticket->sender_name = null;
                    }

                }
                
            }else{
                $tickets = TicketManagement::latest()->get();

                foreach ($tickets as $ticket) {
                    $latestTicketDetail = TicketDetails::where('ticket_management_id', $ticket->id)
                    ->latest()
                    ->join('users', 'users.id', '=', 'ticket_details.admin_id')
                    ->select('ticket_details.*', 'users.name as sender_name')
                    ->first();                
                    if ($latestTicketDetail) {
                        $ticket->sender_name = $latestTicketDetail->sender_name;
                    } else {
                        $ticket->sender_name = null;
                    }

                }
                
            }
        }
        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }
    public function getTicketDetails($ticket_id){
        $data = TicketDetails::where('ticket_management_id', $ticket_id)->with(['guardDetails', 'admin'])->get();
        $ticketDetails = TicketManagement::with('guardDetails')->find($ticket_id);
        $ticketDetails->last_updated_at = TicketDetails::where('ticket_management_id', $ticket_id)->select('id','updated_at')->latest();
        return response()->json([
            'success' => true,
            'data' => $data,
            'ticketDetails' => $ticketDetails
        ]);
    }
    public function replyTicket(Request $request){
        $ticketDetails = new TicketDetails();
        $ticketDetails->ticket_management_id = $request->ticket_id;
        $ticketDetails->guard_id = $request->guard_id;
        $ticketDetails->admin_id = $request->admin_id;
        $ticketDetails->admins_id = $request->admins_id;
        $ticketDetails->customer_id = $request->customer_id;
        $ticketDetails->contractor_id = $request->contractor_id;
        $ticketDetails->saleperson_id = $request->saleperson_id;
        $ticketDetails->message = $request->message;
        $ticketDetails->attachments = json_encode($request->attachments);
        $ticketDetails->save();
        $tickets = TicketManagement::find($request->ticket_id);
        $sender = User::where('id', $request->admin_id)->first();
        $getId = TicketDetails::where('ticket_management_id', $request->ticket_id)
        ->where('admin_id', '!=', $request->admin_id)
        ->groupBy('admin_id')
        ->leftjoin('users', 'users.id', '=', 'ticket_details.admin_id')
        ->select('admin_id', 'users.email', 'users.name')
        ->get();
            
        if($request->admin_id && $tickets->status == 'open'){
            $secondLatestRecord = TicketDetails::where('ticket_management_id', $request->ticket_id)->latest()->skip(1)->take(1)->get();
            if(($secondLatestRecord[0]['guard_id'] != $request->guard_id) || ($secondLatestRecord[0]['admin_id'] != $request->admin_id)){
                $tickets->status = 'answered';
                $tickets->update();
            }
        }

        foreach ($getId as $admin){
            $data = [
                'email' => $admin->email,
                'date' => $tickets->created_at->format('d-m-Y'),
                'name' => $tickets->name,
                'ticket' => $request->ticket_id,
                'reply_by' => $sender->name,
                'subject' => $tickets->subject,
                'message' => $request->message,
                
            ];
            
            if(!empty($data['email']))
            {
                Mail::send('mail.SendTicketEmail', ['data' => $data], function($mail) use ($data) {
                    $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                    $mail->to($data['email'])->subject($data['subject']);
                });
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Answered'
        ]);
    }
    public function closeTicket($ticket_id){
        $ticket = TicketManagement::find($ticket_id);
        if($ticket){

            $closeticket = TicketDetails::where('ticket_management_id', $ticket_id)
            ->groupBy('admin_id')
            ->leftjoin('users', 'users.id', '=', 'ticket_details.admin_id')
            ->select('admin_id', 'users.email', 'users.name')
            ->get();
            
            $ticket->status = 'closed';
            $ticket->update();

            $message = "All the issues that you had added to the ticket have been resolved. Now, we are going to close it. If you need further assistance, please feel free to contact us again.";

            if($closeticket)
            {
                foreach ($closeticket as $admin){
                    $data = [
                        'email' => $admin->email,
                        'date' => $ticket->created_at->format('d-m-Y'),
                        'name' => $ticket->name,
                        'ticket' => $ticket->id,
                        'subject' => "Close Ticket",
                        'status' => $ticket->status,
                        'message' => $message
                    ];
                    
                    if(!empty($data['email']))
                    {
                        Mail::send('mail.CloseTicketEmail', ['data' => $data], function($mail) use ($data) {
                            $mail->from('no-reply@thescouts.com.au', 'AMG Security');
                            $mail->to($data['email'])->subject($data['subject']);
                        });
                    }
                }
            }
            return response()->json([
                'success' => true,
                'message' => 'Ticket has been closed.'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found.'
            ]);
        }
    }
}
