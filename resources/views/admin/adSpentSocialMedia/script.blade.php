<script>
    const errorSubmitAdSpentSocialMedia = $('#errorSubmitAdSpentSocialMedia');

    // submit form
    $('#adSpentSocialMediaForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('adSpentSocialMedia.store') }}",
            data: formData,
            success: function(response) {
                errorSubmitAdSpentSocialMedia.addClass('d-none');
                salesTable.ajax.reload();
                $('#adSpentSocialMediaForm')[0].reset();
                $('#adSpentSocialMediaModal').modal('hide');
                updateRecapCount();
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.ad_spent_social_media')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, errorSubmitAdSpentSocialMedia);
            }
        });
    });
</script>
