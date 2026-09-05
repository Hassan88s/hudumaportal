<?php

namespace App\Http\Controllers; 
use App\User;
use App\Project;
use App\Vote;
use Illuminate\Http\Request;
class VoteController extends Controller

{ 
    
    public function vote(Request $request, $projectId)
{
    $user = auth()->user();
    $project = Project::findOrFail($projectId);

    $existingVote = $project->votes()->where('user_id', $user->id)->first();

    if ($existingVote) {
        $existingVote->delete();
        return response()->json(['status' => 'unvoted', 'count' => $project->votes()->count()]);
    }

    $project->votes()->create(['user_id' => $user->id]);
    return response()->json(['status' => 'voted', 'count' => $project->votes()->count()]);
}
    
}

?>