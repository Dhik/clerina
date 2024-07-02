<script>
    // submit form
    $('#orderImportForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'); // Create spinner element

        // Disable the submit button to prevent multiple submissions
        submitBtn.prop('disabled', true).append(spinner);

        const fieldUpload =  $('#fileOrderImport');

        let fileInput = fieldUpload.prop('files')[0];

        let formData = new FormData();
        formData.append('fileOrderImport', fileInput);
        formData.append('sales_channel_id', $('#salesChannelIdImport').val())

        let csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: 'POST',
            url: "{{ route('order.import') }}",
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                fieldUpload.val(null);
                const newPlaceholder = $('<label class="custom-file-label" for="customFile" id="labelUploadImport"">{{ trans('placeholder.select_file') }}</label>');
                $('#labelUploadImport').replaceWith(newPlaceholder);

                toastr.success('{{ trans('messages.success_import', ['model' => trans('labels.order')]) }}');

                submitBtn.prop('disabled', false);
                spinner.remove();

                orderTable.ajax.reload();
                $('#errorImportOrder').addClass('d-none');
                $('#orderImportModal').modal('hide');
            },
            error: function(xhr, status, error) {
                errorImportAjaxValidation(xhr, status, error, $('#errorImportOrder'));

                submitBtn.prop('disabled', false);
                spinner.remove();
            }
        });
    });
</script>
