<?php

namespace Modules\LiveChat\Http\Controllers\Frontend;

use App\Events\MessageSent;
use App\Order;
use App\User;
use Auth;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\LiveChat\Entities\LiveChatMessage;
use Illuminate\Support\Facades\Http;
use App\Mail\BasicMail;
use Illuminate\Support\Facades\Mail;
class SellerChatController extends Controller
{
    public function liveChat()
    {

        $buyers = LiveChatMessage::select('buyer_id')
            ->with('buyerList')
            ->distinct('buyer_id')
            ->where('buyer_id','!=',NULL)
            ->where('seller_id', Auth::guard('web')->user()->id)
            ->get();


        return view('livechat::frontend.seller.livechat',compact('buyers'));
    }

    /**
     * Return list of buyers the seller has chatted with (for stream link share picker).
     */
    public function chatContacts()
    {
        $userId = Auth::user()->id;
        $contacts = LiveChatMessage::select('buyer_id')
            ->distinct()
            ->where('seller_id', $userId)
            ->whereNotNull('buyer_id')
            ->pluck('buyer_id');

        $users = User::whereIn('id', $contacts)
            ->select('id','name','username','image')
            ->get()
            ->map(function($u){
                return [
                    'id' => $u->id,
                    'name' => $u->name ?: $u->username,
                    'username' => $u->username,
                ];
            });

        return response()->json(['contacts' => $users]);
    }

    /**
     * Share active live stream link to a specific buyer via chat.
     * Sends the stream URL as a chat message. Only works if seller has an active (is_live=1) stream.
     */
    public function shareStreamToChat(Request $request)
    {
        if (!$request->to_user) {
            return response()->json(['state' => 0, 'error' => 'Missing recipient'], 422);
        }

        $user = Auth::user();
        $stream = \App\AgoraStream::where('host_id', $user->id)
            ->where('is_live', 1)
            ->orderBy('started_at', 'desc')
            ->first();

        if (!$stream) {
            return response()->json([
                'state' => 0,
                'error' => __('You are not currently live. Start a stream first.')
            ], 400);
        }

        $streamUrl = url('/live?channel=' . urlencode($stream->channel_name) . '&role=audience');
        $streamTitle = $stream->title ?: (__("'s Live Stream"));
        $messageText = '🔴 ' . __('I am live now!') . ' "' . $streamTitle . '" - ' . __('Join here:') . ' ' . $streamUrl;

        $message = new LiveChatMessage();
        $message->from_user = $user->id;
        $message->to_user = $request->to_user;
        $message->message = $messageText;
        $message->seller_id = $user->id;
        $message->buyer_id = $request->to_user;
        $message->save();

        $profile_image = render_image_markup_by_attachment_id(optional($message->fromUser)->image);
        $message = LiveChatMessage::with(['fromUser', 'toUser'])->find($message->id);

        try { \event(new MessageSent($message)); } catch(\Exception $e) {}

        // Email notification to recipient about the live stream link
        try {
            $recipient = User::find($request->to_user);
            if ($recipient && !empty($recipient->email)) {
                $sender_name = $user->username ?: $user->name;
                $recipient_name = $recipient->username ?: $recipient->name;
                $titleText = $stream->title ?: __('Live Stream');

                $email_body = '<p>' . __('Hello') . ' ' . e($recipient_name) . ',</p>'
                    . '<p><strong>' . e($sender_name) . '</strong> ' . __('has sent you a live stream link on Huduma Portal.') . '</p>'
                    . '<p>' . __('Stream:') . ' <strong>' . e($titleText) . '</strong></p>'
                    . '<p style="margin:20px 0"><a href="' . $streamUrl . '" style="background:#6366f1;color:#fff;padding:12px 28px;text-decoration:none;border-radius:8px;display:inline-block;font-weight:bold">'
                    . '🔴 ' . __('Join Live Stream') . '</a></p>'
                    . '<p style="color:#666;font-size:13px">' . __('Or copy this link:') . '<br><span style="color:#6366f1">' . $streamUrl . '</span></p>'
                    . '<p style="color:#999;font-size:12px;margin-top:24px">' . __('You received this because') . ' ' . e($sender_name) . ' ' . __('shared a live stream with you in chat.') . '</p>';

                Mail::to($recipient->email)->send(new BasicMail([
                    'subject' => $sender_name . ' ' . __('sent you a live stream link'),
                    'message' => $email_body,
                ]));
            }
        } catch (\Exception $e) {
            \Log::error('Stream share email failed for to_user=' . $request->to_user . ': ' . $e->getMessage());
        }

        $all_array = $message->toArray() + ['profile_image' => $profile_image];

        return response()->json(['state' => 1, 'message' => $all_array]);
    }

    public function getLoadLatestMessages(Request $request)
    {
        if(!$request->user_id) {
            return;
        }
        $messages = LiveChatMessage::where(function($query) use ($request) {
            $query->where('from_user', \Illuminate\Support\Facades\Auth::user()->id)->where('to_user', $request->user_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('from_user', $request->user_id)->where('to_user', Auth::user()->id);
        })->orderBy('created_at', 'DESC')->limit(20)->get();


        $return = [];
        foreach ($messages->reverse() as $message) {
            $return[] = view('livechat::frontend.seller.message-line')->with('message', $message)->render();
        }

        return response()->json(['state' => 1, 'messages' => $return]);
    }

    /**
     * postSendMessage
     *
     * @param Request $request
     */
    public function postSendMessage(Request $request)
    {
       
       
        if(!$request->to_user || !$request->message) {
            return;
        }

        $message = new LiveChatMessage();

        $message->from_user = Auth::user()->id;
        $message->to_user = $request->to_user;

        if($request->message != '' && $request->message != null && $request->message != 'null')  {
            $message->message = strip_tags($request->message);
        } else {
            if($request->hasFile("image")) {
                $filename = $this->uploadImage($request);
                $message->image = $filename;
            }
        }
        $message->seller_id = Auth::user()->id;
        $message->buyer_id = $request->to_user;
        $message->save();
         $buyer_info = User::find($message->buyer_id);
        $pusher_auth = get_static_option('pusher_app_push_notification_auth_token');
        $pusher_instance_id = get_static_option('pusher_app_push_notification_instanceId');
        $pusher_auth_url = 'https://'.$pusher_instance_id.'.pushnotifications.pusher.com/publish_api/v1/instances/'.$pusher_instance_id.'/publishes';

           $seller_info = User::find($message->buyer_id);
            $response = Http::withToken($pusher_auth)->acceptJson()->post(
                $pusher_auth_url
            ,[
                "interests" => ["debug-seller".$seller_info->id, 'message'],
                "fcm" =>[
                    "notification" => [
                        "title" => "You have received a message from ".$seller_info?->name,
                        "body" => '"'.Auth::guard('web')->user()->id.'"'
                    ]
                ]
            ]);
            
            
        try{
           
            ///email and notifcations
            
           $message = get_static_option('new_message') ?? '';
                $message = str_replace(["@name"],[$seller_info->username],$message);
                Mail::to($buyer_info->email)->send(new BasicMail([
                    'subject' => __('You Have a New Message on HudumaPortal'),
                    'message' => $message
                ]));
                
         $seller_id = $buyer_info->id;

            notifySeller(
                $seller_id,
                "Umepokea ujumbe mpya! / You’ve got a new message!", //p
                "Fungua kisanduku chako cha ujumbe ili usome na kujibu.", //sms
                [
                    'type' => 'gernalnotifications',
                   // 'service_id' => $service->id,
                    'id' => uniqid('notif_'),
                    'details' => "Fungua kisanduku chako cha ujumbe ili usome na kujibu. / Open your inbox to read and reply." //p
                ]
            );  
                
            
            
            
        }catch(\Exception $e){
            //
        }
            
        $profile_image =  render_image_markup_by_attachment_id(optional($message->fromUser)->image);

        // prepare the message object along with the relations to send with the response
        $message = LiveChatMessage::with(['fromUser', 'toUser'])->find($message->id);

        // fire the event
        \event(new MessageSent($message));

        $all_array = $message->toArray() + ['profile_image'=>$profile_image];

        return response()->json(['state' => 1, 'message' => $all_array]);
    }

    /**
     * getOldMessages
     *
     * we will fetch the old messages using the last sent id from the request
     * by querying the created at date
     *
     * @param Request $request
     */
    public function getOldMessages(Request $request)
    {
        if(!$request->old_message_id || !$request->to_user)
            return;

        $message = LiveChatMessage::find($request->old_message_id);
        $previousMessages = $this->getPreviousMessages($request, $message);
        $return = [];

        $noMoreMessages = true;
        if($previousMessages->count() > 0) {
            foreach ($previousMessages as $message) {
                $return[] = view('livechat::frontend.seller.message-line')->with('message', $message)->render();
            }
            $noMoreMessages = !($this->getPreviousMessages($request, $previousMessages[$previousMessages->count() - 1])->count() > 0);
        }



        return response()->json(['state' => 1, 'messages' => $return, 'no_more_messages' => $noMoreMessages]);
    }

    /**
     * @param Request $request
     * @param $message
     * @return mixed
     */
    private function getPreviousMessages(Request $request, $message)
    {
        $previousMessages = LiveChatMessage::where(function ($query) use ($request, $message) {
            $query->where('from_user', Auth::user()->id)
                ->where('to_user', $request->to_user)
                ->where('created_at', '<', $message->created_at);
        })
            ->orWhere(function ($query) use ($request, $message) {
                $query->where('from_user', $request->to_user)
                    ->where('to_user', Auth::user()->id)
                    ->where('created_at', '<', $message->created_at);
            })
            ->orderBy('created_at', 'DESC')->limit(10)->get();

        return $previousMessages;
    }

    private function uploadImage($request)
    {
        $file = $request->file('image');
        $filename = md5(uniqid()) . "." . $file->getClientOriginalExtension();

        $file->move('assets/uploads/chat_image', $filename);

        return $filename;
    }

}
