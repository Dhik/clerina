<script>
    $(document).on('click', '.btnRefresh', refresh());

    function refresh () {
        return function () {
            let rowData = contentTable.row($(this).closest('tr')).data();

            $.ajax({
                type: 'GET',
                url: "{{ route('statistic.refresh', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', rowData.id),
                success: function(response) {
                    contentTable.ajax.reload();
                    toastr.success('{{ trans('messages.refresh_success') }}');
                },
                error: function(xhr, status, error) {
                    toastr.error('{{ trans('messages.refresh_failed') }}');
                }
            });
        }
    }
</script>
