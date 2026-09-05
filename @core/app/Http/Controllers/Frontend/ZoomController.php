<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller; 
use Illuminate\Http\Request;
use App\VideoSession;
use Illuminate\Support\Facades\Auth;

class ZoomController extends Controller
{
    public function index()
    {
        
         $seller = Auth::user();

        $sessionName = 'session_' . uniqid();
        
        $role ='1';

        return view('frontend.zoommeeting',compact('sessionName','seller','role'));
    }

    public function startSession(Request $request)
    {
        die;
        $seller = Auth::user();

        $sessionName = 'session_' . uniqid();

        $session = VideoSession::create([
            'seller_id' => $seller->id,
            'session_name' => $sessionName,
            'status' => 'active',
            'started_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'session_name' => $sessionName,
            'join_url' => url('video/join/' . $sessionName)
        ]);
    }

    public function generateSignature(Request $request)
{
    $sdkKey = env('ZOOM_VIDEO_SDK_KEY');
    $sdkSecret = env('ZOOM_VIDEO_SDK_SECRET');
    $sessionName = $request->sessionName;
    $role = $request->role;

    $iat = time();
    $exp = $iat + 60 * 60 * 2;

    $payload = [
        'app_key' => $sdkKey,
        'tpc' => $sessionName,
        'role_type' => $role,
        'version' => 1,
        'iat' => $iat,
        'exp' => $exp,
    ];

    $header = ['alg' => 'HS256', 'typ' => 'JWT'];

    $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');

    $signature = hash_hmac('sha256', "$base64UrlHeader.$base64UrlPayload", $sdkSecret, true);
    $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

    $jwt = "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";

    return response()->json(['signature' => $jwt]);
}


    public function joinSession($sessionName)
    {
        $session = VideoSession::where('session_name', $sessionName)->first();

        // if (!$session || $session->status === 'ended') {
        //     abort(404, 'Session not available.');
        // }

        $user = Auth::user();
       // $role = $user && $user->id === $session->seller_id ? 1 : 0;
$role=0;
        return view('frontend.zoommeeting', compact('sessionName', 'user', 'role'));
    }

    public function endSession($sessionName)
    {
        $session = VideoSession::where('session_name', $sessionName)->first();

        if ($session && $session->status === 'active') {
            $session->status = 'ended';
            $session->ended_at = now();
            $session->save();
        }

        return response()->json(['success' => true, 'message' => 'Session ended']);
    }
}