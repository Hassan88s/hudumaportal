<?php

namespace App\Http\Controllers; 
use App\User;
use App\Project;
use App\Vote;
use App\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller

{ 
    
          public function fetch(Project $project)
        {
            $comments = $project->comments()->with(['user', 'replies.user'])->get();
            $html = view('frontend.partials.comments', compact('comments'))->render();
            $count = $comments->count(); // total top-level comments (not replies)

            return response()->json([
                'html' => $html,
                'count' => $count
            ]);
        }
        public function store(Request $request, Project $project)
        {
          
            $request->validate(['content' => 'required']);
            $project->comments()->create([
                'user_id' => auth()->id(),
                'content' => $request->content,
            ]);
            return response()->json(['success' => true]);
        }
        
        public function reply(Request $request, Comment $comment)
        {
            $request->validate(['reply' => 'required']);
            $comment->replies()->create([
                'user_id' => auth()->id(),
                'content' => $request->reply,
                  'project_id' => $comment->project_id,
            ]);
            return response()->json(['success' => true]);
        }
        
                    public function destroy(Comment $comment)
            {
                if (auth()->id() !== $comment->user_id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
            
                $comment->delete();
            
                return response()->json(['success' => true]);
            }

    
}

?>