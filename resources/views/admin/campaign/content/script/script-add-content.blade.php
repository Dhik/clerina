<script>
    // submit update form
    $('#contentForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('campaignContent.store', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
            data: formData,
            success: function(response) {
                contentTable.ajax.reload();
                $('#contentModal').modal('hide');
                $('#username').val(null).trigger('change');
                $('#platform').val(null).trigger('change');
                $('#contentForm')[0].reset();
                $('#errorContent').empty();
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.content')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorContent'));
            }
        });
    });
</script>
