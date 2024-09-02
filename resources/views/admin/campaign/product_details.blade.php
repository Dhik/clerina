@extends('adminlte::page')

@section('title', $product->product . ' Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $product->product }} Details</h1>
        <div>
            <!-- Add any additional buttons or controls if needed -->
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="statistic">
                                <div class="row">
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-info">
                                            <div class="inner">
                                                <h4 id="totalViews">Loading...</h4>
                                                <p>Video Views</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-eye"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-success">
                                            <div class="inner">
                                                <h4 id="totalLikes">Loading...</h4>
                                                <p>Likes</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-thumbs-up"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-maroon">
                                            <div class="inner">
                                                <h4 id="totalComments">Loading...</h4>
                                                <p>Comments</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-comment"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-6">
                                        <div class="small-box bg-purple">
                                            <div class="inner">
                                                <h4 id="totalInfluencers">Loading...</h4>
                                                <p>Influencers</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-users"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Top Engagements</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Username</th>
                                                            <th>Engagement</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="topEngagements">
                                                        <!-- Data will be populated via JS -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Top Views</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Username</th>
                                                            <th>Views</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="topViews">
                                                        <!-- Data will be populated via JS -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Top Likes</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Username</th>
                                                            <th>Likes</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="topLikes">
                                                        <!-- Data will be populated via JS -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5>Top Comments</h5>
                                            </div>
                                            <div class="card-body">
                                                <table class="table table-striped table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Username</th>
                                                            <th>Comments</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="topComments">
                                                        <!-- Data will be populated via JS -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <!-- Add footer content here if needed -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        // Replace :productName with actual product name from your backend
        let productName = '{{ $product->product }}';
        
        let apiUrl = "{{ route('campaignContent.getProductStatistics', ['productName' => ':productName']) }}".replace(':productName', encodeURIComponent(productName));

        // Fetch statistics data and update the UI
        function fetchProductStatistics() {
            $.ajax({
                url: apiUrl,
                method: 'GET',
                success: function(data) {
                    // Update the statistics counts
                    $('#totalViews').text(new Intl.NumberFormat().format(data.totalViews));
                    $('#totalLikes').text(new Intl.NumberFormat().format(data.totalLikes));
                    $('#totalComments').text(new Intl.NumberFormat().format(data.totalComments));
                    $('#totalInfluencers').text(new Intl.NumberFormat().format(data.totalInfluencers));

                    // Populate top engagements table
                    populateTable('#topEngagements', data.topEngagements, 'total_engagement');

                    // Populate top views table
                    populateTable('#topViews', data.topViews, 'total_views');

                    // Populate top likes table
                    populateTable('#topLikes', data.topLikes, 'total_likes');

                    // Populate top comments table
                    populateTable('#topComments', data.topComments, 'total_comments');
                },
                error: function() {
                    alert('Failed to load product statistics.');
                }
            });
        }

        // Function to populate tables
        function populateTable(selector, data, field) {
            let tableBody = $(selector);
            tableBody.empty();

            data.forEach(function(item) {
                let row = `
                    <tr>
                        <td>${item.username}</td>
                        <td>${new Intl.NumberFormat().format(item[field])}</td>
                    </tr>
                `;
                tableBody.append(row);
            });
        }

        // Fetch statistics when the page loads
        $(document).ready(function() {
            fetchProductStatistics();
        });
    </script>
@stop
