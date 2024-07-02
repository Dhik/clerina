@extends('adminlte::page')

@section('title', trans('labels.employee'))

@section('content_header')
<div class="row">
    <div class="col-md-4 text-center">
        <img src="{{ $employee->profile_picture ? asset('storage/' . $employee->profile_picture) : asset('img/user.png') }}" 
                alt="Profile Picture" 
                class="img-fluid rounded-circle" 
                style="width: 150px; height: 150px;">
    </div>
    <div class="col-md-8">
        <h2>{{ $employee->full_name }}</h2>
        <p class="text-muted">{{ $employee->job_position }}</p>
    </div>
</div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>{{ trans('labels.id') }}</th>
                                <td>{{ $employee->employee_id }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.name') }}</th>
                                <td>{{ $employee->full_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.email') }}</th>
                                <td>{{ $employee->email }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <td>{{ $employee->mobile_phone }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.job_position') }}</th>
                                <td>{{ $employee->job_position }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.job_level') }}</th>
                                <td>{{ $employee->job_level }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.organization') }}</th>
                                <td>{{ $employee->organization }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.id') }}</th>
                                <td>{{ $employee->employee_id }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.barcode') }}</th>
                                <td>{{ $employee->barcode }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.join_date') }}</th>
                                <td>{{ $employee->join_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.resign_date') }}</th>
                                <td>{{ $employee->resign_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.status_employee') }}</th>
                                <td>{{ $employee->status_employee }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.end_date') }}</th>
                                <td>{{ $employee->end_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.sign_date') }}</th>
                                <td>{{ $employee->sign_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.email') }}</th>
                                <td>{{ $employee->email }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.birth_date') }}</th>
                                <td>{{ $employee->birth_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.age') }}</th>
                                <td>{{ $employee->age }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.birth_place') }}</th>
                                <td>{{ $employee->birth_place }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.citizen_id_address') }}</th>
                                <td>{{ $employee->citizen_id_address }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.residential_address') }}</th>
                                <td>{{ $employee->residential_address }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.npwp') }}</th>
                                <td>{{ $employee->npwp }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.ptkp_status') }}</th>
                                <td>{{ $employee->ptkp_status }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.employee_tax_status') }}</th>
                                <td>{{ $employee->employee_tax_status }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.tax_config') }}</th>
                                <td>{{ $employee->tax_config }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.bank_name') }}</th>
                                <td>{{ $employee->bank_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.bank_account') }}</th>
                                <td>{{ $employee->bank_account }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.bank_account_holder') }}</th>
                                <td>{{ $employee->bank_account_holder }}</td>
                            </tr>
                            <!-- Add more fields as needed -->
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        @can('update', $employee)
                            <a href="{{ route('employee.edit', $employee->id) }}" class="btn btn-primary">{{ trans('buttons.edit') }}</a>
                        @endcan

                        <!-- @can('delete', $employee)
                            <button class="btn btn-danger delete-employee">{{ trans('buttons.delete') }}</button>
                        @endcan -->
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th>{{ trans('labels.bpjs_ketenagakerjaan') }}</th>
                                <td>{{ $employee->bpjs_ketenagakerjaan }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.bpjs_kesehatan') }}</th>
                                <td>{{ $employee->bpjs_kesehatan }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.nik_npwp_16_digit') }}</th>
                                <td>{{ $employee->nik_npwp_16_digit }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.phone') }}</th>
                                <td>{{ $employee->phone }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.branch_name') }}</th>
                                <td>{{ $employee->branch_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.parent_branch_name') }}</th>
                                <td>{{ $employee->parent_branch_name }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.religion') }}</th>
                                <td>{{ $employee->religion }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.gender') }}</th>
                                <td>{{ $employee->gender }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.marital_status') }}</th>
                                <td>{{ $employee->marital_status }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.blood_type') }}</th>
                                <td>{{ $employee->blood_type }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.nationality_code') }}</th>
                                <td>{{ $employee->nationality_code }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.currency') }}</th>
                                <td>{{ $employee->currency }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.length_of_service') }}</th>
                                <td>{{ $employee->length_of_service }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.payment_schedule') }}</th>
                                <td>{{ $employee->payment_schedule }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.approval_line') }}</th>
                                <td>{{ $employee->approval_line }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.manager') }}</th>
                                <td>{{ $employee->manager }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.grade') }}</th>
                                <td>{{ $employee->grade }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.class') }}</th>
                                <td>{{ $employee->class }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.profile_picture') }}</th>
                                <td><img src="{{ asset('storage/' . $employee->profile_picture) }}" alt="Profile Picture" class="img-fluid rounded-circle" style="width: 50px; height: 50px;"></td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.cost_center') }}</th>
                                <td>{{ $employee->cost_center }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.cost_center_category') }}</th>
                                <td>{{ $employee->cost_center_category }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.sbu') }}</th>
                                <td>{{ $employee->sbu }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.npwp_16_digit') }}</th>
                                <td>{{ $employee->npwp_16_digit }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.passport') }}</th>
                                <td>{{ $employee->passport }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.passport_expiration_date') }}</th>
                                <td>{{ $employee->passport_expiration_date }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.created_at') }}</th>
                                <td>{{ $employee->created_at }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.updated_at') }}</th>
                                <td>{{ $employee->updated_at }}</td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.delete-employee').click(function (e) {
                e.preventDefault();

                Swal.fire({
                    title: '{{ trans('labels.are_you_sure') }}',
                    text: '{{ trans('labels.not_be_able_to_recover') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ trans('buttons.confirm_swal') }}',
                    cancelButtonText: '{{ trans('buttons.cancel_swal') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('users.destroy', $employee->id) }}',
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire(
                                    '{{ trans('labels.success') }}',
                                    '{{ trans('messages.success_delete') }}',
                                    'success'
                                ).then(() => {
                                    window.location.href = "{{ route('users.index') }}";
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
