@extends('adminlte::page')

@section('title', 'Edit Content Plan - Step ' . $step)

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Edit Content Plan - Step {{ $step }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contentPlan.index') }}">Content Production</a></li>
                <li class="breadcrumb-item active">Edit Step {{ $step }}</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Progress Bar -->
            <div class="card">
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: {{ ($step / 6) * 100 }}%">
                            Step {{ $step }} of 6
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-2">
                            <div class="step {{ $step >= 1 ? 'active' : '' }}">
                                <span class="badge {{ $step >= 1 ? 'badge-primary' : 'badge-secondary' }}">1</span>
                                <br><small>Strategy</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ $step >= 2 ? 'active' : '' }}">
                                <span class="badge {{ $step >= 2 ? 'badge-primary' : 'badge-secondary' }}">2</span>
                                <br><small>Content Writing</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ $step >= 3 ? 'active' : '' }}">
                                <span class="badge {{ $step >= 3 ? 'badge-primary' : 'badge-secondary' }}">3</span>
                                <br><small>Admin Support</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ $step >= 4 ? 'active' : '' }}">
                                <span class="badge {{ $step >= 4 ? 'badge-primary' : 'badge-secondary' }}">4</span>
                                <br><small>Creative Review</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ $step >= 5 ? 'active' : '' }}">
                                <span class="badge {{ $step >= 5 ? 'badge-primary' : 'badge-secondary' }}">5</span>
                                <br><small>Content Editing</small>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="step {{ $step >= 6 ? 'active' : '' }}">
                                <span class="badge {{ $step >= 6 ? 'badge-primary' : 'badge-secondary' }}">6</span>
                                <br><small>Store to Content Bank</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step Content -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        @switch($step)
                            @case(1)
                                Step 1: Social Media Strategist - Strategy & Platform Selection
                                @break
                            @case(2)
                                Step 2: Content Writer - Content Creation
                                @break
                            @case(3)
                                Step 3: Admin Support - Booking & Resource Management
                                @break
                            @case(4)
                                Step 4: Creative Leader - Review & Approval
                                @break
                            @case(5)
                                Step 5: Content Editor - Final Editing
                                @break
                            @case(6)
                                Step 6: Admin Social Media - Store to Content Bank
                                @break
                        @endswitch
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('contentPlan.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <form action="{{ route('contentPlan.updateStep', [$contentPlan, $step]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        @switch($step)
                            @case(1)
                                @include('admin.content_plan.steps.step1', ['contentPlan' => $contentPlan])
                                @break
                            @case(2)
                                @include('admin.content_plan.steps.step2', ['contentPlan' => $contentPlan])
                                @break
                            @case(3)
                                @include('admin.content_plan.steps.step3', ['contentPlan' => $contentPlan])
                                @break
                            @case(4)
                                @include('admin.content_plan.steps.step4', ['contentPlan' => $contentPlan])
                                @break
                            @case(5)
                                @include('admin.content_plan.steps.step5', ['contentPlan' => $contentPlan])
                                @break
                            @case(6)
                                @include('admin.content_plan.steps.step6', ['contentPlan' => $contentPlan])
                                @break
                        @endswitch
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            @if($step < 6)
                                Complete Step {{ $step }} & Continue
                            @else
                                Complete & Store to Content Bank
                            @endif
                        </button>
                        <a href="{{ route('contentPlan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
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
</style>
@stop