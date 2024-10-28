<script>
    function handleButtonClick(action) {
        return function() {
            console.log('here')

            let rowData = contentTable.row($(this).closest('tr')).data();
            let url;

            if (action === 'fyp') {
                url = "{{ route('campaignContent.update.fyp', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', rowData.id);
            } else if (action === 'deliver') {
                url = "{{ route('campaignContent.update.deliver', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', rowData.id);
            } else if (action === 'pay') {
                url = "{{ route('campaignContent.update.payment', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', rowData.id);
            }

            $.ajax({
                type: 'GET',
                url: url,
                success: function(response) {
                    console.log('success');
                },
                error: function(xhr, status, error) {
                    console.log('error');
                }
            });

            setTimeout(function () {
                $('[data-toggle="tooltip"]').tooltip().hide();
                contentTable.ajax.reload();
            }, 300);
        };
    }

    $(document).on('click', '.btnFyp', function(event) {
        event.preventDefault();
        handleButtonClick('fyp').call(this, event);
    });

    $(document).on('click', '.btnDeliver', function(event) {
        event.preventDefault();
        handleButtonClick('deliver').call(this, event);
    });

    $(document).on('click', '.btnPay', function(event) {
        event.preventDefault();
        handleButtonClick('pay').call(this, event);
    });

    $(document).on('click', '.btnCopy', function(e) {
        e.preventDefault();

        let rowData = contentTable.row($(this).closest('tr')).data();

        // Create a temporary input element
        let tempInput = document.createElement('input');
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        tempInput.value = rowData.link;
        document.body.appendChild(tempInput);

        // Select the text inside the input
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text to the clipboard
        document.execCommand('copy');

        // Remove the temporary input element
        document.body.removeChild(tempInput);

        toastr.success('{{ trans('messages.copy_success') }}');
    });
    $(document).on('click', '.btnKode', function(e) {
        e.preventDefault();

        let rowData = contentTable.row($(this).closest('tr')).data();

        // Create a temporary input element
        let tempInput = document.createElement('input');
        tempInput.style.position = 'absolute';
        tempInput.style.left = '-9999px';
        tempInput.value = rowData.kode_ads;
        document.body.appendChild(tempInput);

        // Select the text inside the input
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text to the clipboard
        document.execCommand('copy');

        // Remove the temporary input element
        document.body.removeChild(tempInput);

        toastr.success('{{ trans('messages.copy_code_success') }}');
    });
</script>
