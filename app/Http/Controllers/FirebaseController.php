<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseController extends Controller
{
    static public function sendNotification($payload){
        $factory = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        // $factory = (new Factory)->withServiceAccount(json_decode(env('FIREBASE_CREDENTIALS'), true));
        $messaging = $factory->createMessaging();
        // $notification = Notification::create('Hello', 'This is a test notification');
        $data = [];
        $data['token'] = $payload['token'];
        $data['notification']['title'] = $payload['title'] ?? null;
        $data['notification']['body'] = $payload['body'] ?? null;
        // $data['notification']['imageUrl'] = null;
        $data['data'] = $payload['payload'] ?? null;
        // return $data;
        $message = CloudMessage::fromArray($data);
        $messaging->send($message);
    }
}
