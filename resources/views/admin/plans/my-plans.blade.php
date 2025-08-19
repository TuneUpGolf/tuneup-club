@php
    use Carbon\Carbon;
@endphp
@extends('layouts.main')
@section('title', __('My Plans'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('My Plans') }}</li>
@endsection
@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    {{ $dataTable->table(['width' => '100%']) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Buyers Modal -->
<div class="modal fade" id="buyersModal" tabindex="-1" aria-labelledby="buyersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="buyersModalLabel">Plan Buyers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Plan Expiry</th>
                    </tr>
                </thead>
                <tbody id="buyersTableBody">
                    <tr>
                        <td colspan="4" class="text-center">No data</td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>
@endsection
@push('css')
    @include('layouts.includes.datatable_css')
@endpush
@push('javascript')
    @include('layouts.includes.datatable_js')
    {{ $dataTable->scripts() }}
    <script>
        document.addEventListener('click', function(e) {
            var target = e.target;
            if (target && target.classList.contains('js-plan-buyers')) {
                e.preventDefault();
                var url = target.getAttribute('data-url');

                // Clear table body and show loading
                var tbody = document.getElementById('buyersTableBody');
                tbody.innerHTML = '<tr><td colspan="4" class="text-center">Loading...</td></tr>';

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                  .then(function(resp){ return resp.json(); })
                  .then(function(json){
                    var rows = '';
                    if (json && Array.isArray(json.data) && json.data.length) {
                        json.data.forEach(function(buyer, idx){
                            rows += '<tr>'+
                                    '<td>'+(idx+1)+'</td>'+
                                    '<td>'+ (buyer.name ?? '-') +'</td>'+
                                    '<td>'+ (buyer.email ?? '-') +'</td>'+
                                    '<td>'+ (buyer.plan_expired_date ?? '-') +'</td>'+
                                '</tr>';
                        });
                    } else {
                        rows = '<tr><td colspan="4" class="text-center">No buyers found</td></tr>';
                    }
                    tbody.innerHTML = rows;
                    var modal = new bootstrap.Modal(document.getElementById('buyersModal'));
                    modal.show();
                  })
                  .catch(function(){
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Failed to load buyers</td></tr>';
                    var modal = new bootstrap.Modal(document.getElementById('buyersModal'));
                    modal.show();
                  });
            }
        });
    </script>
@endpush
