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

    // Sequential Bulk Refresh Functionality
    $(document).ready(function() {
        // Ensure we remove any existing handlers first
        $(document).off('click', '#confirmRefreshAll');
        
        // Add the sequential bulk refresh handler
        $(document).on('click', '#confirmRefreshAll', function() {
            const contents = $('#refreshAllContentList tr');
            const totalContents = contents.length;
            let completedContents = 0;
            let currentIndex = 0;
            
            console.log(`Starting sequential bulk refresh for ${totalContents} items`);

            // Disable the confirm button to prevent multiple clicks
            $(this).prop('disabled', true).text('Processing...');

            // Function to process one content at a time
            function processNextContent() {
                if (currentIndex >= totalContents) {
                    console.log('All items processed');
                    $('#confirmRefreshAll').prop('disabled', false).text('Confirm Refresh All');
                    return;
                }

                const contentRow = contents.eq(currentIndex);
                const contentId = contentRow.attr('id').split('-')[1];
                
                console.log(`Processing item ${currentIndex + 1}/${totalContents}, ID: ${contentId}`);
                
                // Show loading spinner
                $(`#content-${contentId} td:last-child`).html('<i class="fas fa-spinner fa-spin text-primary"></i>');

                $.ajax({
                    url: "{{ route('statistic.refresh', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', contentId),
                    method: 'GET',
                    timeout: 60000, // 60 second timeout
                    success: function(data) {
                        console.log(`Success for item ${contentId}:`, data);
                        $(`#content-${contentId} td:last-child`).html('<i class="fas fa-check text-success"></i>');
                        completedContents++;
                        updateProgressBar(completedContents, totalContents);
                        
                        // Move to next content after 2 second delay
                        setTimeout(function() {
                            currentIndex++;
                            processNextContent();
                        }, 2000);
                    },
                    error: function(xhr, status, error) {
                        console.log(`Error for item ${contentId}:`, {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            error: error,
                            response: xhr.responseText
                        });
                        
                        // Show error with more specific icon based on error type
                        let errorIcon = '<i class="fas fa-times text-danger"></i>';
                        if (status === 'timeout') {
                            errorIcon = '<i class="fas fa-clock text-warning" title="Timeout"></i>';
                        } else if (xhr.status === 500) {
                            errorIcon = '<i class="fas fa-exclamation-triangle text-danger" title="Server Error"></i>';
                        }
                        
                        $(`#content-${contentId} td:last-child`).html(errorIcon);
                        completedContents++;
                        updateProgressBar(completedContents, totalContents);
                        
                        // Move to next content after delay even on error
                        setTimeout(function() {
                            currentIndex++;
                            processNextContent();
                        }, 2000);
                    }
                });
            }

            // Start processing the first content
            processNextContent();
        });
    });
</script>