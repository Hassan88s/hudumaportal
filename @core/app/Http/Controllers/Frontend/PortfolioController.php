<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Portfolio;
use App\PortfolioImage;
use Illuminate\Support\Facades\Validator;
class PortfolioController extends Controller
{
    
    public function index(){
        $portfolios = Portfolio::where('freelancer_id', auth()->id())->get();
    return view('frontend.user.seller.portfolios.index', compact('portfolios'));
    }
    
    public function create()
    {
        return view('frontend.user.seller.portfolios.create');
    }



public function store(Request $request)
{
   
   
    $user = auth()->user();
    $subscription = \Modules\Subscription\Entities\SellerSubscription::where('seller_id', $user->id)
                        ->latest('id') // or latest('created_at')
                        ->first();
                        if (!$subscription) {
        return redirect()->back()->with('error', 'You need a subscription to add portfolios.');
    }

    $portfolioCount = Portfolio::where('freelancer_id', $user->id)->count();

    if ($portfolioCount >= $subscription->initialprojects_allowed) {
       
         toastr_error(__('You have reached your portfolio limit. Please upgrade your package to add more.'));
                        return redirect()->back();
    }
   

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|required|max:5120', // 5 MB = 5120 KB
        'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:30720', // 30 MB = 30720 KB
                
    ]);


    if ($validator->fails()) {
        // Handle validation error (log, custom message, etc.)
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    // No errors, proceed with saving
    $portfolio = new Portfolio();
    $portfolio->freelancer_id = auth()->id();
    $portfolio->name = $request->name;
    $portfolio->description = $request->description;
     $portfolio->project_cost = $request->cost;
      $portfolio->timeline = $request->Duration;

    if ($request->hasFile('video')) {
        $filename = time() . '_' . $request->file('video')->getClientOriginalName();
        $portfolio->video = 'portfolios/videos/' . $filename;
        $request->file('video')->move(public_path('portfolios/videos'), $filename);
    }

    $portfolio->save();

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('portfolios/images'), $imageName);

            PortfolioImage::create([
                'portfolio_id' => $portfolio->id,
                'image' => 'portfolios/images/' . $imageName,
            ]);
        }
    }

    toastr_success(__('Portfolio created successfully!'));
    return redirect()->route('seller.portfolio.all');
}


    public function edit($id)
    {
        $portfolio = Portfolio::with('images')->where('freelancer_id', auth()->id())->findOrFail($id);
        return view('frontend.user.seller.portfolios.edit', compact('portfolio'));
    }

    public function update(Request $request, $id)
    {
        $portfolio = Portfolio::where('freelancer_id', auth()->id())->findOrFail($id);

        $portfolio->update([
            'name' => $request->name,
            'description' => $request->description,
            'project_cost' => $request->cost,
            'timeline' => $request->Duration,
                 
        ]);

        // if ($request->hasFile('video')) {
        //     if ($portfolio->video) {
        //         \Storage::disk('public')->delete($portfolio->video);
        //     }
        //     $portfolio->video = $request->file('video')->store('portfolios/videos', 'public');
        // }
        if ($request->hasFile('video')) {
    if ($portfolio->video) {
     
        $oldVideoPath = public_path($portfolio->video);
        if (file_exists($oldVideoPath)) {
            unlink($oldVideoPath);
        }
    }

    $destinationPath = public_path('portfolios/videos');
    $fileName = time() . '_' . $request->file('video')->getClientOriginalName();
    $request->file('video')->move($destinationPath, $fileName);
    $portfolio->video = 'portfolios/videos/' . $fileName;
}


        $portfolio->save();

        if ($request->hasFile('images')) {
    foreach ($request->file('images') as $image) {
        $imageName = time() . '_' . $image->getClientOriginalName();

       
        $image->move(public_path('portfolios/images'), $imageName);

      
        PortfolioImage::create([
            'portfolio_id' => $portfolio->id,
            'image' => 'portfolios/images/' . $imageName,
        ]);
    }
}

        
        toastr_success(__('Portfolio updated successfully!'));
        return redirect()->route('seller.portfolio.all');
        
    }

    public function destroy($id)
    {
        $portfolio = Portfolio::where('freelancer_id', auth()->id())->findOrFail($id);

        if ($portfolio->video) {
            \Storage::disk('public')->delete($portfolio->video);
        }

        foreach ($portfolio->images as $image) {
            \Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $portfolio->delete();
             toastr_warning(__('Portfolio deleted successfully!'));
        return redirect()->route('seller.portfolio.all');
    }
    
    public function singleportfolio($id){
       
         $portfolio = Portfolio::with('images')->findOrFail($id);
            return view('frontend.pages.portfolios.index',compact('portfolio'));
    }
    
        public function deleteImage($id)
{
  
    $image = PortfolioImage::find($id);

    if (!$image) {
        return response()->json([
            'success' => false,
            'message' => 'Image not found.',
        ]);
    }

    $imagePath = public_path($image->image);
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    $image->delete();

    return response()->json([
        'success' => true,
        'message' => 'Image deleted successfully!',
    ]);
}



}

?>