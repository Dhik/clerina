@extends('adminlte::page')

@section('title', 'Edit Content Plan')

@section('content_header')
    <div class="row">
        <div class="col-sm-6">
            <h1>Edit Content Plan #{{ $contentPlan->id }}</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('contentPlan.index') }}">Content Production</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit All Fields</h3>
                    <div class="card-tools">
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
                        <span class="badge badge-{{ getStatusBadgeColor($contentPlan->status) }}">
                            {{ $contentPlan->status_label }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('contentPlan.update', $contentPlan) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="form-group">
                                    <label for="created_date">Created Date</label>
                                    <input type="date" class="form-control @error('created_date') is-invalid @enderror" 
                                           id="created_date" name="created_date" 
                                           value="{{ old('created_date', $contentPlan->created_date ? $contentPlan->created_date->format('Y-m-d') : '') }}">
                                    @error('created_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="target_posting_date">Target Posting Date</label>
                                    <input type="date" class="form-control @error('target_posting_date') is-invalid @enderror" 
                                           id="target_posting_date" name="target_posting_date" 
                                           value="{{ old('target_posting_date', $contentPlan->target_posting_date ? $contentPlan->target_posting_date->format('Y-m-d') : '') }}">
                                    @error('target_posting_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status">
                                        @foreach(\App\Domain\ContentPlan\Models\ContentPlan::getStatusOptions() as $key => $label)
                                            <option value="{{ $key }}" {{ old('status', $contentPlan->status) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="objektif">Objektif</label>
                                    <input type="text" class="form-control @error('objektif') is-invalid @enderror" 
                                           id="objektif" name="objektif" value="{{ old('objektif', $contentPlan->objektif) }}" 
                                           placeholder="Enter content objective">
                                    @error('objektif')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="jenis_konten">Jenis Konten</label>
                                    <select class="form-control @error('jenis_konten') is-invalid @enderror" 
                                            id="jenis_konten" name="jenis_konten">
                                        <option value="">Select Content Type</option>
                                        <option value="image" {{ old('jenis_konten', $contentPlan->jenis_konten) == 'image' ? 'selected' : '' }}>Image</option>
                                        <option value="video" {{ old('jenis_konten', $contentPlan->jenis_konten) == 'video' ? 'selected' : '' }}>Video</option>
                                        <option value="carousel" {{ old('jenis_konten', $contentPlan->jenis_konten) == 'carousel' ? 'selected' : '' }}>Carousel</option>
                                        <option value="reel" {{ old('jenis_konten', $contentPlan->jenis_konten) == 'reel' ? 'selected' : '' }}>Reel</option>
                                        <option value="story" {{ old('jenis_konten', $contentPlan->jenis_konten) == 'story' ? 'selected' : '' }}>Story</option>
                                    </select>
                                    @error('jenis_konten')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="pillar">Pillar</label>
                                    <input type="text" class="form-control @error('pillar') is-invalid @enderror" 
                                           id="pillar" name="pillar" value="{{ old('pillar', $contentPlan->pillar) }}" 
                                           placeholder="Enter content pillar">
                                    @error('pillar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="sub_pillar">Sub Pillar</label>
                                    <input type="text" class="form-control @error('sub_pillar') is-invalid @enderror" 
                                           id="sub_pillar" name="sub_pillar" value="{{ old('sub_pillar', $contentPlan->sub_pillar) }}" 
                                           placeholder="Enter sub pillar">
                                    @error('sub_pillar')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="talent">Talent</label>
                                    <input type="text" class="form-control @error('talent') is-invalid @enderror" 
                                           id="talent" name="talent" value="{{ old('talent', $contentPlan->talent) }}" 
                                           placeholder="Enter talent name">
                                    @error('talent')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="venue">Venue</label>
                                    <input type="text" class="form-control @error('venue') is-invalid @enderror" 
                                           id="venue" name="venue" value="{{ old('venue', $contentPlan->venue) }}" 
                                           placeholder="Enter venue location">
                                    @error('venue')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="produk">Produk</label>
                                    <input type="text" class="form-control @error('produk') is-invalid @enderror" 
                                           id="produk" name="produk" value="{{ old('produk', $contentPlan->produk) }}" 
                                           placeholder="Enter product name">
                                    @error('produk')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="referensi">Referensi</label>
                                    <input type="text" class="form-control @error('referensi') is-invalid @enderror" 
                                           id="referensi" name="referensi" value="{{ old('referensi', $contentPlan->referensi) }}" 
                                           placeholder="Enter reference">
                                    @error('referensi')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Platform & Publishing</h5>
                                
                                <div class="form-group">
                                    <label for="platform">Platform</label>
                                    <select class="form-control @error('platform') is-invalid @enderror" 
                                            id="platform" name="platform">
                                        <option value="">Select Platform</option>
                                        <option value="instagram" {{ old('platform', $contentPlan->platform) == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="facebook" {{ old('platform', $contentPlan->platform) == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="tiktok" {{ old('platform', $contentPlan->platform) == 'tiktok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="twitter" {{ old('platform', $contentPlan->platform) == 'twitter' ? 'selected' : '' }}>Twitter</option>
                                        <option value="linkedin" {{ old('platform', $contentPlan->platform) == 'linkedin' ? 'selected' : '' }}>LinkedIn</option>
                                        <option value="youtube" {{ old('platform', $contentPlan->platform) == 'youtube' ? 'selected' : '' }}>YouTube</option>
                                    </select>
                                    @error('platform')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="akun">Akun</label>
                                    <input type="text" class="form-control @error('akun') is-invalid @enderror" 
                                           id="akun" name="akun" value="{{ old('akun', $contentPlan->akun) }}" 
                                           placeholder="Enter account name/handle">
                                    @error('akun')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="kerkun">Kerkun</label>
                                    <input type="text" class="form-control @error('kerkun') is-invalid @enderror" 
                                           id="kerkun" name="kerkun" value="{{ old('kerkun', $contentPlan->kerkun) }}" 
                                           placeholder="Enter kerkun details">
                                    @error('kerkun')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="assignee_content_editor">Assignee Content Editor</label>
                                    <select class="form-control @error('assignee_content_editor') is-invalid @enderror" 
                                            id="assignee_content_editor" name="assignee_content_editor">
                                        <option value="">Select Content Editor</option>
                                        <option value="editor1" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'editor1' ? 'selected' : '' }}>Editor 1</option>
                                        <option value="editor2" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'editor2' ? 'selected' : '' }}>Editor 2</option>
                                        <option value="editor3" {{ old('assignee_content_editor', $contentPlan->assignee_content_editor) == 'editor3' ? 'selected' : '' }}>Editor 3</option>
                                    </select>
                                    @error('assignee_content_editor')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="link_hasil_edit">Link Hasil Edit</label>
                                    <input type="url" class="form-control @error('link_hasil_edit') is-invalid @enderror" 
                                           id="link_hasil_edit" name="link_hasil_edit" value="{{ old('link_hasil_edit', $contentPlan->link_hasil_edit) }}" 
                                           placeholder="https://drive.google.com/...">
                                    @error('link_hasil_edit')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="input_link_posting">Input Link Posting</label>
                                    <input type="url" class="form-control @error('input_link_posting') is-invalid @enderror" 
                                           id="input_link_posting" name="input_link_posting" value="{{ old('input_link_posting', $contentPlan->input_link_posting) }}" 
                                           placeholder="https://instagram.com/p/...">
                                    @error('input_link_posting')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="posting_date">Posting Date</label>
                                    <input type="datetime-local" class="form-control @error('posting_date') is-invalid @enderror" 
                                           id="posting_date" name="posting_date" 
                                           value="{{ old('posting_date', $contentPlan->posting_date ? $contentPlan->posting_date->format('Y-m-d\TH:i') : '') }}">
                                    @error('posting_date')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h5 class="mb-3">Content Details</h5>
                                
                                <div class="form-group">
                                    <label for="hook">Hook</label>
                                    <textarea class="form-control @error('hook') is-invalid @enderror" 
                                              id="hook" name="hook" rows="4" 
                                              placeholder="Enter content hook or main message">{{ old('hook', $contentPlan->hook) }}</textarea>
                                    @error('hook')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="brief_konten">Brief Konten</label>
                                    <textarea class="form-control @error('brief_konten') is-invalid @enderror" 
                                              id="brief_konten" name="brief_konten" rows="6" 
                                              placeholder="Enter detailed content brief...">{{ old('brief_konten', $contentPlan->brief_konten) }}</textarea>
                                    @error('brief_konten')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="caption">Caption</label>
                                    <textarea class="form-control @error('caption') is-invalid @enderror" 
                                              id="caption" name="caption" rows="8" 
                                              placeholder="Write the social media caption...">{{ old('caption', $contentPlan->caption) }}</textarea>
                                    @error('caption')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="link_raw_content">Link Raw Content</label>
                                    <textarea class="form-control @error('link_raw_content') is-invalid @enderror" 
                                              id="link_raw_content" name="link_raw_content" rows="3" 
                                              placeholder="Enter raw content links (Google Drive, Dropbox, etc.)">{{ old('link_raw_content', $contentPlan->link_raw_content) }}</textarea>
                                    @error('link_raw_content')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Content Plan
                        </button>
                        <a href="{{ route('contentPlan.show', $contentPlan) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        <a href="{{ route('contentPlan.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.form-group label {
    font-weight: 600;
    color: #495057;
}

.card-header h5 {
    margin-bottom: 0;
    color: #343a40;
}

.badge {
    font-size: 0.9em;
}

textarea {
    resize: vertical;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.invalid-feedback {
    display: block;
}

.form-text {
    font-size: 0.875em;
}

/* Auto-resize textareas */
textarea.auto-resize {
    overflow: hidden;
    min-height: 80px;
}
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Auto-resize textareas
        function autoResize(element) {
            element.style.height = 'auto';
            element.style.height = element.scrollHeight + 'px';
        }

        // Apply auto-resize to all textareas
        document.querySelectorAll('textarea').forEach(function(textarea) {
            textarea.classList.add('auto-resize');
            
            // Initial resize
            autoResize(textarea);
            
            // Resize on input
            textarea.addEventListener('input', function() {
                autoResize(this);
            });
        });

        // Form validation enhancement
        $('form').on('submit', function(e) {
            let hasErrors = false;
            
            // Check required fields based on status
            const status = $('#status').val();
            
            // You can add custom validation based on status here
            if (status === 'posted' && !$('#input_link_posting').val()) {
                alert('Posted status requires a posting link');
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
            }
        });

        // Show success message if redirected with success
        @if(session('success'))
            toastr.success('{{ session('success') }}');
        @endif

        // Show error message if any
        @if(session('error'))
            toastr.error('{{ session('error') }}');
        @endif

        // Platform selection enhancement
        $('#platform').on('change', function() {
            const platform = $(this).val();
            const akunField = $('#akun');
            
            // Update placeholder based on platform
            switch(platform) {
                case 'instagram':
                    akunField.attr('placeholder', '@username');
                    break;
                case 'facebook':
                    akunField.attr('placeholder', 'Page Name');
                    break;
                case 'tiktok':
                    akunField.attr('placeholder', '@username');
                    break;
                case 'twitter':
                    akunField.attr('placeholder', '@username');
                    break;
                case 'linkedin':
                    akunField.attr('placeholder', 'Company/Profile Name');
                    break;
                case 'youtube':
                    akunField.attr('placeholder', 'Channel Name');
                    break;
                default:
                    akunField.attr('placeholder', 'Enter account name/handle');
            }
        });

        // URL validation for link fields
        $('input[type="url"]').on('blur', function() {
            const url = $(this).val();
            if (url && !isValidUrl(url)) {
                $(this).addClass('is-invalid');
                if (!$(this).next('.invalid-feedback').length) {
                    $(this).after('<div class="invalid-feedback">Please enter a valid URL</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Character counter for caption
        $('#caption').on('input', function() {
            const maxLength = 2200; // Instagram max
            const currentLength = $(this).val().length;
            
            if (!$('.caption-counter').length) {
                $(this).after('<small class="form-text text-muted caption-counter"></small>');
            }
            
            $('.caption-counter').text(`${currentLength}/${maxLength} characters`);
            
            if (currentLength > maxLength) {
                $('.caption-counter').addClass('text-danger').removeClass('text-muted');
            } else {
                $('.caption-counter').addClass('text-muted').removeClass('text-danger');
            }
        });

        // Trigger caption counter on page load
        $('#caption').trigger('input');
    });
</script>
@stop