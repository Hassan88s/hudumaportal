<?php


namespace App\Http\Controllers\Frontend;


use App\Category;
use App\BlogComment;
use App\Helpers\FlashMsg;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Service;
use App\Bookmark;
use App\Helpers\HomePageStaticSettings;
use App\Page;
use App\Blog;
use App\HeaderSlider;
use App\Mail\AdminResetEmail;
use App\Order;
use App\Review;

use App\ServiceArea;
use App\StaticOption;
use App\ServiceCity;

class BookmarkController extends Controller
{
    
    public function index()
{
    $user = auth()->user();

   
    $Related_service = $user->bookmarkedServices()
                     ->with('seller')  
                     ->latest()
                     ->get();

    return view('frontend.pages.bookmarks', compact('Related_service'));
}
    
    
    
     public function toggle(Request $request, Service $service)
    {
        $user = $request->user();

        $bookmark = Bookmark::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
            $message = 'Bookmark removed';
        } else {
            Bookmark::create([
                'user_id'    => $user->id,
                'service_id' => $service->id,
            ]);
            $bookmarked = true;
            $message = 'Service bookmarked';
        }

      
        if ($request->ajax()) {
            return response()->json([
                'status'     => 'ok',
                'bookmarked' => $bookmarked,
                'message'    => $message,
            ]);
        }

    
        return back()->with('success', $message);
    }
}
?>