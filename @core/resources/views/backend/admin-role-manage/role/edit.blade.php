@extends('backend.admin-master')
@section('site-title')
    {{__('Edit Role')}}
@endsection
@section('style')
    <style>
        .perm-toolbar{display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;margin:6px 0 20px;}
        .perm-toolbar .perm-search{flex:1;min-width:220px;max-width:360px;}
        .perm-count-pill{font-size:13px;font-weight:600;padding:6px 14px;border-radius:999px;border:1px solid rgba(135,138,160,.35);opacity:.9;white-space:nowrap;}
        .perm-group{border:1px solid rgba(135,138,160,.28);border-radius:10px;margin-bottom:16px;overflow:hidden;}
        .perm-group__head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;background:rgba(135,138,160,.10);border-bottom:1px solid rgba(135,138,160,.22);}
        .perm-group__title{display:flex;align-items:center;gap:10px;font-size:15px;font-weight:700;color:inherit;}
        .perm-group__count{font-size:12px;font-weight:600;opacity:.6;}
        .perm-group__all{display:flex;align-items:center;gap:8px;font-size:12px;opacity:.85;cursor:pointer;margin:0;}
        .perm-group__body{padding:14px 16px 6px;}
        .perm-item{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:8px 12px;margin-bottom:10px;border:1px solid rgba(135,138,160,.18);border-radius:8px;}
        .perm-item .cap{font-size:13px;font-weight:600;color:inherit;opacity:.95;}
        .perm-item .switch{margin:0;flex:none;}
        .perm-savebar{position:sticky;bottom:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:18px;padding:14px 16px;border-top:1px solid rgba(135,138,160,.28);background:rgba(135,138,160,.06);backdrop-filter:blur(2px);}
    </style>
@endsection
@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <h4 class="header-title">{{__('Edit Role')}}</h4>
                            <div class="btn-wrapper">
                                <a href="{{route('admin.all.admin.role')}}" class="btn btn-info">{{__('All Roles')}}</a>
                            </div>
                        </div>
                        <x-msg.error/>
                        <x-msg.success/>
                        <form action="{{route('admin.user.role.update')}}" method="post" id="roleForm">
                            @csrf
                            <input type="hidden" name="id" value="{{$role->id}}">
                            <div class="form-group">
                                <label for="name">{{__('Role Name')}}</label>
                                <input type="text" class="form-control" id="name" value="{{$role->name}}" name="name" placeholder="{{__('Enter name')}}">
                            </div>

                            <div class="perm-toolbar">
                                <input type="text" class="form-control perm-search" id="permSearch" placeholder="🔍 {{__('Search permissions…')}}">
                                <span class="perm-count-pill"><span id="selCount">0</span> / {{ collect($permissionGroups)->flatten()->count() }} {{__('selected')}}</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary checked_all">{{__('Select / clear all')}}</button>
                            </div>

                            <div class="checkbox-wrapper">
                                @foreach($permissionGroups as $label => $permissions)
                                    @php $gid = 'g'.$loop->index; @endphp
                                    <div class="perm-group" data-group-wrap="{{ $gid }}">
                                        <div class="perm-group__head">
                                            <span class="perm-group__title">{{ __($label) }} <span class="perm-group__count">{{ $permissions->count() }}</span></span>
                                            <label class="perm-group__all">
                                                <span>{{__('All')}}</span>
                                                <label class="switch mb-0"><input type="checkbox" class="group-check-all" data-group="{{ $gid }}"><span class="slider-yes-no"></span></label>
                                            </label>
                                        </div>
                                        <div class="perm-group__body">
                                            <div class="row">
                                                @foreach($permissions as $permission)
                                                    <div class="col-lg-4 col-md-6 perm-col">
                                                        <div class="perm-item" data-name="{{ strtolower(str_replace('-',' ',$permission->name)) }}">
                                                            <span class="cap">{{ ucfirst(str_replace('-',' ',$permission->name)) }}</span>
                                                            <label class="switch mb-0">
                                                                <input type="checkbox" class="group-perm {{ $gid }}" name="permission[]" @if(in_array($permission->id,$rolePermissions)) checked @endif value="{{$permission->id}}">
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

                            <div class="perm-savebar">
                                <span class="text-muted">{{__('Update what this role is allowed to do, then save.')}}</span>
                                <button type="submit" class="btn btn-primary pr-4 pl-4">{{__('Update Role')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @include('backend.admin-role-manage.role._script')
@endsection
