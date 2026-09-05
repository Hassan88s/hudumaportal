<?php

namespace App\Http\Controllers\Frontend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Project;
use App\Service;
use App\ProjectImage;
use Illuminate\Support\Facades\Validator;
use Auth;
use Illuminate\Support\Str;
use App\Category;
class ProjectController extends Controller
{
    
    public function index(){
    $projects = Project::where('freelancer_id', auth()->id())->get();
    return view('frontend.user.seller.projects.index', compact('projects'));
    }
    
    
  
    public function create()
{
     $categories = Category::where('status', 1)->get();
    $check = Service::where('seller_id', Auth::id())->exists(); // fixed 'exist()' to 'exists()'
    
    if ($check) {
        return view('frontend.user.seller.projects.create',compact('categories'));
    } else {
        toastr_error(__('Please upload a service first to continue.'));
        return redirect()->route('seller.project.all');
    }
}



public function store(Request $request)
{
   

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'required|string',
        'cate_id'  => 'required',
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
    $portfolio = new Project();
    $portfolio->freelancer_id = auth()->id();
     $portfolio->cate_id =  $request->cate_id;
    $portfolio->slug = generateUniqueSlug($request->name);
    $portfolio->name = $request->name;
    $portfolio->description = $request->description;

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

            ProjectImage::create([
                'portfolio_id' => $portfolio->id,
                'image' => 'portfolios/images/' . $imageName,
            ]);
        }
    }

    toastr_success(__('Project created successfully!'));
    return redirect()->route('seller.project.all');
}




    public function edit($id)
    {
        $categories = Category::where('status', 1)->get();
        $portfolio = Project::with('images')->where('freelancer_id', auth()->id())->findOrFail($id);
        return view('frontend.user.seller.projects.edit', compact('portfolio','categories'));
    }

    public function update(Request $request, $id)
    {
        $portfolio = Project::where('freelancer_id', auth()->id())->findOrFail($id);

        $portfolio->update([
            'name' => $request->name,
            'description' => $request->description,
            'cate_id'=> $request->cate_id,
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

      
        ProjectImage::create([
            'portfolio_id' => $portfolio->id,
            'image' => 'portfolios/images/' . $imageName,
        ]);
    }
}

        
        toastr_success(__('Project updated successfully!'));
        return redirect()->route('seller.project.all');
        
    }

    public function destroy($id)
    {
        $portfolio = Project::where('freelancer_id', auth()->id())->findOrFail($id);

        if ($portfolio->video) {
            \Storage::disk('public')->delete($portfolio->video);
        }

        foreach ($portfolio->images as $image) {
            \Storage::disk('public')->delete($image->image);
            $image->delete();
        }

        $portfolio->delete();
             toastr_warning(__('Project deleted successfully!'));
        return redirect()->route('seller.project.all');
    }
    
    public function singleportfolio($id){
       
         $portfolio = Project::with('images')->findOrFail($id);
            return view('frontend.pages.projects.index',compact('portfolio'));
    }
    
        public function deleteImage($id)
{
  
    $image = ProjectImage::find($id);

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