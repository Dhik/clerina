<script>
    const mofuTableSelector = $('#mofuTable');

    $('#btnMofuModal').click(function() {
        $('#mofuForm').trigger("reset");
        $('#mofuDate').val(moment().format("DD/MM/YYYY"));
    });

    // datatable
    let mofuTable = mofuTableSelector.DataTable({
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
                d.type = 'mofu';
                d.social_media_id = filterSocialMedia.val();
            }
        },
        columns: [
            {data: 'date', name: 'date', sortable: false, orderable: false},
            {data: 'spendFormatted', name: 'spend', sortable: false, orderable: false},
            {data: 'reachFormatted', name: 'reach', sortable: false, orderable: false},
            {data: 'impressionFormatted', name: 'impression', sortable: false, orderable: false},
            {data: 'engagementFormatted', name: 'engagement', sortable: false, orderable: false},
            {data: 'cpeFormatted', name: 'cpe', sortable: false, orderable: false},
            {data: 'cpmFormatted', name: 'cpm', sortable: false, orderable: false},
            {data: 'frequencyFormatted', name: 'frequency', sortable: false, orderable: false},
            {data: 'cpcFormatted', name: 'cpc', sortable: false, orderable: false},
            {data: 'linkClickFormatted', name: 'link_click', sortable: false, orderable: false},
            {data: 'ctrFormatted', name: 'ctr', sortable: false, orderable: false},
            {data: 'cplvFormatted', name: 'cplv', sortable: false, orderable: false},
            {data: 'cpaFormatted', name: 'cpa', sortable: false, orderable: false},
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
            { "targets": [11], "className": "text-right" },
            { "targets": [12], "className": "text-right" },
            { "targets": [13], "className": "text-center" }
        ],
    });

    // submit form
    $('#mofuForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('funnel.input.mofu') }}",
            data: formData,
            success: function(response) {
                mofuTable.ajax.reload();
                $('#mofuForm').trigger("reset");
                $('#mofuModal').modal('hide');
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.mofu')]) }}');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    // Handle row click event to open modal and fill form
    mofuTable.on('draw.dt', function() {
        const tableBodySelector =  $('#mofuTable tbody');

        tableBodySelector.on('click', '.updateButton', function() {
            let rowData = mofuTable.row($(this).closest('tr')).data();

            let dateObject = moment(rowData.date, "DD MMM YYYY");
            let formattedDate = dateObject.format("DD/MM/YYYY");

            $('#mofuDate').val(formattedDate);
            $('#mofuSocialMedia').val(rowData.social_media_id);
            $('#mofuSpend').val(rowData.spend);
            $('#mofuReach').val(rowData.reach);
            $('#mofuImpression').val(rowData.impression);
            $('#mofuEngagement').val(rowData.engagement);
            $('#mofuLinkClick').val(rowData.link_click);
            $('#mofuCTR').val(rowData.ctr.replace(/\./g, ','));
            $('#mofuCPLV').val(rowData.cplv);
            $('#mofuCPA').val(rowData.cpa);
            $('#mofuModal').modal('show');
        });
    });
</script>
