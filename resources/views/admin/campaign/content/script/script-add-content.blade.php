<script>
    // submit update form
    $('#contentForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        let saveButton = $(this).find('button[type="submit"]');
        let spinner = saveButton.find('.spinner-border');

        // Show loading spinner
        spinner.removeClass('d-none');
        saveButton.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: "{{ route('campaignContent.store', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
            data: formData,
            success: function(response) {
                contentTable.ajax.reload();
                $('#contentModal').modal('hide');
                $('#platform').val(null).trigger('change');
                $('#contentForm')[0].reset();
                $('#errorContent').empty();
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.content')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorContent'));
            },
            complete: function() {
                // Hide loading spinner and re-enable button
                spinner.addClass('d-none');
                saveButton.prop('disabled', false);
            }
        });
    });
</script>
