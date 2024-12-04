@extends('adminlte::page')

@section('title', 'Talent Content')

@section('content_header')
    <h1>Talent Content</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addTalentContentModal">
                                <i class="fas fa-plus"></i> Add Talent Content
                            </button>
                            <a href="#" id="exportButton" class="btn btn-success mr-2">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                            <button id="toggleCalendarBtn" class="btn btn-info">
                                <i class="fas fa-calendar"></i> See Calendar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column (Statistics Cards) -->
                        <div class="col-12 col-md-4">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="small-box bg-info">
                                        <div class="inner">
                                            <h4 id="todayCount">0</h4>
                                            <p>Today's Count</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-calendar-day"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="small-box bg-maroon">
                                        <div class="inner">
                                            <h4 id="doneFalseCount">0</h4>
                                            <p>Count Not Done</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="small-box bg-success">
                                        <div class="inner">
                                            <h4 id="doneTrueCount">0</h4>
                                            <p>Count Done</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="small-box bg-purple">
                                        <div class="inner">
                                            <h4 id="totalCount">0</h4>
                                            <p>Total Count</p>
                                        </div>
                                        <div class="icon">
                                            <i class="fas fa-list"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3 outer-card">
                                <div class="card-header">
                                    Notification
                                </div>
                                <div class="card-body">
                                    <div class="inner-scrollable" id="todayTalentContainer"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <div id="calendar"></div>
                            <!-- <div id="calendar" style="display: none;"></div> -->
                            <!-- <div id="lineChartContainer" class="mt-3">
                                <div class="form-group">
                                    <label for="productFilter">Select Product:</label>
                                    <select id="productFilter" class="form-control">
                                    </select>
                                </div>
                                <canvas id="lineChart"></canvas>
                            </div> -->
                        </div>
                    </div>
                </div>

                
                <div class="card-body">
                <div class="justify-content-between align-items-center mb-3">
                    <div class="col-auto">
                    <div class="row align-items-center">
                        <div class="col-3">
                            <input type="text" class="form-control filterDate" id="filterDealingDate" placeholder="Select Dealing Date Range" autocomplete="off">
                        </div>
                        <div class="col-3">
                            <input type="text" class="form-control filterDate" id="filterPostingDate" placeholder="Select Posting Date Range" autocomplete="off">
                        </div>
                        <div class="col-1">
                            <div class="icheck-primary d-inline">
                                <input type="checkbox" id="filterDone">
                                <label for="filterDone"> Done </label>
                            </div>
                        </div>
                        <div class="col-3">
                            <select id="filterProduk" class="form-control select2">
                                <option value="">All Product</option>
                            </select>
                        </div>
                        <div class="col-2">
                            <button id="resetFilterBtn" class="btn btn-outline-secondary"> Reset Filter </button>
                        </div>
                    </div>

                    </div>
                </div>
                    <table id="talentContentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Dealing Upload Date</th>
                                <th>Posting Date</th>
                                <th>Done</th>
                                <th>Additional Info</th>
                                <th>Action</th>
                                <th>Refund</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('admin.talent_content.modals.add_talent_content_modal')
    @include('admin.talent_content.modals.edit_talent_content_modal')
    @include('admin.talent_content.modals.view_talent_content_modal')
    @include('admin.talent_content.modals.add_link_content_modal')
@stop


@section('css')
<style>
    .outer-card {
        overflow-y: auto;
        max-height: 400px; 
    }

    .inner-scrollable {
        max-height: 250px; 
        overflow-y: auto;
    }

    .sub-card {
        border: 1px solid #ddd;
    }
    #lineChartContainer {
        height: 500px;
    }

</style>
@endsection


@section('js')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js'></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        $('#filterProduk').select2({
            placeholder: "All Product",
            allowClear: true,
            width: '100%',
            theme: 'bootstrap4'
        });

        function populateProdukFilter() {
            fetch('{{ route('talent_content.get_products') }}')  
                .then(response => response.json())
                .then(data => {
                    const produkSelect = $('#filterProduk');
                    produkSelect.empty();  
                    produkSelect.append('<option value="">All Product</option>'); 
                    data.forEach(produk => {
                        produkSelect.append(`<option value="${produk.short_name}">${produk.short_name}</option>`);
                    });
                })
                .catch(error => console.error('Error fetching product list:', error));
        }
        populateProdukFilter(); 

        const filterDone = $('#filterDone');
        var table = $('#talentContentTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('talent_content.data') }}',
                data: function (d) {
                    d.filterDealingDate = $('#filterDealingDate').val(); 
                    d.filterPostingDate = $('#filterPostingDate').val();
                    d.filterDone = $('#filterDone').is(':checked') ? 1 : ''; 
                    d.filterProduct = $('#filterProduk').val();
                }
            },
            columns: [
                { data: 'id', name: 'id', visible: false },
                { data: 'username', name: 'talents.username' }, 
                {
                    data: 'dealing_upload_date', 
                    name: 'dealing_upload_date',
                    render: function(data) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                }, 
                {
                    data: 'posting_date', 
                    name: 'posting_date',
                    render: function(data) {
                        if (data) {
                            let date = new Date(data);
                            return ('0' + date.getDate()).slice(-2) + '/' + 
                                   ('0' + (date.getMonth() + 1)).slice(-2) + '/' + 
                                   date.getFullYear();
                        }
                        return '';
                    }
                },
                { data: 'done', name: 'done', orderable: false, searchable: false },
                { data: 'status_and_link', name: 'status_and_link', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false },
                { data: 'refund', name: 'refund', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']]
        });

        filterDone.change(function() {
            table.ajax.reload();
        });

        $('#filterProduk').change(function() {
            table.ajax.reload();
        });
        const filterDealingDate = $('#filterDealingDate').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear' }
        });

        const filterPostingDate = $('#filterPostingDate').daterangepicker({
            autoUpdateInput: false,
            locale: { cancelLabel: 'Clear' }
        });

        filterDealingDate.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            table.ajax.reload(); 
        });

        filterDealingDate.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.ajax.reload();
        });

        filterPostingDate.on('apply.daterangepicker', function(ev, picker) {
            var selectedRange = picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD');
            $(this).val(selectedRange);
            table.ajax.reload(); 
        });

        filterPostingDate.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            table.ajax.reload();
        });

        $('#resetFilterBtn').click(function () {
            $('#filterDealingDate').val('');
            $('#filterPostingDate').val('');
            $('#filterProduk').val('');
            $('#filterDone').prop('checked', false);
            table.ajax.reload();
        });
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: "{{ route('talent_content.calendar') }}", 
                    method: "GET",
                    success: function(data) {
                        var events = [];
                        $.each(data.data, function(index, item) {
                            if (item.posting_date) {
                                events.push({
                                    title: item.talent_name, 
                                    start: item.posting_date.split('T')[0], 
                                    allDay: true 
                                });
                            }
                        });
                        successCallback(events); 
                    },
                    error: function() {
                        failureCallback(); 
                    }
                });
            }
        });
        calendar.render();
        function fetchContentCounts() {
            $.ajax({
                url: "{{ route('talent_content.count') }}",
                method: "GET",
                success: function(data) {
                    $('#todayCount').text(data.today_count);
                    $('#doneFalseCount').text(data.done_false_count);
                    $('#doneTrueCount').text(data.done_true_count);
                    $('#totalCount').text(data.total_count);
                },
                error: function() {
                    alert('Failed to fetch content counts.');
                }
            });
        }
        fetchContentCounts();
        function fetchTodayTalents() {
            $.ajax({
                url: "{{ route('talent_content.today') }}", 
                method: "GET",
                success: function(data) {
                    var container = $('#todayTalentContainer');
                    container.empty(); 
                    if (data.length) {
                        $.each(data, function(index, item) {
                            var campaignInfo = item.campaign_title ? `<p>Campaign: ${item.campaign_title}</p>` : '';
                            var subCard = `
                                <div class="sub-card card mb-2">
                                    <div class="card-header">Akun <strong>${item.username}</strong></div>
                                    <div class="card-body">
                                        <p>Harus upload konten hari ini</p>
                                        ${campaignInfo}
                                    </div>
                                </div>
                            `;
                            container.append(subCard);
                        });
                    } else {
                        container.append('<p>No talents available for today.</p>');
                    }
                },
                error: function() {
                    alert('Failed to fetch talents for today.');
                }
            });
        }
        fetchTodayTalents();
        var talentChoices, campaignChoices;
        
        $('#addTalentContentModal').on('show.bs.modal', function() {
            $.ajax({
                url: "{{ route('talent_content.get') }}",
                method: "GET",
                success: function(data) {
                    var select = $('#talent_id');
                    select.empty();
                    select.append('<option value="">Select Talent</option>');
                    $.each(data, function(index, talent) {
                        select.append('<option value="' + talent.id + '">' + talent.username + '</option>');
                    });

                    if (talentChoices) {
                        talentChoices.destroy();
                    }
                    talentChoices = new Choices(select[0], {
                        searchEnabled: true,
                        placeholder: true,
                        placeholderValue: 'Select Talent'
                    });
                },
                error: function() {
                    alert('Failed to fetch talents.');
                }
            });
            $.ajax({
                url: "{{ route('talent_content.getCampaigns') }}",
                method: "GET",
                success: function(data) {
                    var select = $('#campaign_id');
                    select.empty();
                    select.append('<option value="">Select Campaign</option>');
                    $.each(data, function(index, campaign) {
                        select.append('<option value="' + campaign.id + '">' + campaign.title + '</option>');
                    });

                    if (campaignChoices) {
                        campaignChoices.destroy();
                    }
                    campaignChoices = new Choices(select[0], {
                        searchEnabled: true,
                        placeholder: true,
                        placeholderValue: 'Select Campaign'
                    });
                },
                error: function() {
                    alert('Failed to fetch campaigns.');
                }
            });
        });
        
        $('#talentContentTable').on('click', '.editButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('talent_content.edit', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#editTalentContentForm').attr('action', '{{ route('talent_content.update', ':id') }}'.replace(':id', id));

                    const addOneDay = (dateString) => {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        date.setDate(date.getDate() + 1);
                        return date.toISOString().split('T')[0];
                    };
                    
                    $('#edit_talent_id').val(response.talentContent.talent_id);
                    $('#edit_dealing_upload_date').val(addOneDay(response.talentContent.dealing_upload_date));
                    $('#edit_posting_date').val(addOneDay(response.talentContent.posting_date));
                    $('#edit_done').val(response.talentContent.done ? 1 : 0);
                    $('#edit_upload_link').val(response.talentContent.upload_link);
                    $('#edit_final_rate_card').val(response.talentContent.final_rate_card);
                    $('#edit_pic_code').val(response.talentContent.pic_code);
                    $('#edit_product').val(response.talentContent.product);
                    $('#edit_boost_code').val(response.talentContent.boost_code);
                    $('#edit_kerkun').val(response.talentContent.kerkun ? 1 : 0);
                    
                    $('#editTalentContentModal').modal('show');
                },
                error: function(response) {
                    alert('Error: ' + response.message);
                }
            });
        });

        $('#talentContentTable').on('click', '.viewButton', function() {
            var id = $(this).data('id');

            $.ajax({
                url: '{{ route('talent_content.show', ':id') }}'.replace(':id', id),
                method: 'GET',
                success: function(response) {
                    const formatDate = (dateString) => {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                        const year = date.getFullYear();
                        return `${day}/${month}/${year}`;
                    };

                    $('#view_talent_name').val(response.talentContent.talent_name);
                    $('#view_dealing_upload_date').val(formatDate(response.talentContent.dealing_upload_date));
                    $('#view_posting_date').val(formatDate(response.talentContent.posting_date));
                    $('#view_product').val(response.talentContent.product);
                    $('#view_final_rate_card').val(response.talentContent.final_rate_card);
                    $('#view_done').val(response.talentContent.done ? 'Yes' : 'No');
                    $('#view_upload_link').val(response.talentContent.upload_link);
                    $('#view_campaign_name').val(response.talentContent.campaign_title);
                    $('#view_pic_code').val(response.talentContent.pic_code);
                    $('#view_boost_code').val(response.talentContent.boost_code);
                    $('#view_kerkun').val(response.talentContent.kerkun ? 'Yes' : 'No');
                    
                    // Embed content based on channel
                    $('#contentEmbed').html('');  // Clear previous embed content

                    if (response.talentContent.upload_link !== '') {
                        if (response.talentContent.channel === 'tiktok_video') {
                            // Embed TikTok video
                            $.ajax({
                                url: "https://www.tiktok.com/oembed?url=" + response.talentContent.upload_link,
                                type: 'GET',
                                success: function (response) {
                                    $('#contentEmbed').html(response.html);
                                },
                                error: function (xhr, status, error) {
                                    console.error(xhr.responseText);
                                }
                            });
                        } else if (response.talentContent.channel === 'twitter_post') {
                            let twitterLink = response.talentContent.upload_link.replace('https://x.com/', 'https://twitter.com/');
                            let tweetEmbed = `
                                <blockquote class="twitter-tweet">
                                    <a href="${twitterLink}"></a>
                                </blockquote>
                            `;
                            $('#contentEmbed').html(tweetEmbed);
                            twttr.widgets.load(document.getElementById('contentEmbed'));

                        } else if (response.talentContent.channel === 'instagram_feed') {
                            let cleanLink = response.talentContent.upload_link.split('?')[0];
                            let linkIg = cleanLink.endsWith('/');
                            let embedLink = linkIg ? cleanLink + 'embed' : cleanLink + '/embed';

                            let embedIg = '<iframe width="315" height="560" src="' + embedLink + '" frameborder="0"></iframe>';
                            $('#contentEmbed').html(embedIg);
                        } else if (response.talentContent.channel === 'youtube_video') {
                            let videoId = response.talentContent.upload_link.split('/').pop(); 
                            let embedLink = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1'; 

                            let embedYoutube = `<iframe width="315" height="560" src="${embedLink}" frameborder="0" allowfullscreen></iframe>`;
                            $('#contentEmbed').html(embedYoutube);
                        } else if (response.talentContent.channel === 'shopee_video') {
                            let shopeeEmbed = `<iframe src="${response.talentContent.upload_link}" width="315" height="560" frameborder="0" allowfullscreen></iframe>`;
                            $('#contentEmbed').html(shopeeEmbed);

                        } else {
                            let buttonEmbed = '<a href="'+ response.talentContent.upload_link +'" target="_blank" class="btn btn-primary">Go to Content</a>';
                            $('#contentEmbed').html(buttonEmbed);
                        }
                        // You can add more channel conditions here (e.g., YouTube, Instagram, etc.)
                    }
                    else {
                        $('#contentEmbed').html('');  // Clear contentEmbed if no link is provided
                    }

                    $('#viewTalentContentModal').modal('show');
                },
                error: function(response) {
                    alert('Error: ' + response.message);
                }
            });
        });


        $('#talentContentTable').on('click', '.deleteButton', function(e) {
            e.preventDefault(); 
            var id = $(this).data('id');
            var url = '{{ route('talent_content.destroy', ':id') }}'.replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    'Talent content has been deleted.',
                                    'success'
                                );
                                table.ajax.reload();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    'There was an issue deleting the talent content.',
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error!',
                                'There was an issue deleting the talent content.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        $('#talentContentTable').on('click', '.addLinkButton', function() {
            var id = $(this).data('id');
            $('#addLinkContentForm').attr('action', '{{ route('talent_content.addLink', ':id') }}'.replace(':id', id));
            $('#addLinkContentModal').modal('show');
        });

        $('#addLinkContentForm').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');

            console.log(form.serialize());

            $.ajax({
                url: url,
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if (response.success) {
                        $('#addLinkContentModal').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Success', 'Link added successfully', 'success');
                    } else {
                        Swal.fire('Error', 'Failed to add link', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to add link', 'error');
                }
            });
        });

        $('#talentContentTable').on('click', '.refundButton', function() {
            var id = $(this).data('id');
            var url = '{{ route('talent_content.refund', ':id') }}'.replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: 'This will mark the content as refunded.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, refund it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Refunded!', 'Talent content has been marked as refunded.', 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error!', 'There was an issue marking the content as refunded.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'There was an issue marking the content as refunded.', 'error');
                        }
                    });
                }
            });
        });

        $('#talentContentTable').on('click', '.unRefundButton', function() {
            var id = $(this).data('id');
            var url = '{{ route('talent_content.unrefund', ':id') }}'.replace(':id', id);

            Swal.fire({
                title: 'Are you sure?',
                text: 'This will unmark the content as refunded.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, unrefund it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Unrefunded!', 'Talent content has been unmarked as refunded.', 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error!', 'There was an issue unmarking the content as refunded.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'There was an issue unmarking the content as refunded.', 'error');
                        }
                    });
                }
            });
        });

        let lineChart;

        const fetchDataAndInitChart = () => {
            fetch("{{ route('talent_content.get_line_data') }}")
                .then(response => response.json()) 
                .then(data => {
                    const labels = data.labels;
                    const datasets = data.datasets;

                    const products = datasets.map(dataset => dataset.label);
                    const uniqueProducts = [...new Set(products)];

                    const productFilter = document.getElementById('productFilter');
                    uniqueProducts.forEach((product, index) => {
                        const option = document.createElement('option');
                        option.value = index;
                        option.textContent = product;
                        productFilter.appendChild(option);
                    });

                    const ctx = document.getElementById('lineChart').getContext('2d');
                    lineChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date'
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Count'
                                    }
                                }
                            }
                        }
                    });
                    productFilter.addEventListener('change', function (e) {
                        const selectedProductIndex = parseInt(e.target.value);
                        const selectedProduct = uniqueProducts[selectedProductIndex];

                        // Filter datasets to show only the selected product
                        const filteredDatasets = datasets.filter(dataset => {
                            return dataset.label === selectedProduct || selectedProductIndex === 0;
                        });

                        // Update the chart with the filtered datasets
                        lineChart.data.datasets = filteredDatasets;
                        lineChart.update();
                    });
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        }

        fetchDataAndInitChart();

        $('#toggleCalendarBtn').click(function() {
            var calendarEl = $('#calendar');
            var buttonEl = $(this);
            var lineChartContainer = $('#lineChartContainer'); 

            if (calendarEl.is(':visible')) {
                calendarEl.hide();
                lineChartContainer.show();
                buttonEl.html('<i class="fas fa-calendar"></i> Show Calendar');
            } else {
                calendarEl.show();
                lineChartContainer.hide();
                buttonEl.html('<i class="fas fa-calendar"></i> Hide Calendar');
                calendar.render();
            }
        });

        $('#exportButton').on('click', function(e) {
            e.preventDefault();
            var queryString = '?';
            var postingDate = $('#filterPostingDate').val();
            if (postingDate) {
                queryString += 'filterPostingDate=' + encodeURIComponent(postingDate);
            }
            window.location.href = '{{ route('talent_content.export') }}' + queryString;
        });

    });
</script>
@stop







