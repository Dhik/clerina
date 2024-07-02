<script>
    $(document).on('click', '.btnStatistic', addStatistic());

    function addStatistic () {
        return function () {
            let rowData = contentTable.row($(this).closest('tr')).data();

            $('#statisticContentId').val(rowData.id);
            $('#view').val(rowData.view);
            $('#like').val(rowData.like);
            $('#comment').val(rowData.comment);
            $('#statisticModal').modal('show');
        }
    }

    // submit statistic form
    $('#statisticForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('statistic.store', ['campaignContent' => ':campaignContent']) }}".replace(':campaignContent', $('#statisticContentId').val()),
            data: formData,
            success: function(response) {
                contentTable.ajax.reload();
                $('#statisticModal').modal('hide');
                $('#statisticForm')[0].reset();
                $('#errorStatistic').empty();
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.data')]) }}');
            },
            error: function(xhr, status, error) {
                errorAjaxValidation(xhr, status, error, $('#errorStatistic'));
            }
        });
    });
</script>
