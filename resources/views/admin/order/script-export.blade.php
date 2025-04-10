<script>
    // submit form
    $('#orderExportForm').submit(function(e) {
        e.preventDefault();

        let form = $(this);
        let submitBtn = form.find('button[type="submit"]');
        let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'); // Create spinner element

        // Disable the submit button to prevent multiple submissions
        submitBtn.prop('disabled', true).append(spinner);

        let formData = form.serialize();

        let now = moment();
        let formattedTime = now.format('YYYYMMDD-HHmmss');

        $.ajax({
            type: 'POST',
            url: "{{ route('order.export') }}",
            data: formData,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(response) {
                let link = document.createElement('a');
                link.href = window.URL.createObjectURL(response);
                link.download = 'Order-' + formattedTime + '.xlsx';
                link.click();

                $('#orderExportModal').modal('hide');
                toastr.success('{{ trans('messages.success_export', ['model' => trans('labels.order')]) }}');

                submitBtn.prop('disabled', false);
                spinner.remove();
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorExportOrder'));

                submitBtn.prop('disabled', false);
                spinner.remove();
            }
        });
    });
</script>
