<script>
    // submit branding update form
    $('#orderUpdateForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        let orderId = $('#orderId').val();

        let updateUrl = '{{ route('order.update', ':orderId') }}';
        updateUrl = updateUrl.replace(':orderId', orderId);

        $.ajax({
            type: 'PUT',
            url: updateUrl,
            data: formData,
            success: function(response) {
                orderTable.ajax.reload();
                $('#orderUpdateModal').modal('hide');
                toastr.success('{{ trans('messages.success_update', ['model' => trans('labels.order')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorUpdateSubmitOrder'))
            }
        });
    });
</script>
