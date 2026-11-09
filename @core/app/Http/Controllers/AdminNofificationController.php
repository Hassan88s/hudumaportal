<?php

namespace App\Http\Controllers;

use App\AdminNotification;
use App\Helpers\FlashMsg;
use Illuminate\Http\Request;

class AdminNofificationController extends Controller
{
      /*
       * The bell only shows an admin the notification types their role covers.
       * This page is what the bell's "View all" links to, so it has to answer
       * the same way — otherwise the full list hands back everything the bell
       * was careful to leave out. The page itself stays open to every admin:
       * a role with none of these areas simply sees an empty list.
       */
      public function notifications(){
          $admin  = auth()->guard('admin')->user();
          $canSee = [];

          if ($admin) {
              if ($admin->can('order-list'))  { $canSee[] = 'order_id'; }
              if ($admin->can('ticket-list')) { $canSee[] = 'ticket_id'; }
              if ($admin->can('job-list'))    { $canSee[] = 'job_post_id'; }
          }

          $notifications = empty($canSee)
              ? AdminNotification::whereRaw('1 = 0')->get()
              : AdminNotification::where(function ($q) use ($canSee) {
                    foreach ($canSee as $column) {
                        $q->orWhereNotNull($column);
                    }
                })->latest('id')->get();

          return view('backend.pages.notifications.all-notification', compact('notifications'));
      }

      public function notificationDelete($id){
              AdminNotification::findOrFail($id)->delete();
          return redirect()->back()->with(FlashMsg::item_delete('Notification Deleted Success'));
      }
}
