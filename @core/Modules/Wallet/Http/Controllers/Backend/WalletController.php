<?php

namespace Modules\Wallet\Http\Controllers\Backend;

use App\Helpers\FlashMsg;
use App\Mail\BasicMail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('permission:wallet-list|wallet-history',['only' => ['wallet_lists']]);
        $this->middleware('permission:wallet-history',['only' => ['wallet_history']]);
    }

    public function wallet_lists()
    {
        $wallet_lists = Wallet::with('user') ->has('user')->latest()->get(['id','buyer_id','balance','status']);
   
        return view('wallet::backend.wallet-lists',compact('wallet_lists'));
    }

    public function change_status($id)
    {
       
        $job = Wallet::find($id);
        $job->status === 1 ? $status = 0 : $status = 1;
        Wallet::where('id',$id)->update(['status'=>$status]);
        return redirect()->back()->with(FlashMsg::item_new('Status Changed Success'));
    }

    public function wallet_history()
    {
        $wallet_history_lists = WalletHistory::with('user')
            ->latest()
            ->where('payment_status','!=','')
            ->get(['id','buyer_id','payment_gateway','payment_status','amount','manual_payment_image','Action','created_at']);
        $users = User::select('id', 'name','username', 'email', 'phone', 'user_type')->get();

        return view('wallet::backend.history',compact('wallet_history_lists', 'users'));
    }

    public function wallet_history_status($id)
    {
        $wallet_history = WalletHistory::find($id);
        $status = $wallet_history->payment_status === 'pending' ? 'complete' : '';
        WalletHistory::where('id',$id)->update(['payment_status'=>$status]);
        $wallet = Wallet::select(['id','buyer_id','balance'])->where('buyer_id',$wallet_history->buyer_id)->first();
        Wallet::where('buyer_id',$wallet->buyer_id)->update([
            'balance'=>$wallet->balance+$wallet_history->amount,
        ]);
        return redirect()->back()->with(FlashMsg::item_new('Status Changed Success'));
    }


    public function depositCreateByAdmin(Request $request)
    {
            // dd($request)->all();
        $request->validate([
            'amount'=>'required|integer|min:10|max:1000000',
            'user_id'=>'required',
        ]);

        //get user_id and deposit amount
        $user_id = $request->user_id;
        $total = $request->amount;

        if($request->selected_payment_gateway == 'manual_payment'){
            $payment_status='pending';
        }else{
            $payment_status='';
        }

        // first check if user wallet empty create wallet
        if (!empty($user_id)){
            $user_wallet = Wallet::where('buyer_id',$user_id)->first();
            if(empty($user_wallet)){
                Wallet::create([
                    'buyer_id' => $user_id,
                    'balance' => 0,
                    'status' => 0,
                ]);
            }
             $user_wallet = Wallet::where('buyer_id',$user_id)->first();
        }
        
        // die;

        // create wallet history
        $deposit = WalletHistory::create([
            'buyer_id' => $user_id,
            'amount' => $total,
            'payment_gateway' => $request->selected_payment_gateway,
            'payment_status' => 'complete',
            'status' => 1,
            'Action'=> $request->action,

        ]);

        $last_deposit_id = $deposit->id;
        $email = optional($deposit->user)->email;
        
        ///
        if($request->action === 'Deposit'){
             
         $user_wallet->balance += $total;
         $user_wallet->save();
        Wallet::where('buyer_id', $user_id)->limit(1)->update(['status' => 1]);
          if($request->selected_payment_gateway === 'added_by_admin') {
            try {
                $message_body = __('Hello, New User deposit credited by Admin').'</br>'.'<span class="verify-code">'.__('Deposit ID:').$last_deposit_id.'</span>';
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => __('New Deposit Added'),
                    'message' => $message_body
                ]));
                Mail::to($email)->send(new BasicMail([
                    'subject' => __('Deposit added by admin'),
                    'message' => __('Manual deposit success. Your wallet credited by admin #').$last_deposit_id
                ]));
            } catch (\Exception $e) {
                //
            }

        }
        }
        if($request->action === 'withdrawal'){
         $user_wallet = Wallet::where('buyer_id',$user_id)->first();
        
            if(!empty($user_wallet)){
            if($user_wallet->balance >= $request->amount){
          $user_wallet->balance -= $request->amount;
         $user_wallet->save();
          if($request->selected_payment_gateway === 'added_by_admin') {
            try {
                $message_body = __('Hello, New User Debit by Admin').'</br>'.'<span class="verify-code">'.__('Debit ID:').$last_deposit_id.'</span>';
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => __('New Deposit Added'),
                    'message' => $message_body
                ]));
                Mail::to($email)->send(new BasicMail([
                    'subject' => __('Debit  by admin'),
                    'message' => __('Manual Debit success. Your wallet Debit by admin #').$last_deposit_id
                ]));
            } catch (\Exception $e) {
                //
            }

        }
        
            }else{
                
                WalletHistory::where('id',  $last_deposit_id)->update([
            'Action' =>"Request Reject due to Low balance of User",
        ]);
                toastr_warning('User wallet  Balance is Low Empty');
                        return back();
            }
            
            }else{
                 toastr_warning('User wallet is Already Empty');
                        return back();
            }
            
            
        }
        
        
        
        
        ////

      
        toastr_success(__('Manual deposit success. User wallet credited'));
        return back();
     }

}
