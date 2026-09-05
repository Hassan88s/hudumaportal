@extends('backend.admin-master')
@section('site-title')
    {{__('Refferal Settings')}}
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                @include('backend.partials.message')
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">{{__("Refferal Settings")}}</h4>
                        @if($errors->any())
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger">{{$error}}</div>
                             @endforeach
                        @endif
                        <form action="{{route('admin.general.update.refferal')}}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="order_mail_success_message">{{__('Set Reward For Sign Up')}}</label>
                                <input type="text" name="sign_up_points"  class="form-control" value="{{get_static_option('sign_up_points')}}" >
                               
                            </div>
                            <div class="form-group">
                                <label for="contact_mail_success_message">{{__('Set Reward For First Service Creation')}}</label>
                                <input type="text" name="first_order_points"  class="form-control" value="{{get_static_option('first_order_points')}}" >
                               
                            </div>
                              <div class="form-group">
                                <label for="contact_mail_success_message">{{__('Set Reward For First Purchase')}}</label>
                                <input type="text" name="first_purchase_points"  class="form-control" value="{{get_static_option('first_purchase_points')}}" >
                               
                            </div>
                            <button id="update" type="submit" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        <x-btn.update/>
    </script>
@endsection
