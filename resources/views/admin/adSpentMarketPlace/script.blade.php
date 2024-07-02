<script>
    const errorSubmitAdSpentMarketPlace = $('#errorSubmitAdSpentMarketPlace');

    // submit form
    $('#adSpentMarketPlaceForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('adSpentMarketPlace.store') }}",
            data: formData,
            success: function(response) {
                errorSubmitAdSpentSocialMedia.addClass('d-none');
                salesTable.ajax.reload();
                $('#adSpentMarketPlaceForm')[0].reset();
                $('#adSpentMarketPlaceModal').modal('hide');
                updateRecapCount();
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.ad_spent_market_place')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, errorSubmitAdSpentSocialMedia);
            }
        });
    });
</script>
