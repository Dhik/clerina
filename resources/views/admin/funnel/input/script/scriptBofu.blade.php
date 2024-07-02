<script>

    const bofuTableSelector = $('#bofuTable');

    $('#btnBofuModal').click(function() {
        $('#bofuForm').trigger("reset");
        $('#bofuDate').val(moment().format("DD/MM/YYYY"));
    });

    // datatable
    let bofuTable = bofuTableSelector.DataTable({
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
                d.type = 'bofu';
                d.social_media_id = filterSocialMedia.val();
            }
        },
        columns: [
            {data: 'date', name: 'date', sortable: false, orderable: false},
            {data: 'spendFormatted', name: 'spend', sortable: false, orderable: false},
            {data: 'atcFormatted', name: 'atc', sortable: false, orderable: false},
            {data: 'initiatedCheckoutNumberFormatted', name: 'initiated_checkout_number', sortable: false, orderable: false},
            {data: 'purchaseNumberFormatted', name: 'purchase_number', sortable: false, orderable: false},
            {data: 'costPerIcFormatted', name: 'cost_per_ic', sortable: false, orderable: false},
            {data: 'costPerAtcFormatted', name: 'cost_per_atc', sortable: false, orderable: false},
            {data: 'costPerPurchaseFormatted', name: 'cost_per_purchase', sortable: false, orderable: false},
            {data: 'roasFormatted', name: 'roas', sortable: false, orderable: false},
            {data: 'frequencyFormatted', name: 'frequency', sortable: false, orderable: false},
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
            { "targets": [10], "className": "text-center" }
        ],
    });

    const bofuSpend = $('#bofuSpend');
    const bofuATC = $('#bofuATC');
    const bofuIC = $('#bofuIC');
    const bofuPurchaseNumber = $('#bofuPurchaseNumber');
    const bofuRoas = $('#bofuRoas');
    const bofuFrequency = $('#bofuFrequency');

    // submit form
    $('#bofuForm').submit(function(e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            type: 'POST',
            url: "{{ route('funnel.input.bofu') }}",
            data: formData,
            success: function(response) {
                bofuTable.ajax.reload();
                $('#bofuForm').trigger("reset");
                $('#bofuModal').modal('hide');
                toastr.success('{{ trans('messages.success_save', ['model' => trans('labels.bofu')]) }}');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    // Handle row click event to open modal and fill form
    bofuTable.on('draw.dt', function() {
        const tableBodySelector =  $('#bofuTable tbody');

        tableBodySelector.on('click', '.updateButton', function() {
            let rowData = bofuTable.row($(this).closest('tr')).data();

            let dateObject = moment(rowData.date, "DD MMM YYYY");
            let formattedDate = dateObject.format("DD/MM/YYYY");

            $('#bofuDate').val(formattedDate);
            $('#bofuSocialMedia').val(rowData.social_media_id);
            $('#bofuSpend').val(rowData.spend);
            $('#bofuATC').val(rowData.atc);
            $('#bofuIC').val(rowData.initiated_checkout_number);
            $('#bofuPurchaseNumber').val(rowData.purchase_number);
            $('#bofuRoas').val(rowData.roas.replace(/\./g, ','));
            $('#bofuFrequency').val(rowData.frequency.replace(/\./g, ','));
            $('#bofuModal').modal('show');
        });
    });
</script>
