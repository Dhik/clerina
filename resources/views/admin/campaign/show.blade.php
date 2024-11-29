@extends('adminlte::page')

@section('title', trans('labels.campaign'))

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $campaign->title }} : {{ $campaign->start_date }} - {{ $campaign->end_date }}</h1>
        <div>
            @can('updateCampaign', $campaign)
                <a href="{{ route('campaign.edit', $campaign->id) }}" class="btn btn-outline-success mr-1">
                    {{ trans('buttons.edit') }}
                </a>
            @endcan
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#statistic" data-toggle="tab">{{ trans('labels.statistic') }}</a></li>
                            <!-- <li class="nav-item"><a class="nav-link" href="#offer" data-toggle="tab">{{ trans('labels.offer') }}</a></li> -->
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="statistic">
                                @include('admin.campaign.statistic')
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        {{ trans('labels.created_by') }} {{ $campaign->createdBy->name ?? '' }}

                        @can('deleteCampaign', $campaign)
                            <a href="#" class="delete-campaign">{{ trans('buttons.delete') }}</a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        
        let campaignId = '{{ $campaign->id }}';
        const filterStatus = $('#filterStatus');

        const filterDates = $('#filterDates');
        const filterInfluencer = $('#filterInfluencer');
        const filterProduct = $('#filterProduct');
        const filterPlatform = $('#filterPlatform');
        const filterFyp = $('#filterFyp');
        const filterPayment = $('#filterPayment');
        const filterDelivery = $('#filterDelivery');

        const contentTable = $('#contentTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: false, 
            ajax: {
                url: "{{ route('campaignContent.getJson', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                data: function (d) {
                    d.filterInfluencer = filterInfluencer.val();
                    d.filterProduct = filterProduct.val();
                    d.filterPlatform = filterPlatform.val();
                    d.filterFyp = filterFyp.prop('checked');
                    d.filterPayment = filterPayment.prop('checked');
                    d.filterDelivery = filterDelivery.prop('checked');
                }
            },
            columns: [
                { data: 'username' },
                { data: 'channel', orderable: false },
                { data: 'product', orderable: false },
                { data: 'task', orderable: false },
                { data: 'kode_ads', orderable: true, visible: false },
                { data: 'upload_date', orderable: true, visible: false },
                { data: 'like', className: "text-right", orderable: true }, 
                { data: 'comment', className: "text-right", orderable: true }, 
                { data: 'view', className: "text-right", orderable: true }, 
                { data: 'cpm', className: "text-right", orderable: true }, 
                { data: 'engagement_rate', className: "text-right", orderable: true }, 
                { data: 'kol_followers', className: "text-right", orderable: true }, 
                { data: 'tiering', className: "text-right", orderable: false }, 
                { data: 'additional_info', orderable: false }, 
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[4, 'desc']],
        });

        function resetFilters() {
            $('#filterDates').val('');
            $('#filterPlatform').val('').trigger('change');
            $('#filterFyp').prop('checked', false);
            $('#filterPayment').prop('checked', false);
            $('#filterDelivery').prop('checked', false);
            $('#filterInfluencer').val('').trigger('change'); 
            $('#filterProduct').val('').trigger('change'); 

            contentTable.ajax.reload();
            offerTable.ajax.reload();

            updateCard(); 
            initChart(); 
        }

        $(window).on('load', function () {
            resetFilters(); 
        });

        $('#resetFilterBtn').click(function () {
            resetFilters(); 
        });
        
        $('#refreshAllBtn').click(function() {
            $('#refreshAllModal').modal('show');

            $.ajax({
                url: "{{ route('campaignContent.getDataTableForRefresh', ['campaignId' => $campaign->id]) }}",
                method: 'GET',
                success: function(data) {
                    let contentList = '';
                    data.forEach(function(content) {
                        contentList += `
                            <tr id="content-${content.id}">
                                <td>${content.username}</td>
                                <td>${content.task_name}</td>
                                <td>${content.channel}</td>
                                <td>${content.product}</td>
                                <td class="text-center"><i class="fas fa-clock text-warning"></i></td>
                            </tr>
                        `;
                    });
                    $('#refreshAllContentList').html(contentList);
                },
                error: function() {
                    alert('Failed to load content list.');
                }
            });
        });

        $('#confirmRefreshAll').click(function() {
            const contents = $('#refreshAllContentList tr');
            const totalContents = contents.length;
            let completedContents = 0;

            contents.each(function(index, contentRow) {
                const contentId = $(contentRow).attr('id').split('-')[1];
                
                $(`#content-${contentId} td:last-child`).html('<i class="fas fa-spinner fa-spin text-primary"></i>');

                $.ajax({
                    url: "{{ route('statistic.refresh', ['campaignContent' => ':campaignContentId']) }}".replace(':campaignContentId', contentId),
                    method: 'GET',
                    success: function(data) {
                        $(`#content-${contentId} td:last-child`).html('<i class="fas fa-check text-success"></i>');
                        completedContents++;
                        updateProgressBar(completedContents, totalContents);
                    },
                    error: function() {
                        $(`#content-${contentId} td:last-child`).html('<i class="fas fa-times text-danger"></i>');
                        completedContents++;
                        updateProgressBar(completedContents, totalContents);
                    }
                });
            });
        });

        function updateProgressBar(completed, total) {
            const progressPercentage = Math.round((completed / total) * 100);
            $('#refreshProgressBar').css('width', progressPercentage + '%').attr('aria-valuenow', progressPercentage).text(progressPercentage + '%');
            if (progressPercentage === 100) {
                $('#refreshAllModal').modal('hide');
                location.reload();
            }
        }

        $('#refreshFollowersBtn').click(function() {
            $('#refreshFollowersModal').modal('show');

            $.ajax({
                url: "{{ route('campaignContent.getDataTableForRefresh', ['campaignId' => $campaign->id]) }}",
                method: 'GET',
                success: function(data) {
                    const uniqueUsers = {};
                    let userList = '';
                    data.forEach(function(user) {
                        const baseUsername = user.username.replace(/\s*\(.*?\)\s*/g, '');
                        if (!uniqueUsers[user.username]) {
                            uniqueUsers[user.username] = user;
                            userList += `
                                <tr id="user-${user.id}">
                                    <td>${user.username}</td>
                                    <td>${user.channel}</td>
                                    <td class="text-center"><i class="fas fa-clock text-warning"></i></td>
                                </tr>
                            `;
                        }
                    });
                    $('#refreshFollowersList').html(userList);
                },
                error: function() {
                    alert('Failed to load user list.');
                }
            });
        });

        $('#confirmRefreshFollowers').click(function() {
            const rows = $('#refreshFollowersList tr');
            const totalUsers = rows.length;
            let completedUsers = 0;

            rows.each(function(index, row) {
                const username = $(row).find('td:first').text();
                const userId = $(row).attr('id').split('-')[1];

                $(`#user-${userId} td:last-child`).html('<i class="fas fa-spinner fa-spin text-primary"></i>');

                $.ajax({
                    url: "{{ route('keyOpinionLeader.refreshFollowersFollowing', ['username' => ':username']) }}".replace(':username', username),
                    method: 'GET',
                    success: function(data) {
                        $(`#user-${userId} td:last-child`).html('<i class="fas fa-check text-success"></i>');
                        completedUsers++;
                        updateProgressBar(completedUsers, totalUsers);
                    },
                    error: function() {
                        $(`#user-${userId} td:last-child`).html('<i class="fas fa-times text-danger"></i>');
                        completedUsers++;
                        updateProgressBar(completedUsers, totalUsers);
                    }
                });
            });
        });

        function updateProgressBarFollowers(completed, total) {
            const progressPercentage = Math.round((completed / total) * 100);
            $('#refreshFollowersProgressBar').css('width', progressPercentage + '%').attr('aria-valuenow', progressPercentage).text(progressPercentage + '%');
            if (progressPercentage === 100) {
                $('#refreshFollowersModal').modal('hide');
                location.reload();
            }
        }

        let offerTable = $('#offerTable').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: {
                url: "{{ route('offer.getByCampaignId', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                data: function (d) {
                    d.status = filterStatus.val();
                }
            },
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'id', name: 'id'},
                {data: 'created_by_name', name: 'createdBy.name'},
                {data: 'key_opinion_leader_username', name: 'username'},
                {data: 'rate_formatted', name: 'rate_per_slot'},
                {data: 'key_opinion_leader_cpm', name: 'keyOpinionLeader.cpm'},
                {data: 'key_opinion_leader_average_view', name: 'keyOpinionLeader.average_view'},
                {data: 'benefit', name: 'benefit'},
                {data: 'negotiate', name: 'negotiate'},
                {data: 'acc_slot', name: 'acc_slot'},
                {data: 'status_label', name: 'status'},
                {data: 'actions', sortable: false, orderable: false}
            ],
            columnDefs: [
                { "targets": [0, 1], "visible": false },
                { "targets": [4, 5, 6, 9], "className": "text-right" },
                { "targets": [10, 11], "className": "text-center" },
            ],
            order: [[0, 'desc']]
        });

        

        contentTable.on('draw.dt', function() {
            $('[data-toggle="tooltip"]').tooltip();
        });

        filterDates.change(function () {
            contentTable.ajax.reload();
            updateCard();
            initChart();
        });

        filterPlatform.change(function() {
            contentTable.ajax.reload();
        });

        filterFyp.change(function() {
            contentTable.ajax.reload();
        });

        filterPayment.change(function() {
            contentTable.ajax.reload();
        });

        filterDelivery.change(function() {
            contentTable.ajax.reload();
        });

        $(function () {
            offerTable.draw();
            $('[data-toggle="tooltip"]').tooltip();

            $('#usernameOffer').select2({
                theme: 'bootstrap4',
                dropdownParent: $("#offerModal"),
                placeholder: '{{ trans('placeholder.select', ['field' => trans('labels.username_kol')]) }}',
                ajax: {
                    url: "{{ route('kol.select') }}",
                    data: function (params) {
                        return {
                            search: params.term,
                        };
                    },
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        let formattedResults = [];
                        data.forEach(function(result) {
                            formattedResults.push({
                                id: result.id,
                                text: result.username + ' - ' +result.channel
                            });
                        });
                        return {
                            results: formattedResults
                        };
                    },
                    cache: true
                }
            });

            const startFilter = moment().startOf('month'); 
            const endFilter = moment().endOf('month');

            $('.filterDate').daterangepicker({
                startDate: startFilter,
                endDate: endFilter,
                autoApply: false,
                showDropdowns: true,
                locale: {
                    format: 'DD/MM/YYYY'
                }
            });

            bsCustomFileInput.init();

            $('#username').select2({
                theme: 'bootstrap4',
                placeholder: '{{ trans('placeholder.select', ['field' => trans('labels.influencer')]) }}',
                dropdownParent: $("#contentModal"),
                ajax: {
                    url: "{{ route('campaignContent.select', ['campaignId' => ':campaignId']) }}".replace(':campaignId', campaignId),
                    data: function (params) {
                        return {
                            search: params.term,
                        };
                    },
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        let formattedResults = [];
                        data.forEach(function(result) {
                            formattedResults.push({
                                id: result.key_opinion_leader_id,
                                text: result.key_opinion_leader.username + ' - ' +result.key_opinion_leader.channel+ ' - {{ trans('labels.remaining_slot') }}:'+ result.remaining_slot
                            });
                        });

                        return {
                            results: formattedResults
                        };
                    },
                    cache: true
                }
            });
        });
    </script>

    @include('admin.campaign.content.script.script-button-info')
    @include('admin.campaign.content.script.script-refresh')
    @include('admin.campaign.content.script.script-manual-statistic')
    @include('admin.campaign.content.script.script-add-content')
    @include('admin.campaign.content.script.script-update-content')
    @include('admin.campaign.content.script.script-import')
    @include('admin.campaign.content.script.script-import-kol')
    @include('admin.campaign.content.script.script-detail-content')
    @include('admin.campaign.content.script.script-card')
    @include('admin.campaign.content.script.script-chart')
    @include('admin.campaign.content.script.script-delete-content')
@endsection
