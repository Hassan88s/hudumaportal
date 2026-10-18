@extends('backend.admin-master')
@section('site-title')
    {{__('Add New Role')}}
@endsection
@section('style')
    <style>
        .perm-group{border:1px solid #e9edf2;border-radius:8px;margin-bottom:18px;overflow:hidden;}
        .perm-group__head{display:flex;align-items:center;justify-content:space-between;background:#f6f8fb;padding:12px 16px;border-bottom:1px solid #e9edf2;}
        .perm-group__head h5{margin:0;font-size:15px;font-weight:600;}
        .perm-group__count{font-size:12px;color:#8a94a6;margin-left:8px;font-weight:400;}
        .perm-group__body{padding:14px 16px;}
        .perm-item{margin-bottom:10px;}
        .perm-item label.caption{display:block;font-size:13px;margin-bottom:4px;}
    </style>
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <h4 class="header-title">{{__('New Role')}}</h4>
                            <div class="btn-wrapper">
                                <a href="{{route('admin.all.admin.role')}}" class="btn btn-info">{{__('All Roles')}}</a>
                            </div>
                        </div>
                        <x-msg.error/>
                        <x-msg.success/>
                        <form action="{{route('admin.role.new')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="name">{{__('Name')}}</label>
                                <input type="text" class="form-control"  id="name" name="name" value="{{ old('name') }}" placeholder="{{__('Enter name')}}">
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <p class="mb-0 text-muted">{{__('Choose what this role is allowed to do.')}}</p>
                                <button type="button" class="btn btn-xs btn-outline-dark checked_all">{{__('Toggle all permissions')}}</button>
                            </div>

                            <div class="checkbox-wrapper">
                                @foreach($permissionGroups as $label => $permissions)
                                    @php $gid = 'g'.$loop->index; @endphp
                                    <div class="perm-group">
                                        <div class="perm-group__head">
                                            <h5>{{ __($label) }} <span class="perm-group__count">{{ $permissions->count() }}</span></h5>
                                            <label class="switch mb-0" title="{{__('Select all in this group')}}">
                                                <input type="checkbox" class="group-check-all" data-group="{{ $gid }}">
                                                <span class="slider-yes-no"></span>
                                            </label>
                                        </div>
                                        <div class="perm-group__body">
                                            <div class="row">
                                                @foreach($permissions as $permission)
                                                    <div class="col-lg-3 col-md-4 col-6">
                                                        <div class="perm-item">
                                                            <label class="caption"><strong>{{ucfirst(str_replace('-',' ',$permission->name))}}</strong></label>
                                                            <label class="switch mb-0">
                                                                <input type="checkbox" class="group-perm {{ $gid }}" name="permission[]" value="{{$permission->id}}" {{ in_array($permission->id, (array) old('permission', [])) ? 'checked' : '' }}>
                                                                <span class="slider-yes-no"></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <button type="submit" class="btn btn-primary mt-2 pr-4 pl-4">{{__('Submit')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function (){
           "use strict";

           // Global toggle-all
           $(document).on('click','.checked_all',function (){
              var boxes = $('.checkbox-wrapper input[type="checkbox"].group-perm');
              var makeChecked = boxes.filter(':checked').length < boxes.length;
              boxes.prop('checked', makeChecked);
              $('.group-check-all').prop('checked', makeChecked);
           });

           // Per-group toggle-all
           $(document).on('change','.group-check-all',function (){
              var gid = $(this).data('group');
              $('.group-perm.' + gid).prop('checked', $(this).is(':checked'));
           });

           // Keep a group's header checkbox in sync with its items
           $(document).on('change','.group-perm',function (){
              var gid = $(this).attr('class').split(/\s+/).filter(function(c){ return c.charAt(0) === 'g' && !isNaN(c.slice(1)); })[0];
              if (!gid) return;
              var items = $('.group-perm.' + gid);
              $('.group-check-all[data-group="' + gid + '"]').prop('checked', items.length === items.filter(':checked').length);
           });
        });
    </script>
@endsection
