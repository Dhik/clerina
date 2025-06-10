@extends('adminlte::page')

@section('title', 'View Content Plan')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>View Content Plan #{{ $contentPlan->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contentPlan.index') }}">Content Production</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Status and Progress -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Content Plan Status</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $this->getStatusBadgeColor($contentPlan->status) }} badge-lg">
                            {{ $contentPlan->status_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        @php
                            $statusProgress = [
                                'draft' => 16.67,
                                'content_writing' => 33.33,
                                'creative_review' => 50,
                                'admin_support' => 66.67,
                                'content_editing' => 83.33,
                                'ready_to_post' => 100,
                                'posted' => 100
                            ];
                            $progress = $statusProgress[$contentPlan->status] ?? 0;
                        @endphp
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%">
                            {{ number_format($progress, 0) }}% Complete
                        </div>
                    </div>
                    
                    <div class="row text-center">
                        <div class="col-2">
                            <div class="step {{ in_array($contentPlan->status, ['content_writing', 'creative_review', 'admin_support', 'content_editing', 'ready_to_post', 'posted']) ? 'completed' : ($contentPlan->status == 'draft' ? 'active' : '') }}">
                                <span class="badge {{ in_array($contentPlan->status, ['content_writing', 'creative_review', 'admin_support', 'content_editing', 'ready_to_post', 'posted']) ? 'badge-success' : ($contentPlan->status == 'draft' ? 'badge-primary' : 'badge-secondary') }}">
                                    @if(in_array($contentPlan->status, ['content_writing', 'creative_review', 'admin_support', 'content_editing', 'ready_to_post', 'posted']))
                                        <i class="fas fa-check"></i>
                                    @else
                                        1
                                    @endif
                                </span>
                                <br><small>Strategy</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ in_array($contentPlan->status, ['creative_review', 'admin_support', 'content_editing', 'ready_to_post', 'posted']) ? 'completed' : ($contentPlan->status == 'content_writing' ? 'active' : '') }}">
                                <span class="badge {{ in_array($contentPlan->status, ['creative_review', 'admin_support', 'content_editing', 'ready_to_post', 'posted']) ? 'badge-success' : ($contentPlan->status == 'content_writing' ? 'badge-primary' : 'badge-secondary') }}">
                                    @if(in_array($contentPlan->status, ['creative_review', 'admin_support', 'content_editing', 'ready_to_post', 'posted']))
                                        <i class="fas fa-check"></i>
                                    @else
                                        2
                                    @endif
                                </span>
                                <br><small>Content Writing</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ in_array($contentPlan->status, ['admin_support', 'content_editing', 'ready_to_post', 'posted']) ? 'completed' : ($contentPlan->status == 'creative_review' ? 'active' : '') }}">
                                <span class="badge {{ in_array($contentPlan->status, ['admin_support', 'content_editing', 'ready_to_post', 'posted']) ? 'badge-success' : ($contentPlan->status == 'creative_review' ? 'badge-primary' : 'badge-secondary') }}">
                                    @if(in_array($contentPlan->status, ['admin_support', 'content_editing', 'ready_to_post', 'posted']))
                                        <i class="fas fa-check"></i>
                                    @else
                                        3
                                    @endif
                                </span>
                                <br><small>Creative Review</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ in_array($contentPlan->status, ['content_editing', 'ready_to_post', 'posted']) ? 'completed' : ($contentPlan->status == 'admin_support' ? 'active' : '') }}">
                                <span class="badge {{ in_array($contentPlan->status, ['content_editing', 'ready_to_post', 'posted']) ? 'badge-success' : ($contentPlan->status == 'admin_support' ? 'badge-primary' : 'badge-secondary') }}">
                                    @if(in_array($contentPlan->status, ['content_editing', 'ready_to_post', 'posted']))
                                        <i class="fas fa-check"></i>
                                    @else
                                        4
                                    @endif
                                </span>
                                <br><small>Admin Support</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ in_array($contentPlan->status, ['ready_to_post', 'posted']) ? 'completed' : ($contentPlan->status == 'content_editing' ? 'active' : '') }}">
                                <span class="badge {{ in_array($contentPlan->status, ['ready_to_post', 'posted']) ? 'badge-success' : ($contentPlan->status == 'content_editing' ? 'badge-primary' : 'badge-secondary') }}">
                                    @if(in_array($contentPlan->status, ['ready_to_post', 'posted']))
                                        <i class="fas fa-check"></i>
                                    @else
                                        5
                                    @endif
                                </span>
                                <br><small>Content Editing</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ $contentPlan->status == 'posted' ? 'completed' : ($contentPlan->status == 'ready_to_post' ? 'active' : '') }}">
                                <span class="badge {{ $contentPlan->status == 'posted' ? 'badge-success' : ($contentPlan->status == 'ready_to_post' ? 'badge-primary' : 'badge-secondary') }}">
                                    @if($contentPlan->status == 'posted')
                                        <i class="fas fa-check"></i>
                                    @else
                                        6
                                    @endif
                                </span>
                                <br><small>Final Posting</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Details -->
            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Created Date:</strong></td>
                                    <td>{{ $contentPlan->created_date ? $contentPlan->created_date->format('Y-m-d') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Target Posting Date:</strong></td>
                                    <td>{{ $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Objektif:</strong></td>
                                    <td>{{ $contentPlan->objektif ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Konten:</strong></td>
                                    <td>{{ $contentPlan->jenis_konten ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Pillar:</strong></td>
                                    <td>{{ $contentPlan->pillar ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sub Pillar:</strong></td>
                                    <td>{{ $contentPlan->sub_pillar ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Talent:</strong></td>
                                    <td>{{ $contentPlan->talent ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Venue:</strong></td>
                                    <td>{{ $contentPlan->venue ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Produk:</strong></td>
                                    <td>{{ $contentPlan->produk ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Referensi:</strong></td>
                                    <td>{{ $contentPlan->referensi ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Platform & Publishing Info -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Platform & Publishing</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Platform:</strong></td>
                                    <td>{{ $contentPlan->platform ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Akun:</strong></td>
                                    <td>{{ $contentPlan->akun ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kerkun:</strong></td>
                                    <td>{{ $contentPlan->kerkun ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Content Editor:</strong></td>
                                    <td>{{ $contentPlan->assignee_content_editor ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Posting Date:</strong></td>
                                    <td>{{ $contentPlan->posting_date ? $contentPlan->posting_date->format('Y-m-d H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Details -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Content Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><strong>Hook:</strong></h6>
                                    <p class="text-muted">{{ $contentPlan->hook ?? 'No hook provided' }}</p>
                                    
                                    <h6><strong>Brief Konten:</strong></h6>
                                    <p class="text-muted">{{ $contentPlan->brief_konten ?? 'No brief provided' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><strong>Caption:</strong></h6>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0">{{ $contentPlan->caption ?? 'No caption provided' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Links -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Content Links</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6><strong>Raw Content Links:</strong></h6>
                                    @if($contentPlan->link_raw_content)
                                        <div class="bg-light p-2 rounded">
                                            {{ $contentPlan->link_raw_content }}
                                        </div>
                                    @else
                                        <p class="text-muted">No raw content links</p>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <h6><strong>Edited Content Link:</strong></h6>
                                    @if($contentPlan->link_hasil_edit)
                                        <a href="{{ $contentPlan->link_hasil_edit }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-external-link-alt"></i> View Edited Content
                                        </a>
                                    @else
                                        <p class="text-muted">No edited content link</p>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    <h6><strong>Posted Link:</strong></h6>
                                    @if($contentPlan->input_link_posting)
                                        <a href="{{ $contentPlan->input_link_posting }}" target="_blank" class="btn btn-outline-success btn-sm">
                                            <i class="fas fa-external-link-alt"></i> View Post
                                        </a>
                                    @else
                                        <p class="text-muted">Not posted yet</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-footer">
                            <a href="{{ route('contentPlan.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            
                            @if($contentPlan->status == 'draft')
                                <a href="{{ route('contentPlan.editStep', [$contentPlan, 1]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Continue Step 1
                                </a>
                            @elseif($contentPlan->status == 'content_writing')
                                <a href="{{ route('contentPlan.editStep', [$contentPlan, 2]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Continue Step 2
                                </a>
                            @elseif($contentPlan->status == 'creative_review')
                                <a href="{{ route('contentPlan.editStep', [$contentPlan, 3]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Continue Step 3
                                </a>
                            @elseif($contentPlan->status == 'admin_support')
                                <a href="{{ route('contentPlan.editStep', [$contentPlan, 4]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Continue Step 4
                                </a>
                            @elseif($contentPlan->status == 'content_editing')
                                <a href="{{ route('contentPlan.editStep', [$contentPlan, 5]) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Continue Step 5
                                </a>
                            @elseif($contentPlan->status == 'ready_to_post')
                                <a href="{{ route('contentPlan.editStep', [$contentPlan, 6]) }}" class="btn btn-success">
                                    <i class="fas fa-share"></i> Complete Step 6
                                </a>
                            @endif
                            
                            <a href="{{ route('contentPlan.edit', $contentPlan) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit All Fields
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.step {
    margin-bottom: 10px;
}
.step.active {
    color: #007bff;
    font-weight: bold;
}
.step.completed {
    color: #28a745;
    font-weight: bold;
}
.badge-lg {
    font-size: 1em;
    padding: 0.5em 1em;
}
</style>
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