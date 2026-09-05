<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enterprise;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
class EnterpriseAdminController extends Controller
{
    public function index()
    {
        $enterprises = Enterprise::all();
     
        return view('backend.pages.enterprise.index', compact('enterprises'));
    }

    public function approve($id)
    {
        $enterprise = Enterprise::findOrFail($id);
        $enterprise->update(['status' => 1, 'rejection_reason' => null]);
            \DB::table('users')
        ->where('id', $enterprise->user_id)
        ->update(['is_company' => 1]);
        // Send Approval Email
        Mail::to($enterprise->enterprise_email)->send(new \App\Mail\EnterpriseApprovedMail($enterprise));
         toastr_success(__('Enterprise Approved Successfully!'));
        return redirect()->back();
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required'
        ]);

        $enterprise = Enterprise::findOrFail($id);
        $enterprise->update(['status' => 2, 'rejection_reason' => $request->rejection_reason]);

        // Send Rejection Email
        Mail::to($enterprise->enterprise_email)->send(new \App\Mail\EnterpriseRejectedMail($enterprise,$request->rejection_reason));
            toastr_error(__('Enterprise Rejected Successfully!'));
        return redirect()->back();
    }
}

?>