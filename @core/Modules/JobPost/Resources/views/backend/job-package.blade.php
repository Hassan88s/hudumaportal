@extends('backend.admin-master')
@section('site-title')
    {{__('Job Settings')}}
@endsection
@section('content')
    <div class="col-lg-6 col-ml-12 padding-bottom-30">
        <div class="row">
            <div class="col-lg-12">
                <div class="margin-top-40"></div>
                <x-msg.success/>
                <x-msg.error/>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="header-wrap d-flex justify-content-between">
                            <div class="left-content">
                                <h4 class="header-title">{{__('Manage Job Package Prices')}} </h4>
                               
                            </div>
                        </div>
           
<div class="container mt-5">
    

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.job_packages.update') }}" method="POST" class="mt-4">
        @csrf
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Package Name</th>
                        <th>Price (USD)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                    <tr>
                        <td>{{ $package->name }}</td>
                        <td>
                            <input type="number" step="0.01" name="prices[{{ $package->id }}]" value="{{ $package->price }}" class="form-control" required>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Update Prices</button>
    </form>
</div>



                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection