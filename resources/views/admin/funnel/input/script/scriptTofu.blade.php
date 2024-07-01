<script>
    const tofuTableSelector = $('#tofuTable');

    $('#btnTofuModal').click(function() {
        $('#tofuForm').trigger("reset");
        $('#tofuDate').val(moment().format("DD/MM/YYYY"));
    });

    // datatable
    let tofuTable = tofuTableSelector.DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        pageLength: 50,
        searching: false,
        lengthChange: false,
        ajax: {
            url: "{{ route('funnel.input.get') }}",
            data: function (d) {
                d.filterDates = filterDate.val();
                d.type = 'tofu';
                d.social_media_id = filterSocialMedia.val();
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
            {data: 'actions', sortable: false, orderable: false}
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
            { "targets": [10], "className": "text-right" },
            { "targets": [11], "className": "text-center" }
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

    // Handle row click event to open modal and fill form
    tofuTable.on('draw.dt', function() {
        const tableBodySelector =  $('#tofuTable tbody');

        tableBodySelector.on('click', '.updateButton', function() {
            let rowData = tofuTable.row($(this).closest('tr')).data();

            let dateObject = moment(rowData.date, "DD MMM YYYY");
            let formattedDate = dateObject.format("DD/MM/YYYY");

            $('#tofuDate').val(formattedDate);
            $('#tofuSocialMedia').val(rowData.social_media_id);
            $('#tofuSpend').val(rowData.spend);
            $('#tofuReach').val(rowData.reach);
            $('#tofuImpression').val(rowData.impression);
            $('#tofuCPV').val(rowData.cpv);
            $('#tofuPlayVideo').val(rowData.play_video);
            $('#tofuLinkClick').val(rowData.link_click);
            $('#tofuModal').modal('show');
        });
    });
</script>
