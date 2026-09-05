<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Auth;
use App\Http\Controllers\Controller; 

require_once app_path('Libraries/Agora/RtcTokenBuilder2.php');
require_once app_path('Libraries/Agora/RtmTokenBuilder.php');

class AgoraController extends Controller
{

          public function generateToken(Request $request)
        {
            
            $appID = env('AGORA_APP_ID');
            $appCertificate = env('AGORA_APP_CERTIFICATE');
            $uid = $request->uid ?? rand(1, 999999);
            $requestedRole = $request->role ?? 'audience';
            $expireTimeInSeconds = env('AGORA_EXPIRY_TIME', 3600);
            $privilegeExpiredTs = time() + $expireTimeInSeconds;
        
            $user = Auth::user();
            $channelName = $request->channel;
        
            $actualRole = 'audience';
        
           
            $stream = \App\AgoraStream::where('channel_name', $channelName)->first();
        
      
            if ($requestedRole === 'host') {
             
                if ($stream) {
              
                    if ($stream->host_id !== $user->id) {
                        return response()->json([
                            'error' => 'You are not authorized to host this stream.'
                        ], 403);
                    }
        
                  
                    $actualRole = 'publisher';
                } 
                else {
                   
                    $channelName = 'user_' . $user->id . '_live';
        
                    $stream = \App\AgoraStream::create([
                        'channel_name' => $channelName,
                        'host_id' => $user->id,
                        'is_live' => 1,
                        'started_at' => now(),
                    ]);
        
                    $actualRole = 'publisher';
                }
            } 
        
          
            else {
                // if (!$stream || $stream->is_live == 0) {
                //     return response()->json([
                //         'error' => 'This live stream is not active.'
                //     ], 404);
                // }
                $actualRole = 'audience';
            }
        
           
            $roleValue = $actualRole === 'publisher' ? 1 : 2;
        
            $token = \RtcTokenBuilder2::buildTokenWithUid(
                $appID,
                $appCertificate,
                $channelName,
                $uid,
                $roleValue,
                $privilegeExpiredTs
            );
        
            return response()->json([
                'token' => $token,
                'appID' => $appID,
                'channel' => $channelName,
                'uid' => $uid,
                'role' => $actualRole,
            ]);
        }
        
        
        
         public function generateRtmToken(Request $request)
        {
            $appID = env('AGORA_APP_ID');
            $appCertificate = env('AGORA_APP_CERTIFICATE');
            $uid = $request->query('uid', 'guest_' . rand(1000, 9999));
            $expireTimeInSeconds = env('AGORA_EXPIRY_TIME', 3600);
            $privilegeExpiredTs = time() + $expireTimeInSeconds;
    
            // your existing RTM builder works fine here
            $role = 1; // RoleRtmUser
            $rtmToken = \RtmTokenBuilder::buildToken(
                $appID,
                $appCertificate,
                $uid,
                $role,
                $privilegeExpiredTs
            );
    
            return response()->json([
                'rtmToken' => $rtmToken,
                'uid' => $uid,
            ]);
        }


}
