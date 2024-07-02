<script>
    const tofuTableSelector = $('#tofuTable');

    // datatable
    let tofuTable = tofuTableSelector.DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 50,
        searching: false,
        lengthChange: false,
        ajax: {
            url: "{{ route('funnel.recap.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val();
                d.type = 'tofu';
            }
        },
        columns: [
            {data: 'date', name: 'date', sortable: false, orderable: false},
            {data: 'spendFormatted', name: 'spend', sortable: false, orderable: false},
            {data: 'reachFormatted', name: 'reach', sortable: false, orderable: false},
            {data: 'cprFormatted', name: 'cpr', sortable: false, orderable: false},
            {data: 'impressionFormatted', name: 'impression', sortable: false, orderable: false},
            {data: 'cpmFormatted', name: 'cpm', sortable: false, orderable: false},
            {data: 'frequencyFormatted', name: 'frequency', sortable: false, orderable: false},
            {data: 'cpvFormatted', name: 'cpv', sortable: false, orderable: false},
            {data: 'playVideoFormatted', name: 'play_video', sortable: false, orderable: false},
            {data: 'linkClickFormatted', name: 'link_click', sortable: false, orderable: false},
            {data: 'cpcFormatted', name: 'cpc', sortable: false, orderable: false},
        ],
        columnDefs: [
            { "targets": [1], "className": "text-right" },
            { "targets": [2], "className": "text-right" },
            { "targets": [3], "className": "text-right" },
            { "targets": [4], "className": "text-right" },
            { "targets": [5], "className": "text-right" },
            { "targets": [6], "className": "text-right" },
            { "targets": [7], "className": "text-right" },
            { "targets": [8], "className": "text-right" },
            { "targets": [9], "className": "text-right" },
            { "targets": [10], "className": "text-right" }
        ],
    });

    // submit form
    $('#tofuForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('funnel.input.tofu') }}",
            data: formData,
            success: function(response) {
                tofuTable.ajax.reload();
                $('#tofuForm').trigger("reset");
                $('#tofuModal').modal('hide');
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.tofu')]) }}');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });
</script>
