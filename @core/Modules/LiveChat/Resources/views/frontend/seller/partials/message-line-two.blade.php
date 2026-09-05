@if($message->from_user == \Auth::user()->id)
    <!--reply -->
    
    <div class="chat_wrapper__details__inner__chat chat-reply base_receive" data-message-id="{{ $message->id }}" id="message-line-{{$message->id}}">
        <div class="chat_wrapper__details__inner__chat__flex">
            <div class="chat_wrapper__details__inner__chat__thumb">
                {!! render_image_markup_by_attachment_id(optional($message->fromUser)->image) !!}
            </div>
            <div class="chat_wrapper__details__inner__chat__contents messages msg_receive">
                @if($message->message)
                    <p class="chat_wrapper__details__inner__chat__contents__para"> {!! linkify_message($message->message) !!} 
                    <??>
                    
                    </p>
                @endif
                 <br>
                 @if($message->offermessage)
                    
                    
                    <?php $new= json_decode($message->offermessage); 
                    
                  
                    ?>
                   <div class="card chat_wrapper__details__inner__chat__contents__para">
  <div class="card-body">
      <h2 style="font-family:emoji">Here's your Custom Offer</h2>
    <h5 class="card-title">{{ $new->title }}</h5>
    <h6 class="card-subtitle mb-2 ">Price : {{ float_amount_with_currency_symbol($new->price) }}</h6>
      <h6 class="card-subtitle mb-2 ">Delivery :{{$new->end_date  }}Days</h6>
    <p class="card-text">Description : {{ $new->description}}</p>
    <a href="{{ route('seller.job.withdrwa',$new->id) }}" class="card-link btn btn-danger">Withdrwal offer</a>
    <!--<a href="#" class="card-link">Another link</a>-->
  </div>
</div>
                    
                    </p>
                @endif
                <br>
                @if($message->image)
                    <div class="width-100 chat_wrapper__details__inner__chat__contents__para mt-2">
                        <img class="img-responsive" src="{{asset(('assets/uploads/chat_image/'.$message->image))}}" />
                    </div>
                @endif
                <span class="chat_wrapper__details__inner__chat__contents__time mt-2"
                      datetime="{{ date("Y-m-dTH:i", strtotime($message->created_at->toDateTimeString())) }}">
                        {{ optional($message->fromUser)->username }} • {{ $message->created_at->diffForHumans() }}
                    </span>
            </div>
        </div>
    </div>
@else
    <div class="chat_wrapper__details__inner__chat base_sent" data-message-id="{{ $message->id }}" id="message-line-{{$message->id}}">
        <div class="chat_wrapper__details__inner__chat__flex">
            <div class="chat_wrapper__details__inner__chat__thumb">
                {!! render_image_markup_by_attachment_id(optional($message->fromUser)->image) !!}
            </div>
            <div class="chat_wrapper__details__inner__chat__contents messages msg_sent">

                @if($message->message)
                    <p class="chat_wrapper__details__inner__chat__contents__para">{!! linkify_message($message->message) !!}</p>
                @endif
                <br>
                @if($message->image)
                    <div class="chat_sent_image chat_wrapper__details__inner__chat__contents__para mt-2">
                    <img src="{{ asset(('assets/uploads/chat_image/'.$message->image)) }}" >
                    </div>
                @endif
                <span class="chat_wrapper__details__inner__chat__contents__time mt-2"
                      datetime="{{ date("Y-m-dTH:i", strtotime($message->created_at->toDateTimeString())) }}">
                    {{ optional($message->fromUser)->username }} • {{ $message->created_at->diffForHumans() }}
                </span>

            </div>
            <!-- sent image load -->
        </div>
    </div>
@endif