@extends('adminlte::page')

@section('title', trans('labels.user'))

@section('content_header')
    <h1>{{ $user->name }}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
                            <tbody>
                            <tr>
                                <th>{{ trans('labels.name') }}</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.email') }}</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.phone_number') }}</th>
                                <td>{{ $user->phone_number }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.position') }}</th>
                                <td>{{ $user->position }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.roles') }}</th>
                                <td>{{ $roles }}</td>
                            </tr>
                            <tr>
                                <th>{{ trans('labels.tenant') }}</th>
                                <td>
                                    <ul class="list-unstyled">
                                        @forelse($tenants as $tenant)
                                            <li class="mb-2">{{ $tenant }}</li>
                                        @empty
                                            -
                                        @endforelse
                                    </ul>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    @php
                        use App\Domain\User\Enums\PermissionEnum;
                    @endphp
                    <div class="card-footer">
                        @can(PermissionEnum::UpdateUser, $user)
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">{{ trans('buttons.edit') }}</a>
                            <a href="{{ route('users.editPasswordReset', $user->id) }}" class="btn btn-success">{{ trans('buttons.change_password') }}</a>
                        @endcan

                        @can(PermissionEnum::DeleteUser, $user)
                            <button class="btn btn-danger delete-user">{{ trans('buttons.delete') }}</button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.delete-user').click(function (e) {
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
                            url: '{{ route('users.destroy', $user->id) }}',
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
