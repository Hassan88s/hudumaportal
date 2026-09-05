@extends('backend.admin-master')
@section('site-title')
    {{__('Company Request')}}
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
                                <h4 class="header-title">{{__('Manage Company Request')}} </h4>
                                
                                <table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Business Type</th>
            <th>Industry</th>
             <th>Enterprise Email</th>
              <th>Enterprise Number</th>
               <th>Website</th>
                 <th>Office Address</th>
                   <th>Representative Name</th>
                     <th>Representative Position</th>
                       <th>Representative Email</th>
                         <th>Representative Phone</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($enterprises as $enterprise)
        <tr>
            <td>{{ $enterprise->name }}</td>
            <td>{{ $enterprise->description }}</td>
            <td>{{ $enterprise->business_type }}</td>
             <td>{{ $enterprise->industry }}</td>
              <td>{{ $enterprise->enterprise_email }}</td>
              <td>{{ $enterprise->phone_number }}</td>
              <td>{{ $enterprise->website }}</td>
              <td>{{ $enterprise->office_address }}</td>
              <td>{{ $enterprise->representative_name }}</td>
              <td>{{ $enterprise->representative_position }}</td>
             <td>{{ $enterprise->representative_email }}</td>
              <td>{{ $enterprise->representative_phone }}</td>
            <td>
                @if($enterprise->status == 0)
                    <span class="badge bg-warning">Pending</span>
                @elseif($enterprise->status == 1)
                    <span class="badge bg-success">Approved</span>
                @elseif($enterprise->status == 2)
                    <span class="badge bg-danger">Rejected</span>
                @endif
            </td>
            <td>
                @if($enterprise->status == 0)
                    <form action="{{ route('admin.enterprise.approve', $enterprise->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>

                   <button class="btn btn-danger btn-sm" onclick="openRejectModal({{ $enterprise->id }})">Reject</button>

                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

                            </div>
                        </div>
                        <div class="table-wrap table-responsive">
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Enter Rejection Reason</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="enterpriseId" name="enterprise_id">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason</label>
                        <textarea class="form-control" name="rejection_reason" id="rejection_reason" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" onclick="closeModal()">Close</button>

                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <script>
    function openRejectModal(id) {
        document.getElementById('enterpriseId').value = id;
        document.getElementById('rejectForm').action = "/admin-home/enterprise-reject/" + id;
        var rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
        rejectModal.show();
    }
</script><script>
    function closeModal() {
        var modal = document.getElementById('rejectModal');
        var modalInstance = new bootstrap.Modal(modal);
        modalInstance.hide();
    }
</script>


@endsection
