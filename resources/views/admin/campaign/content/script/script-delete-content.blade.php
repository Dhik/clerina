<script>
    $('.delete-campaign').click(function (e) {
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
                    url: '{{ route('campaign.destroy', $campaign->id) }}',
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
                            window.location.href = "{{ route('campaign.index') }}";
                        });
                    },
                    error: function (xhr, status, error) {
                        if (xhr.status === 422) {
                            Swal.fire(
                                '{{ trans('labels.failed') }}',
                                xhr.responseJSON.message,
                                'error'
                            );
                        } else {
                            Swal.fire(
                                '{{ trans('labels.failed') }}',
                                '{{ trans('messages.error_delete') }}',
                                'error'
                            );
                        }
                    }
                });
            }
        });
    });
</script>
