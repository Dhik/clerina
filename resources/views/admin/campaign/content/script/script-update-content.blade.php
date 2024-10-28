<script>
    $(document).on('click', '.btnUpdateContent', updateContent());

    $(document).on('click', '.btnDeleteContent',  deleteContent());

    function updateContent() {
        return function (event) {
            event.preventDefault()
            let rowData = contentTable.row($(this).closest('tr')).data();

            $('#contentId').val(rowData.id);
            $('#usernameUpdate').val(rowData.username);
            $('#taskNameUpdate').val(rowData.task);
            $('#rateCardUpdate').val(rowData.rate_card);
            $('#platformUpdate').val(rowData.channel).trigger('change');
            $('#linkUpdate').val(rowData.link);
            $('#productUpdate').val(rowData.product);
            $('#boostCodeUpdate').val(rowData.boost_code);
            $('#adsCodeUpdate').val(rowData.kode_ads);

            $('#viewsUpdate').val(rowData.view);
            $('#likesUpdate').val(rowData.like);
            $('#commentsUpdate').val(rowData.comment);

            $('#contentUpdateModal').modal('show');
        }
    }

    function deleteContent() {
        return function (event) {
            event.preventDefault()
            let rowData = contentTable.row($(this).closest('tr')).data();

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
                        url: "{{ route('campaignContent.destroy', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', rowData.id),
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
                                contentTable.ajax.reload();
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
        }
    }

    // submit update form
    $('#contentUpdateForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'PUT',
            url: "{{ route('campaignContent.update', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', $('#contentId').val()),
            data: formData,
            success: function(response) {
                contentTable.ajax.reload();
                $('#contentUpdateModal').modal('hide');
                $('#usernameUpdate').val(null).trigger('change');
                $('#platformUpdate').val(null).trigger('change');
                $('#contentUpdateForm')[0].reset();
                $('#errorContentUpdate').empty();
                toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.content')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorContentUpdate'));
            }
        });
    });
</script>
