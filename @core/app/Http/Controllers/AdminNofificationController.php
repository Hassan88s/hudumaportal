<?php

namespace App\Http\Controllers;

use App\AdminNotification;
use App\Helpers\FlashMsg;
use Illuminate\Http\Request;

class AdminNofificationController extends Controller
{
    /**
     * Which notification columns this admin's role covers.
     *
     * The bell only shows an admin the types their role covers, and this page
     * is what the bell's "View all" links to, so it has to answer the same
     * way — otherwise the full list hands back everything the bell was careful
     * to leave out. The page itself stays open to every admin: a role with
     * none of these areas simply sees an empty list.
     *
     * @return array<int,string>
     */
    protected function visible_columns(): array
    {
        $admin  = auth()->guard('admin')->user();
        $canSee = [];

        if ($admin) {
            if ($admin->can('order-list'))  { $canSee[] = 'order_id'; }
            if ($admin->can('ticket-list')) { $canSee[] = 'ticket_id'; }
            if ($admin->can('job-list'))    { $canSee[] = 'job_post_id'; }
        }

        return $canSee;
    }

    /** Notifications this admin may see, newest first. */
    protected function visible_query()
    {
        $canSee = $this->visible_columns();

        if (empty($canSee)) {
            return AdminNotification::whereRaw('1 = 0');
        }

        return AdminNotification::where(function ($q) use ($canSee) {
            foreach ($canSee as $column) {
                $q->orWhereNotNull($column);
            }
        })->latest('id');
    }

    public function notifications()
    {
        $notifications = $this->visible_query()->paginate(30);
        $unread_count  = (clone $this->visible_query())->where('status', 0)->count();

        return view('backend.pages.notifications.all-notification', compact('notifications', 'unread_count'));
    }

    /**
     * Opening an order or a ticket marks its notification read, but nothing
     * ever marked a job-post notification read — so those sat unread forever
     * and the bell's count could only grow. An admin can now clear them here.
     */
    public function markRead($id)
    {
        $notification = $this->visible_query()->where('id', $id)->first();

        if (! $notification) {
            return redirect()->back()->with(['msg' => __('That notification is not yours to change.'), 'type' => 'danger']);
        }

        $notification->update(['status' => 1]);

        return redirect()->back()->with(['msg' => __('Marked as read.'), 'type' => 'success']);
    }

    /** Clear everything this admin can see. Other roles' rows are untouched. */
    public function markAllRead()
    {
        $changed = $this->visible_query()->where('status', 0)->update(['status' => 1]);

        return redirect()->back()->with([
            'msg'  => trans_choice('{0}Nothing was unread.|{1}One notification marked as read.|[2,*]:count notifications marked as read.', $changed, ['count' => $changed]),
            'type' => 'success',
        ]);
    }

    /**
     * Clear the whole list in one go.
     *
     * Scoped like everything else here, so an admin only empties the areas
     * their role covers and another role's notifications are left where they
     * are. Lands back on the first page, since whichever page the button was
     * pressed on no longer exists.
     */
    public function deleteAll()
    {
        $deleted = $this->visible_query()->reorder()->delete();

        return redirect()->route('admin.notifications.all')->with([
            'msg'  => trans_choice(
                '{0}There was nothing to delete.|{1}One notification deleted.|[2,*]:count notifications deleted.',
                $deleted,
                ['count' => $deleted]
            ),
            'type' => 'danger',
        ]);
    }

    public function notificationDelete($id)
    {
        // Scoped to what this admin may see, so a role cannot delete the
        // notifications of an area it has no access to.
        $notification = $this->visible_query()->where('id', $id)->first();

        if (! $notification) {
            return redirect()->back()->with(['msg' => __('That notification is not yours to change.'), 'type' => 'danger']);
        }

        $notification->delete();

        return redirect()->back()->with(FlashMsg::item_delete('Notification Deleted Success'));
    }
}
