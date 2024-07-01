<script>
    // submit form
    $('#marketingImportForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'); // Create spinner element

        // Disable the submit button to prevent multiple submissions
        submitBtn.prop('disabled', true).append(spinner);

        const fieldUpload =  $('#fileMarketingImport');

        let fileInput = fieldUpload.prop('files')[0];

        let formData = new FormData();
        formData.append('fileMarketingImport', fileInput);

        let csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: 'POST',
            url: "{{ route('marketing.import') }}",
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

                toastr.success('{{ trans('messages.success_import', ['model' => trans('labels.marketing')]) }}');

                submitBtn.prop('disabled', false);
                spinner.remove();

                marketingTable.ajax.reload();
                $('#errorImportMarketing').addClass('d-none');
                $('#marketingImportModal').modal('hide');
            },
            error: function(xhr, status, error) {
                errorImportAjaxValidation(xhr, status, error, $('#errorImportMarketing'));

                submitBtn.prop('disabled', false);
                spinner.remove();
            }
        });
    });
</script>
