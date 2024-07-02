<script>
    const bofuTableSelector = $('#bofuTable');

    // datatable
    let bofuTable = bofuTableSelector.DataTable({
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
                d.type = 'bofu';
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
            {data: 'frequencyFormatted', name: 'frequency', sortable: false, orderable: false}
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
        ],
    });

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
</script>
