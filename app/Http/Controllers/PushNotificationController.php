<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PushNotificationController extends Controller
{
    public function sendPushNotification()
    {
        $token = request('token');
        $response = Http::post(config('services.kitchen-sink') . 'send-push-notification', [
            'token' => $token,
        ]);

        if ($response->ok()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'success' => false,
        ]);
    }
}
