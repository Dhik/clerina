<script>

    $('#btnAddOrder').click(function() {
        $('#date').val(moment().format("DD/MM/YYYY"));
    });

    // submit form
    $('#orderForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('order.store') }}",
            data: formData,
            success: function(response) {
                errorSubmitOrder.addClass('d-none');
                orderTable.ajax.reload();
                $('#orderForm')[0].reset();
                $('#orderModal').modal('hide');
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.order')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorSubmitOrder'));
            }
        });
    });
</script>
