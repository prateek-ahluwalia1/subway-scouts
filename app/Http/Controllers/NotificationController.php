<?php

namespace App\Http\Controllers;

use App\Events\NewMailNotification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function receive(Request $request)
    {
        if ($request->has('validationToken')) {
            return response($request->input('validationToken'), 200)->header('Content-Type', 'text/plain');
        }
        $message = $request->all();
        broadcast(new NewMailNotification($message));
        return response()->json(['message' => 'Notification received']);
    }
}
