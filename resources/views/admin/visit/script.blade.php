<script>
    const amountVisitField = $('#amountVisit');
    const errorSubmitVisit = $('#errorSubmitVisit');

    // submit form
    $('#visitForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('visit.store') }}",
            data: formData,
            success: function(response) {
                errorSubmitVisit.addClass('d-none');
                salesTable.ajax.reload();
                $('#visitForm')[0].reset();
                $('#visitModal').modal('hide');
                updateRecapCount();
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.visit')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, errorSubmitVisit);
            }
        });
    });
</script>
