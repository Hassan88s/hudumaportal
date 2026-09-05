@extends('backend.admin-master')
@section('site-title')
    {{__('Prmoted Service Request')}}
@endsection

@section('style')
<x-datatable.css/>
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-msg.success/>
                <x-msg.error/>
            </div>
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <div class="left-content">
                                <h4 class="header-title">{{__('Prmoted Service Request')}}  </h4>
                                @can('brand-delete')
                                  <x-bulk-action/>
                                @endcan
                            </div>
                         
                        </div>
                        <div class="table-wrap table-responsive">
                            <table class="table table-default">
                                <thead>
                                <th class="no-sort">
                                    <div class="mark-all-checkbox">
                                        <input type="checkbox" class="all-checkbox">
                                    </div>
                                </th>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Title')}}</th>
                                <th>{{__('month')}}</th>
                                <th>{{__('Payment Status')}}</th>
                                <th>{{__('Payment Gateway')}}</th>
                                <th>{{__('Featured')}}</th>
                                <th>{{__('Created date')}}</th>
                                </thead>
                                <tbody>
                                    @foreach($brands as $data)
                                        <tr>
                                            <td>
                                                <x-bulk-delete-checkbox :id="$data->id"/>
                                            </td>
                                            <td>{{$data->id}}</td>
                                            <td>{{$data->title}}</td>
                                            <td>
                                              {{$data->month}}
                                            </td>
                                            <td>
                                              {{$data->Payment_status}}
                                                 <a class="btn btn-success mb-3" href="{{ route('admin.promotedpayment.edit',$data->service_id) }}"><i class="ti-pencil"></i></a>
                                            </td>
                                            <td>
                                              {{$data->Payment_gatway}}
                                            </td>
                                            <td>
                                                @if($data->featured==1)
                                             <span class='btn btn-warning'>Prmoted</span>
                                              @else
                                               <span class='btn btn-warning'>Not Prmoted</span>
                                              @endif
                                            </td>
                                            <td>{{date('d-m-Y', strtotime($data->created_at))}}</td>
                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
 <x-datatable.js/>
    <script type="text/javascript">
        (function(){
            "use strict";
            $(document).ready(function(){
                <x-bulk-action-js :url="route('admin.brand.bulk.action')"/>
              });
        })(jQuery);
    </script>
@endsection
