<script>
    // Submit form for KOL content import
    $('#contentImportKOLForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
        submitBtn.prop('disabled', true).append(spinner);
        
        const fieldUpload = $('#fileContentImportKOL');
        let fileInput = fieldUpload.prop('files')[0];
        let formData = new FormData();
        formData.append('fileContentImport', fileInput);
        
        let csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        let url = "{{ route('campaignContent.import_kol', ['campaign' => ':campaign']) }}".replace(':campaign', '{{ $campaign->id }}');

        $.ajax({
            type: 'POST',
            url: url,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                fieldUpload.val(null);
                const newPlaceholder = $('<label class="custom-file-label" for="customFile" id="labelUploadImportKOL">{{ trans('placeholder.select_file') }}</label>');
                $('#labelUploadImportKOL').replaceWith(newPlaceholder);

                toastr.success('{{ trans('messages.success_import', ['model' => trans('labels.campaign')]) }}');

                submitBtn.prop('disabled', false);
                spinner.remove();

                contentTable.ajax.reload();
                $('#errorImportCampaignKOL').addClass('d-none');
                $('#contentImportKOLModal').modal('hide');
            },
            error: function(xhr, status, error) {
                errorImportAjaxValidation(xhr, status, error, $('#errorImportCampaignKOL'));

                submitBtn.prop('disabled', false);
                spinner.remove();
            }
        });
    });
</script>
