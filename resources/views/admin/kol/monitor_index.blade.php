@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>Affiliate Monitor</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="kolTable" class="table table-bordered table-striped dataTable responsive" aria-describedby="kol-info" width="100%">
                        <thead>
                        <tr>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.username') }}</th>
                            <th width="8%">Followers</th>
                            <th width="8%">Following</th>
                            <th width="8%">Total Likes</th>
                            <th width="8%">Video Count</th>
                            <th width="8%">Engagement</th>
                            <th width="8%">Recent Views</th>
                            <th width="8%">Activity</th>
                            <th width="8%">Affiliate Status</th>
                            <th width="5%">Refresh</th>
                            <th width="8%">{{ trans('labels.action') }}</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Add this modal to your index.blade.php -->
    <div class="modal fade" id="editKolModal" tabindex="-1" role="dialog" aria-labelledby="editKolModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="editKolModalLabel">
                        <i class="fas fa-edit"></i> Edit KOL Information
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editKolForm" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Hidden fields to preserve existing required data -->
                    <input type="hidden" name="channel" id="edit_channel">
                    <input type="hidden" name="niche" id="edit_niche">
                    <input type="hidden" name="average_view" id="edit_average_view">
                    <input type="hidden" name="skin_type" id="edit_skin_type">
                    <input type="hidden" name="skin_concern" id="edit_skin_concern">
                    <input type="hidden" name="content_type" id="edit_content_type">
                    <input type="hidden" name="rate" id="edit_rate">
                    <input type="hidden" name="pic_contact" id="edit_pic_contact">
                    <input type="hidden" name="name" id="edit_name">
                    <input type="hidden" name="address" id="edit_address">
                    <input type="hidden" name="bank_name" id="edit_bank_name">
                    <input type="hidden" name="bank_account" id="edit_bank_account">
                    <input type="hidden" name="bank_account_name" id="edit_bank_account_name">
                    <input type="hidden" name="npwp" id="edit_npwp">
                    <input type="hidden" name="npwp_number" id="edit_npwp_number">
                    <input type="hidden" name="nik" id="edit_nik">
                    <input type="hidden" name="notes" id="edit_notes">
                    <input type="hidden" name="product_delivery" id="edit_product_delivery">
                    <input type="hidden" name="product" id="edit_product">
                    
                    <div class="modal-body">
                        <div id="editFormLoader" class="text-center" style="display: none;">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Loading KOL data...</p>
                        </div>
                        
                        <div id="editFormContent" style="display: none;">
                            <!-- Username -->
                            <div class="form-group row">
                                <label for="edit_username" class="col-md-4 col-form-label text-md-right">Username</label>
                                <div class="col-md-8">
                                    <input type="text" 
                                        class="form-control" 
                                        name="username" 
                                        id="edit_username" 
                                        required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group row">
                                <label for="edit_phone_number" class="col-md-4 col-form-label text-md-right">Phone Number</label>
                                <div class="col-md-8">
                                    <input type="text" 
                                        class="form-control" 
                                        name="phone_number" 
                                        id="edit_phone_number">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Status Affiliate - NEW FIELD -->
                            <div class="form-group row">
                                <label for="edit_status_affiliate" class="col-md-4 col-form-label text-md-right">Affiliate Status</label>
                                <div class="col-md-8">
                                    <select class="form-control" name="status_affiliate" id="edit_status_affiliate">
                                        <option value="">Not Set</option>
                                        <option value="Qualified">Qualified</option>
                                        <option value="Waiting List">Waiting List</option>
                                        <option value="Not Qualified">Not Qualified</option>
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Views Last 9 Posts -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Recent Views</label>
                                <div class="col-md-8">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="views_last_9_post" 
                                            id="edit_views_yes" 
                                            value="1">
                                        <label class="form-check-label" for="edit_views_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="views_last_9_post" 
                                            id="edit_views_no" 
                                            value="0">
                                        <label class="form-check-label" for="edit_views_no">No</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="views_last_9_post" 
                                            id="edit_views_null" 
                                            value="">
                                        <label class="form-check-label" for="edit_views_null">Not Set</label>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Activity Posting -->
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">Activity Status</label>
                                <div class="col-md-8">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="activity_posting" 
                                            id="edit_activity_active" 
                                            value="1">
                                        <label class="form-check-label" for="edit_activity_active">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="activity_posting" 
                                            id="edit_activity_inactive" 
                                            value="0">
                                        <label class="form-check-label" for="edit_activity_inactive">Inactive</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" 
                                            type="radio" 
                                            name="activity_posting" 
                                            id="edit_activity_null" 
                                            value="">
                                        <label class="form-check-label" for="edit_activity_null">Not Set</label>
                                    </div>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" id="saveKolBtn">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        const kolTableSelector = $('#kolTable');
        const channelSelector = $('#filterChannel');
        const nicheSelector = $('#filterNiche');
        const skinTypeSelector = $('#filterSkinType');
        const skinConcernSelector = $('#filterSkinConcern');
        const contentTypeSelector = $('#filterContentType');
        const picSelector = $('#filterPIC');
        const btnExportKol = $('#btnExportKol');
        const statusAffiliateSelector = $('#filterStatusAffiliate');
        const followersMinSelector = $('#filterFollowersMin');
        const followersMaxSelector = $('#filterFollowersMax');
        let bulkRefreshInProgress = false;
        let bulkRefreshStopped = false;

        $('#btnBulkRefresh').click(function() {
            if (bulkRefreshInProgress) {
                return;
            }

            // Show confirmation
            Swal.fire({
                title: 'Bulk Refresh Confirmation',
                text: 'This will refresh followers/following for all KOLs. This may take several minutes. Continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, start refresh!'
            }).then((result) => {
                if (result.isConfirmed) {
                    startBulkRefresh();
                }
            });
        });

        function loadKpiData() {
            $.get("{{ route('kol.kpi') }}", function(data) {
                $('#totalKol').text(data.total_kol || 0);
                $('#totalAffiliate').text(data.total_affiliate || 0);
                $('#activeAffiliate').text(data.active_affiliate || 0);
                $('#activePosting').text(data.active_posting || 0);
                $('#hasViews').text(data.has_views || 0);
                
                // Format average engagement as percentage
                const avgEngagement = data.avg_engagement ? parseFloat(data.avg_engagement).toFixed(2) + '%' : '0%';
                $('#avgEngagement').text(avgEngagement);
            }).fail(function() {
                console.error('Failed to load KPI data');
                // Set default values on error
                $('#totalKol, #totalAffiliate, #activeAffiliate, #activePosting, #hasViews').text('0');
                $('#avgEngagement').text('0%');
            });
        }

        $('#btnStopBulkRefresh').click(function() {
            bulkRefreshStopped = true;
            $('#btnStopBulkRefresh').hide();
            $('#statusText').text('Stopping refresh...');
        });

        // Start bulk refresh process
        function startBulkRefresh() {
            bulkRefreshInProgress = true;
            bulkRefreshStopped = false;
            
            // Reset modal
            $('#bulkRefreshProgress').css('width', '0%').attr('aria-valuenow', 0).text('0%');
            $('#bulkRefreshResults').empty();
            $('#statusText').text('Fetching KOL usernames...');
            $('#btnCloseBulkModal').prop('disabled', true);
            $('#btnStopBulkRefresh').show();
            
            // Show modal
            $('#bulkRefreshModal').modal('show');

            // Get all usernames first
            $.ajax({
                url: "{{ route('kol.bulk-usernames') }}",
                type: "GET",
                data: {
                    channel: channelSelector.val(),
                    niche: nicheSelector.val(),
                    skinType: skinTypeSelector.val(),
                    skinConcern: skinConcernSelector.val(),
                    contentType: contentTypeSelector.val(),
                    pic: picSelector.val()
                },
                success: function(response) {
                    if (response.usernames && response.usernames.length > 0) {
                        processBulkRefresh(response.usernames);
                    } else {
                        $('#statusText').text('No KOLs found');
                        finishBulkRefresh();
                    }
                },
                error: function() {
                    $('#statusText').text('Error fetching KOL list');
                    finishBulkRefresh();
                }
            });
        }

        // Process bulk refresh
        async function processBulkRefresh(usernames) {
            const total = usernames.length;
            let processed = 0;
            let successful = 0;
            let failed = 0;

            $('#statusText').text(`Processing ${total} KOLs...`);

            for (let i = 0; i < usernames.length; i++) {
                if (bulkRefreshStopped) {
                    $('#statusText').text('Refresh stopped by user');
                    break;
                }

                const username = usernames[i];
                const url = `{{ route('kol.refresh_follow', ['username' => ':username']) }}`.replace(':username', username);

                // Add row to results table
                const row = `
                    <tr id="refresh-row-${i}">
                        <td>${username}</td>
                        <td><span class="badge badge-warning">Processing...</span></td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                `;
                $('#bulkRefreshResults').append(row);

                try {
                    const response = await fetch(url);
                    const data = await response.json();

                    if (data.error) {
                        // Error case
                        $(`#refresh-row-${i}`).html(`
                            <td>${username}</td>
                            <td><span class="badge badge-danger">Failed</span></td>
                            <td>-</td>
                            <td>-</td>
                            <td>${data.error}</td>
                        `);
                        failed++;
                    } else {
                        // Success case
                        $(`#refresh-row-${i}`).html(`
                            <td>${username}</td>
                            <td><span class="badge badge-success">Success</span></td>
                            <td>${data.followers || 0}</td>
                            <td>${data.following || 0}</td>
                            <td>${data.message || 'Updated successfully'}</td>
                        `);
                        successful++;
                    }
                } catch (error) {
                    // Network or other error
                    $(`#refresh-row-${i}`).html(`
                        <td>${username}</td>
                        <td><span class="badge badge-danger">Failed</span></td>
                        <td>-</td>
                        <td>-</td>
                        <td>Network error</td>
                    `);
                    failed++;
                }

                processed++;
                const percentage = Math.round((processed / total) * 100);
                $('#bulkRefreshProgress').css('width', `${percentage}%`).attr('aria-valuenow', percentage).text(`${percentage}%`);
                $('#statusText').text(`Processed: ${processed}/${total} | Success: ${successful} | Failed: ${failed}`);

                // Scroll to bottom of results
                const resultsDiv = $('#bulkRefreshResults').parent().parent();
                resultsDiv.scrollTop(resultsDiv[0].scrollHeight);

                // Add delay to prevent API rate limiting
                await new Promise(resolve => setTimeout(resolve, 1000)); // 1 second delay
            }

            finishBulkRefresh();
        }

        // Finish bulk refresh
        function finishBulkRefresh() {
            bulkRefreshInProgress = false;
            $('#btnCloseBulkModal').prop('disabled', false);
            $('#btnStopBulkRefresh').hide();
            
            if (!bulkRefreshStopped) {
                $('#statusText').text('Bulk refresh completed!');
            }

            // Reload the main table
            kolTable.ajax.reload(null, false);
        }

        let kolTable = kolTableSelector.DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('kol.get') }}",
                data: function (d) {
                    d.channel = channelSelector.val();
                    d.niche = nicheSelector.val();
                    d.skinType = skinTypeSelector.val();
                    d.skinConcern = skinConcernSelector.val();
                    d.contentType = contentTypeSelector.val();
                    d.pic = picSelector.val();
                    // Add status affiliate filter
                    d.statusAffiliate = statusAffiliateSelector.val();
                    // Add followers range filter
                    d.followersMin = followersMinSelector.val();
                    d.followersMax = followersMaxSelector.val();
                }
            },
            columns: [
                {data: 'channel', name: 'channel'},
                {data: 'username', name: 'username'},
                {data: 'followers', name: 'followers'},
                {data: 'following', name: 'following'},
                {data: 'total_likes', name: 'total_likes'},
                {data: 'video_count', name: 'video_count'},
                {
                    data: 'engagement_rate_display', 
                    name: 'engagement_rate', 
                    orderable: true
                },
                // {data: 'program', name: 'program'},
                {
                    data: 'views_last_9_post_display', 
                    name: 'views_last_9_post', 
                    orderable: false
                },
                {
                    data: 'activity_posting_display', 
                    name: 'activity_posting', 
                    orderable: false
                },
                {
                    data: 'status_affiliate_display', 
                    name: 'status_affiliate', 
                    orderable: false
                },
                {data: 'refresh_follower', sortable: false, orderable: false},
                {data: 'actions', sortable: false, orderable: false}
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                // Refresh KPI data after table draw/filter
                loadKpiData();
            }
        });

        statusAffiliateSelector.change(function() {
            kolTable.draw();
        });

        let followersFilterTimeout;
        followersMinSelector.on('input', function() {
            clearTimeout(followersFilterTimeout);
            followersFilterTimeout = setTimeout(function() {
                kolTable.draw();
            }, 500); // Wait 500ms after user stops typing
        });

        followersMaxSelector.on('input', function() {
            clearTimeout(followersFilterTimeout);
            followersFilterTimeout = setTimeout(function() {
                kolTable.draw();
            }, 500); // Wait 500ms after user stops typing
        });

        // Clear filters functionality
        $('#btnResetFilter').click(function() {
            statusAffiliateSelector.val('');
            followersMinSelector.val('');
            followersMaxSelector.val('');
            kolTable.draw();
        });

        // btnExportKol.click(function () {
        //     let data = {
        //         channel: channelSelector.val(),
        //         niche: nicheSelector.val(),
        //         skinType: skinTypeSelector.val(),
        //         skinConcern: skinConcernSelector.val(),
        //         contentType: contentTypeSelector.val(),
        //         pic: picSelector.val()
        //     };

        //     let spinner = $('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
        //     btnExportKol.prop('disabled', true).append(spinner);

        //     let now = moment();
        //     let formattedTime = now.format('YYYYMMDD-HHmmss');

        //     $.ajax({
        //         url: "{{ route('kol.export') }}",
        //         type: "GET",
        //         data: data,
        //         xhrFields: {
        //             responseType: 'blob'
        //         },
        //         success: function(response) {
        //             let link = document.createElement('a');
        //             link.href = window.URL.createObjectURL(response);
        //             link.download = 'KOL-' + formattedTime + '.xlsx';
        //             link.click();

        //             btnExportKol.prop('disabled', false);
        //             spinner.remove();
        //         },
        //         error: function(xhr, status, error) {
        //             console.error(xhr, status, error);

        //             btnExportKol.prop('disabled', false);
        //             spinner.remove();
        //         }
        //     });
        // });

        $(function () {
            kolTable.draw()
        });

        $('#btnExportKol').click(function() {
            // Reset modal form
            $('#exportStatusAffiliate').val('');
            $('#exportFollowersMin').val('');
            $('#exportFollowersMax').val('');
            $('#exportCount').text('Click "Generate Export" to see the count of records');
            
            // Show modal
            $('#exportModal').modal('show');
        });

        // Preview count functionality
        $('#btnPreviewExport').click(function() {
            const btn = $(this);
            const originalText = btn.html();
            
            // Show loading state
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...');
            
            // Get count with current filters
            $.ajax({
                url: "{{ route('kol.get') }}", // Reuse existing endpoint
                type: "GET",
                data: {
                    channel: channelSelector.val(),
                    niche: nicheSelector.val(),
                    skinType: skinTypeSelector.val(),
                    skinConcern: skinConcernSelector.val(),
                    contentType: contentTypeSelector.val(),
                    pic: picSelector.val(),
                    statusAffiliate: $('#exportStatusAffiliate').val(),
                    followersMin: $('#exportFollowersMin').val(),
                    followersMax: $('#exportFollowersMax').val(),
                    length: 1, // Just get count, not actual data
                    draw: 1,
                    start: 0
                },
                success: function(response) {
                    const totalRecords = response.recordsFiltered || 0;
                    $('#exportCount').html(`
                        <i class="fas fa-chart-bar"></i> 
                        <strong>${totalRecords.toLocaleString()}</strong> records will be exported
                    `);
                    
                    if (totalRecords === 0) {
                        $('#exportCount').append('<br><small class="text-warning">No records match the current filters.</small>');
                        $('#btnConfirmExport').prop('disabled', true);
                    } else {
                        $('#btnConfirmExport').prop('disabled', false);
                    }
                },
                error: function() {
                    $('#exportCount').html('<i class="fas fa-exclamation-triangle text-danger"></i> Error getting record count');
                    $('#btnConfirmExport').prop('disabled', false);
                },
                complete: function() {
                    // Restore button state
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            });
        });

        // Confirm export functionality
        $('#btnConfirmExport').click(function() {
            const btn = $(this);
            const originalText = btn.html();
            
            // Show loading state
            btn.prop('disabled', true);
            btn.html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            
            let data = {
                channel: channelSelector.val(),
                niche: nicheSelector.val(),
                skinType: skinTypeSelector.val(),
                skinConcern: skinConcernSelector.val(),
                contentType: contentTypeSelector.val(),
                pic: picSelector.val(),
                statusAffiliate: $('#exportStatusAffiliate').val(),
                followersMin: $('#exportFollowersMin').val(),
                followersMax: $('#exportFollowersMax').val()
            };

            let now = moment();
            let formattedTime = now.format('YYYYMMDD-HHmmss');

            $.ajax({
                url: "{{ route('kol.export') }}",
                type: "GET",
                data: data,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(response);
                    link.download = 'KOL-Affiliate-Data-' + formattedTime + '.xlsx';
                    link.click();

                    // Close modal and show success message
                    $('#exportModal').modal('hide');
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Complete!',
                        text: 'Affiliate data has been exported successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr, status, error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Export Failed',
                        text: 'An error occurred while exporting data. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    // Restore button state
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            });
        });

        // Reset export modal when it's closed
        $('#exportModal').on('hidden.bs.modal', function() {
            $('#exportStatusAffiliate').val('');
            $('#exportFollowersMin').val('');
            $('#exportFollowersMax').val('');
            $('#exportCount').text('Click "Generate Export" to see the count of records');
            $('#btnConfirmExport').prop('disabled', false);
        });

        // Auto-update preview when filters change
        $('#exportStatusAffiliate, #exportFollowersMin, #exportFollowersMax').on('change input', function() {
            $('#exportCount').text('Click "Preview Count" to see updated count');
            $('#btnConfirmExport').prop('disabled', false);
        });

        $(document).on('click', '.refresh-follower', function() {
            const username = $(this).data('id'); // Get the username from the data-id attribute
            const url = `{{ route('kol.refresh_follow', ['username' => ':username']) }}`.replace(':username', username);

            Swal.fire({
                title: 'Refreshing...',
                text: 'Updating followers and following counts',
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    Swal.close();

                    if (data.error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.error,
                        });
                    } else {
                        kolTable.ajax.reload(null, false); // Reload table to reflect updated follower data
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                })
                .catch(error => {
                    Swal.close();
                    console.error('Error:', error);

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while refreshing data. Please try again later.',
                    });
                });
        });

        // Global variables to hold the Chart instances
        let orderPieChart;
        let rateBarChart;

        // Function to fetch data and render pie chart
        function fetchChannelData() {
            $.ajax({
                url: "{{ route('kol.chart') }}",
                type: "GET",
                success: function(response) {
                    renderChannelPieChart(response.labels, response.values);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching channel distribution data:', error);
                }
            });
        }

        // Function to fetch data and render bar chart for average rate
        function fetchAverageRateData() {
            $.ajax({
                url: "{{ route('kol.averageRate') }}",
                type: "GET",
                success: function(response) {
                    renderChannelBarChart(response.labels, response.values);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching average rate data:', error);
                }
            });
        }

        // Function to map predefined colors based on label names
        function getColorsForLabels(labels) {
            const colors = {
                "tiktok_video": "#000000",
                "instagram_feed": "#8939C4",
                "twitter_post": "#179CF4",
                "youtube_video": "#F10000",
                "shopee_video": "#EC4D28"
            };

            return labels.map(label => colors[label] || "#CCCCCC");
        }

        // Render the pie chart
        function renderChannelPieChart(labels, values) {
            const ctxPie = document.getElementById('channelPieChart').getContext('2d');

            if (orderPieChart) {
                orderPieChart.destroy();
            }

            orderPieChart = new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: getColorsForLabels(labels),
                        borderColor: getColorsForLabels(labels),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allow it to stretch to container
                    legend: {
                        position: 'right'
                    },
                    tooltips: {
                        callbacks: {
                            label: function(tooltipItem, data) {
                                let dataset = data.datasets[tooltipItem.datasetIndex];
                                let value = dataset.data[tooltipItem.index];
                                return data.labels[tooltipItem.index] + ': ' + value + '%';
                            }
                        }
                    }
                }
            });
        }

        // Render the bar chart for average rate per channel
        function renderChannelBarChart(labels, values) {
            const ctxBar = document.getElementById('channelBarChart').getContext('2d');

            if (rateBarChart) {
                rateBarChart.destroy();
            }

            rateBarChart = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Rate Card',
                        data: values,
                        backgroundColor: getColorsForLabels(labels),
                        borderColor: getColorsForLabels(labels),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allow it to stretch to container
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            },
                            scaleLabel: {
                                display: true,
                                labelString: 'Average Rate (IDR)'
                            }
                        }],
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Channel'
                            }
                        }]
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            });
        }

        // Fetch and render both charts on page load
        $(document).ready(function () {
            fetchChannelData();
            fetchAverageRateData();
        });

        let currentKolId = null;
        function openEditModal(kolId) {
            currentKolId = kolId;
            
            // Reset form and show loader
            $('#editKolForm')[0].reset();
            $('#editFormContent').hide();
            $('#editFormLoader').show();
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Open modal
            $('#editKolModal').modal('show');

            $('#editKolModal').on('hidden.bs.modal', function() {
                currentKolId = null;
                $('#editKolForm')[0].reset();
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            });
            
            // Load KOL data using route name
            $.get(`{{ route('kol.edit-data', ':kolId') }}`.replace(':kolId', kolId))
                .done(function(data) {
                    populateEditForm(data);
                    $('#editFormLoader').hide();
                    $('#editFormContent').show();
                })
                .fail(function() {
                    $('#editKolModal').modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load KOL data. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        function populateEditForm(data) {
            $('#edit_username').val(data.username || '');
            $('#edit_phone_number').val(data.phone_number || '');
            
            // Hidden fields to preserve existing required data
            $('#edit_channel').val(data.channel || '');
            $('#edit_niche').val(data.niche || '');
            $('#edit_average_view').val(data.average_view || '');
            $('#edit_skin_type').val(data.skin_type || '');
            $('#edit_skin_concern').val(data.skin_concern || '');
            $('#edit_content_type').val(data.content_type || '');
            $('#edit_rate').val(data.rate || '');
            $('#edit_pic_contact').val(data.pic_contact || '');
            
            // Set status_affiliate dropdown
            $('#edit_status_affiliate').val(data.status_affiliate || '');
            
            // Set radio buttons for views_last_9_post
            if (data.views_last_9_post === 1 || data.views_last_9_post === '1' || data.views_last_9_post === true) {
                $('#edit_views_yes').prop('checked', true);
            } else if (data.views_last_9_post === 0 || data.views_last_9_post === '0' || data.views_last_9_post === false) {
                $('#edit_views_no').prop('checked', true);
            } else {
                $('#edit_views_null').prop('checked', true);
            }
            
            // Set radio buttons for activity_posting
            if (data.activity_posting === 1 || data.activity_posting === '1' || data.activity_posting === true) {
                $('#edit_activity_active').prop('checked', true);
            } else if (data.activity_posting === 0 || data.activity_posting === '0' || data.activity_posting === false) {
                $('#edit_activity_inactive').prop('checked', true);
            } else {
                $('#edit_activity_null').prop('checked', true);
            }
            
            // Set form action using route name
            $('#editKolForm').attr('action', `{{ route('kol.update', ':kolId') }}`.replace(':kolId', data.id));
        }

        // Handle form submission
        $('#editKolForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const submitBtn = $('#saveKolBtn');
            const originalBtnText = submitBtn.html();
            
            // Show loading state
            submitBtn.prop('disabled', true);
            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            
            // Clear previous errors
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');
            
            // Submit form
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    // Close modal
                    $('#editKolModal').modal('hide');
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'KOL information updated successfully.',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    });
                    
                    // Refresh DataTable and KPI
                    kolTable.ajax.reload(null, false); // false = stay on current page
                    loadKpiData();
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        
                        Object.keys(errors).forEach(function(field) {
                            const input = $(`[name="${field}"]`);
                            input.addClass('is-invalid');
                            input.siblings('.invalid-feedback').text(errors[field][0]);
                        });
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Validation Error',
                            text: 'Please check the form and fix the errors.',
                            confirmButtonColor: '#ffc107'
                        });
                    } else {
                        // Other errors
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to update KOL information. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                complete: function() {
                    // Restore button state
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalBtnText);
                }
            });
        });

    </script>
@stop
