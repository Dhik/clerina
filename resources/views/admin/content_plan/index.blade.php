@extends('adminlte::page')

@section('title', 'Content Production')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Content Production</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Content Production</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Content Plan Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('contentPlan.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Content Plan
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter and Search Form -->
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Search by objektif, jenis konten, pillar, platform..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Content Plans Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Target Date</th>
                                    <th>Status</th>
                                    <th>Objektif</th>
                                    <th>Jenis Konten</th>
                                    <th>Pillar</th>
                                    <th>Platform</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($contentPlans as $plan)
                                    <tr>
                                        <td>{{ $plan->id }}</td>
                                        <td>{{ $plan->target_posting_date ? $plan->target_posting_date->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $this->getStatusBadgeColor($plan->status) }}">
                                                {{ $plan->status_label }}
                                            </span>
                                        </td>
                                        <td>{{ $plan->objektif ?? '-' }}</td>
                                        <td>{{ $plan->jenis_konten ?? '-' }}</td>
                                        <td>{{ $plan->pillar ?? '-' }}</td>
                                        <td>{{ $plan->platform ?? '-' }}</td>
                                        <td>{{ $plan->created_at->format('Y-m-d') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('contentPlan.show', $plan) }}" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('contentPlan.edit', $plan) }}" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Step-specific edit buttons -->
                                                @if($plan->status == 'draft')
                                                    <a href="{{ route('contentPlan.editStep', [$plan, 1]) }}" class="btn btn-primary btn-sm" title="Step 1: Strategy">
                                                        <i class="fas fa-clipboard-list"></i> 1
                                                    </a>
                                                @elseif($plan->status == 'content_writing')
                                                    <a href="{{ route('contentPlan.editStep', [$plan, 2]) }}" class="btn btn-primary btn-sm" title="Step 2: Content Writing">
                                                        <i class="fas fa-pen"></i> 2
                                                    </a>
                                                @elseif($plan->status == 'creative_review')
                                                    <a href="{{ route('contentPlan.editStep', [$plan, 3]) }}" class="btn btn-primary btn-sm" title="Step 3: Creative Review">
                                                        <i class="fas fa-check-double"></i> 3
                                                    </a>
                                                @elseif($plan->status == 'admin_support')
                                                    <a href="{{ route('contentPlan.editStep', [$plan, 4]) }}" class="btn btn-primary btn-sm" title="Step 4: Admin Support">
                                                        <i class="fas fa-users-cog"></i> 4
                                                    </a>
                                                @elseif($plan->status == 'content_editing')
                                                    <a href="{{ route('contentPlan.editStep', [$plan, 5]) }}" class="btn btn-primary btn-sm" title="Step 5: Content Editing">
                                                        <i class="fas fa-edit"></i> 5
                                                    </a>
                                                @elseif($plan->status == 'ready_to_post')
                                                    <a href="{{ route('contentPlan.editStep', [$plan, 6]) }}" class="btn btn-primary btn-sm" title="Step 6: Final Posting">
                                                        <i class="fas fa-share"></i> 6
                                                    </a>
                                                @endif
                                                
                                                <form action="{{ route('contentPlan.destroy', $plan) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this content plan?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No content plans found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $contentPlans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    @if(session('success'))
        toastr.success('{{ session('success') }}');
    @endif
    
    @if(session('error'))
        toastr.error('{{ session('error') }}');
    @endif
</script>
@stop

@php
function getStatusBadgeColor($status) {
    switch($status) {
        case 'draft': return 'secondary';
        case 'content_writing': return 'info';
        case 'creative_review': return 'warning';
        case 'admin_support': return 'primary';
        case 'content_editing': return 'dark';
        case 'ready_to_post': return 'success';
        case 'posted': return 'success';
        default: return 'light';
    }
}
@endphp