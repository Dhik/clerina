@extends('adminlte::page')

@section('title', 'Talent Content')

@section('content_header')
    <h1>Talent Content</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTalentContentModal">
                        Add Talent Content
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="row">
                                <div class="col-6">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h4 id="todayCount">0</h4>
                                            <p>Today's Count</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small-box bg-maroon">
                                        <div class="inner">
                                            <h4 id="doneFalseCount">0</h4>
                                            <p>Count Not Done</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h4 id="doneTrueCount">0</h4>
                                            <p>Count Done</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="small-box bg-purple">
                                        <div class="inner">
                                            <h4 id="totalCount">0</h4>
                                            <p>Total Count</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-list"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-3 outer-card">
                                <div class="card-header">
                                    Notification
                                </div>
                                <div class="card-body">
                                    <div class="inner-scrollable" id="todayTalentContainer">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-8">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="talentContentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Talent Name</th>
                                <th>Dealing Upload Date</th>
                                <th>Posting Date</th>
                                <th>Final Rate Card</th>
                                <th>Additional Info</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.talent_content.modals.add_talent_content_modal')
    @include('admin.talent_content.modals.edit_talent_content_modal')
    @include('admin.talent_content.modals.view_talent_content_modal')
@stop


@section('css')
<style>
    .outer-card {
        overflow-y: auto;
        max-height: 400px; 
    }

    .inner-scrollable {
        max-height: 250px; 
        overflow-y: auto;
    }

    .sub-card {
        border: 1px solid #ddd;
    }
</style>
@endsection


@section('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: "{{ route('talent_content.calendar') }}", 
                    method: "GET",
                    success: function(data) {
                        var events = [];
                        $.each(data.data, function(index, item) {
                            if (item.posting_date) {
                                events.push({
                                    title: item.talent_name, 
                                    start: item.posting_date.split('T')[0], 
                                    allDay: true 
                                });
                            }
                        });
                        successCallback(events); 
                    },
                    error: function() {
                        failureCallback(); 
                    }
                });
            }
        });
        calendar.render();
        function fetchContentCounts() {
            $.ajax({
                url: "{{ route('talent_content.count') }}",
                method: "GET",
                success: function(data) {
                    $('#todayCount').text(data.today_count);
                    $('#doneFalseCount').text(data.done_false_count);
                    $('#doneTrueCount').text(data.done_true_count);
                    $('#totalCount').text(data.total_count);
                },
                error: function() {
                    alert('Failed to fetch content counts.');
                }
            });
        }
        fetchContentCounts();
        function fetchTodayTalents() {
            $.ajax({
                url: "{{ route('talent_content.today') }}", 
                method: "GET",
                success: function(data) {
                    var container = $('#todayTalentContainer');
                    container.empty(); 
                    if (data.length) {
                        $.each(data, function(index, talentName) {
                            var subCard = `
                                <div class="sub-card card mb-2">
                                    <div class="card-header">Akun <strong>${talentName}</strong></div>
                                    <div class="card-body">
                                        <p>Harus upload konten hari ini</p>
                                    </div>
                                </div>
                            `;
                            container.append(subCard);
                        });
                    } else {
                        container.append('<p>No talents available for today.</p>');
                    }
                },
                error: function() {
                    alert('Failed to fetch talents for today.');
                }
            });
        }
        fetchTodayTalents();
    });

    $(document).ready(function() {
        var choices;
        
        $('#addTalentContentModal').on('show.bs.modal', function() {
            $.ajax({
                url: "{{ route('talent_content.get') }}",
                method: "GET",
                success: function(data) {
                    var select = $('#talent_id');
                    select.empty();
                    select.append('<option value="">Select Talent</option>');
                    $.each(data, function(index, talent) {
                        select.append('<option value="' + talent.id + '">' + talent.talent_name + '</option>');
                    });

                    if (choices) {
                        choices.destroy();
                    }
                    choices = new Choices(select[0], {
                        searchEnabled: true,
                        placeholder: true,
                        placeholderValue: 'Select Talent'
                    });
                },
                error: function() {
                    alert('Failed to fetch talents.');
                }
            });
        });

        var table = $('#talentContentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('talent_content.data') }}',
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'talent_name', name: 'talents.talent_name' }, 
                {
                    data: 'dealing_upload_date', 
                    name: 'dealing_upload_date',
                    render: function(data, type, row) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                },
                {
                    data: 'posting_date', 
                    name: 'posting_date',
                    render: function(data, type, row) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                },
                { data: 'final_rate_card', name: 'final_rate_card' },
                { data: 'status_and_link', name: 'status_and_link', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        $('#talentContentTable').on('click', '.editButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('talent_content.edit', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#editTalentContentForm').attr('action', '{{ route('talent_content.update', ':id') }}'.replace(':id', id));
                    
                    $('#edit_transfer_date').val(response.talentContent.transfer_date.split('T')[0]);
                    $('#edit_talent_id').val(response.talentContent.talent_id);
                    $('#edit_dealing_upload_date').val(response.talentContent.dealing_upload_date.split('T')[0]);
                    $('#edit_posting_date').val(response.talentContent.posting_date.split('T')[0]);
                    $('#edit_done').val(response.talentContent.done ? 1 : 0);
                    $('#edit_upload_link').val(response.talentContent.upload_link);
                    $('#edit_final_rate_card').val(response.talentContent.final_rate_card);
                    $('#edit_pic_code').val(response.talentContent.pic_code);
                    $('#edit_boost_code').val(response.talentContent.boost_code);
                    $('#edit_kerkun').val(response.talentContent.kerkun ? 1 : 0);
                    
                    $('#editTalentContentModal').modal('show');
                },
                error: function(response) {
                    alert('Error: ' + response.message);
                }
            });
        });

        $('#talentContentTable').on('click', '.viewButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('talent_content.show', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#view_talent_name').text(response.talentContent.talent_name);
                    $('#view_dealing_upload_date').text(response.talentContent.dealing_upload_date);
                    $('#view_posting_date').text(response.talentContent.posting_date);
                    $('#view_final_rate_card').text(response.talentContent.final_rate_card);
                    $('#view_done').text(response.talentContent.done ? 'Yes' : 'No');
                    $('#view_upload_link').text(response.talentContent.upload_link);
                    $('#view_pic_code').text(response.talentContent.pic_code);
                    $('#view_boost_code').text(response.talentContent.boost_code);
                    $('#view_kerkun').text(response.talentContent.kerkun ? 'Yes' : 'No');
                    
                    $('#viewTalentContentModal').modal('show');
                },
                error: function(response) {
                    alert('Error: ' + response.message);
                }
            });
        });

        $('#talentContentTable').on('click', '.deleteButton', function(e) {
            e.preventDefault(); 
            var id = $(this).data('id');
            var url = '{{ route('talent_content.destroy', ':id') }}'.replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'Talent content has been deleted.',
                                    'success'
                                );
                                table.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was an issue deleting the talent content.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an issue deleting the talent content.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

    });
</script>
@stop