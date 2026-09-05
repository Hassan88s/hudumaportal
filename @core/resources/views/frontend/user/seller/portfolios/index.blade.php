@extends('frontend.user.buyer.buyer-master')
@section('site-title')
    {{__('Manage Portfolio')}}
@endsection
@section('style')
    <x-media.css/>
    <style>
        img.no-image {
            width: 119px;
        }
    </style>
@endsection
@section('content')
    <x-frontend.seller-buyer-preloader/>
    @php $default_lang = get_default_language(); @endphp
            <!-- Dashboard area Starts -->
    @include('frontend.user.seller.partials.sidebar-two')
    <div class="dashboard__right">
        <!-- buyer header -->
        @include('frontend.user.buyer.header.buyer-header')
        <div class="dashboard__body">
            <div class="dashboard__inner">
               
 <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboard__headerContents__title">
                              
                           
                        </div>
                        <div class="btn-wrapper">
                          
                               <a href="{{ route('seller.portfolio.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> {{ __('Add Portfolio') }}
</a>

                        </div>
                        
                        
           
                    </div>
                </div>
                
      @if (count($errors) > 0)
         <div class = "alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
      @endif
                   <div class="dashboard__headerContents">
                    <div class="dashboard__headerContents__flex">
                        <div class="dashboard__headerContents__left">
                            <h4 class="dashboards-title"><strong>{{__('All Portfolio')}}</strong></h4>
                           
                        </div>
                    </div>
                </div>
  
               @if($portfolios->count() >= 1)
                    <div class="dashboard_table__wrapper dashboard_border padding-20 radius-10 bg-white">
                        <div class="dashboard_table__main custom--table mt-4">
                            <table>
                                <thead>
                                <tr>
                                  
                               
                                
                                    <th> {{ __('Project Title') }} </th>
                                    <th> {{ __('Portfolio Description') }} </th>
                                    <th> {{ __('Action') }} </th>
                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($portfolios as $portfolio)
                                    <tr>
                                      
                                      
                                        <td data-label="{{ __('Job Title') }}"> {{ Str::limit($portfolio->name,50) }} 
                                        <td data-label="{{ __('Description') }}">  
                                       {!! $portfolio->description !!}
                                        </td>
                                        <td>
                                           <a href="{{ route('seller.portfolio.edit', $portfolio->id) }}" class="btn btn-primary">{{ __('Edit')}}</a>
                                          <form action="{{ route('seller.portfolio.delete', $portfolio->id) }}" method="POST" onsubmit="return confirm('Are you sure?')" class="pt-2">
                                        @csrf
                                        <button type="submit" class="btn btn-danger"> {{ __('Delete')}}</button>
                                    </form>
                                        </td>
                                       
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="blog-pagination margin-top-55">
                            <div class="custom-pagination mt-4 mt-lg-5">
                             
                            </div>
                        </div>

                    </div>
                @else
                    <div class="chat_wrapper__details__inner__chat__contents">
                        <p class="no_data_found_for_buyer_seller_panel">
                            {{ __('No Portfolio Found')}}
                        </p>
                    </div>
                @endif

            </div>
        </div>
   
        </div>
    </div>
    </div>
    <!-- Buyer Profile Edit Modal End-->
    <x-media.markup :type="'web'"/>
@endsection
@section('scripts')
    <x-media.js :type="'web'"/>
    <script src="{{asset('assets/backend/js/sweetalert2.js')}}"></script>
  


@endsection
