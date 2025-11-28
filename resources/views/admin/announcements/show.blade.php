@extends('layouts.main')
@section('title', __('Announcement Details'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('announcements.index') }}">{{ __('Announcements') }}</a></li>
    <li class="breadcrumb-item">{{ __('Announcement Details') }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>{{ __('Announcement Details') }}</h5>
                <div class="float-end">
                    @can('edit-announcements')
                    <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-edit me-2"></i>{{ __('Edit') }}
                    </a>
                    @endcan
                    <a href="{{ route('announcements.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left me-2"></i>{{ __('Back') }}
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-3">{{ $announcement->title }}</h4>
                        
                        <!-- <div class="mb-3">
                            <strong>{{ __('Status') }}:</strong>
                            @if($announcement->is_active)
                                <span class="badge bg-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ __('Inactive') }}</span>
                            @endif
                        </div> -->

                        <div class="mb-3">
                            <strong>{{ __('Created By') }}:</strong>
                            {{ $announcement->creator->name ?? 'N/A' }}
                        </div>

                        <!-- <div class="mb-3">
                            <strong>{{ __('Created At') }}:</strong>
                            {{ $announcement->created_at->format('M j, Y g:i A') }}
                        </div>

                        <div class="mb-3">
                            <strong>{{ __('Updated At') }}:</strong>
                            {{ $announcement->updated_at->format('M j, Y g:i A') }}
                        </div> -->

                        <div class="mt-4">
                            <strong>{{ __('Content') }}:</strong>
                            <div class="border p-3 mt-2 rounded bg-light">
                                {!! nl2br(e($announcement->content)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection