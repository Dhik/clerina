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
                    <tbody>
                        @foreach ($topEngagements as $stat)
                            <tr>
                                <td>{{ $stat->username }}</td>
                                <td>{{ number_format($stat->engagement) }}</td>
                            </tr>
                        @endforeach
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
                    <tbody>
                        @foreach ($topViews as $stat)
                            <tr>
                                <td>{{ $stat->username }}</td>
                                <td>{{ number_format($stat->view) }}</td>
                            </tr>
                        @endforeach
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
                    <tbody>
                        @foreach ($topLikes as $stat)
                            <tr>
                                <td>{{ $stat->username }}</td>
                                <td>{{ number_format($stat->like) }}</td>
                            </tr>
                        @endforeach
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
                    <tbody>
                        @foreach ($topComments as $stat)
                            <tr>
                                <td>{{ $stat->username }}</td>
                                <td>{{ number_format($stat->comment) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
