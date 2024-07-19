@extends('adminlte::page')

@section('title', trans('labels.payroll'))

@section('content_header')
    <h1>{{ trans('labels.salary') }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payrollImportModal">
                                    <i class="fas fa-file-upload"></i> {{ trans('labels.import') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <table id="payrollTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="payroll-info" width="100%">
                        <thead>
                            <tr>
                                <th>{{ trans('labels.employee_id') }}</th>
                                <th>{{ trans('labels.full_name') }}</th>
                                <th>{{ trans('labels.gaji_pokok') }}</th>
                                <th>{{ trans('labels.tunjangan_jabatan') }}</th>
                                <th>{{ trans('labels.insentif_live') }}</th>
                                <th>{{ trans('labels.insentif') }}</th>
                                <th>{{ trans('labels.function') }}</th>
                                <th>{{ trans('labels.bpjs') }}</th>
                                <th>{{ trans('labels.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.payroll.modal-import')
@stop

@section('js')
    <script>
        $(function () {
            let payrollTable = $('#payrollTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                pageLength: 25,
                ajax: {
                    url: "{{ route('payrolls.data') }}",
                },
                columns: [
                    {data: 'employee_id', name: 'employee_id'},
                    {data: 'full_name', name: 'full_name'},
                    {data: 'gaji_pokok', name: 'gaji_pokok'},
                    {data: 'tunjangan_jabatan', name: 'tunjangan_jabatan'},
                    {data: 'insentif_live', name: 'insentif_live'},
                    {data: 'insentif', name: 'insentif'},
                    {data: 'function', name: 'function'},
                    {data: 'bpjs', name: 'bpjs'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[0, 'desc']]
            });

            // Handle form submission with AJAX
            $('#payrollImportForm').submit(function(e) {
                e.preventDefault();

                let form = $(this);
                let submitBtn = form.find('button[type="submit"]');
                let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'); // Create spinner element

                // Disable the submit button to prevent multiple submissions
                submitBtn.prop('disabled', true).append(spinner);

                const fieldUpload =  $('#filePayrollImport');

                let fileInput = fieldUpload.prop('files')[0];

                let formData = new FormData();
                formData.append('file', fileInput);

                let csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    type: 'POST',
                    url: "{{ route('payrolls.import') }}",
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        fieldUpload.val(null);
                        const newPlaceholder = $('<label class="custom-file-label" for="customFile" id="labelUploadImport">{{ trans('placeholder.select_file') }}</label>');
                        $('#labelUploadImport').replaceWith(newPlaceholder);

                        toastr.success('Payrolls imported successfully.');

                        submitBtn.prop('disabled', false);
                        spinner.remove();

                        payrollTable.ajax.reload();
                        $('#errorImportOrder').addClass('d-none');
                        $('#payrollImportModal').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Error importing payrolls: ' + xhr.responseText);

                        submitBtn.prop('disabled', false);
                        spinner.remove();
                    }
                });
            });

            // Handle delete button
            $('#payrollTable').on('click', '.deleteButton', function() {
                var id = $(this).data('id');
                if (confirm('Are you sure you want to delete this payroll?')) {
                    $.ajax({
                        url: "{{ url('admin/payroll/destroy') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                payrollTable.ajax.reload();
                                alert('Payroll deleted successfully.');
                            } else {
                                alert('An error occurred while deleting the payroll.');
                            }
                        }
                    });
                }
            });
        });
    </script>
@stop
