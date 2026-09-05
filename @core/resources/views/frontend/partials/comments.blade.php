@foreach($comments as $comment)
<div class="mb-3 border-bottom pb-2">
    <strong>{{ $comment->user->username }}</strong>: {{ $comment->content }}
     @if(auth()->id() === $comment->user_id)
        <button class="btn btn-sm btn-danger float-end delete-comment" data-id="{{ $comment->id }}">
            Delete
        </button>
    @endif
    <!-- Load Replies Button -->
    <div>
        <a href="javascript:void(0);" class="text-primary small toggle-replies" data-comment-id="{{ $comment->id }}">
            Show Replies ({{ $comment->replies->count() }})
        </a>
    </div>

    <!-- Replies + Form Container (hidden by default) -->
    <div class="ms-4 text-muted small replies-container" id="replies-{{ $comment->id }}" style="display: none;">
        @foreach($comment->replies as $reply)
            <div><strong>{{ $reply->user->username }}</strong>: {{ $reply->content }}</div>
        @endforeach

        <form method="POST" class="reply-form mt-2" action="{{ route('comments.reply', $comment->id) }}">
            @csrf
            <input type="hidden" name="project_id" value="{{ $comment->project_id }}">
            <input type="text" name="reply" class="form-control" placeholder="Reply..." required>
        </form>
    </div>
</div>
@endforeach
