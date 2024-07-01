<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Top Engagements</h5>
            </div>
            <div class="card-body">
                <table id="top-engagements-table" class="table table-striped table-bordered">
                    <tbody>
                    <!-- Table body will be populated by jQuery -->
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
                <table id="top-likes-table" class="table table-striped table-bordered">
                    <tbody>
                    <!-- Table body will be populated by jQuery -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Top Comment</h5>
            </div>
            <div class="card-body">
                <table id="top-comments-table" class="table table-striped table-bordered">
                    <tbody>
                    <!-- Table body will be populated by jQuery -->
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
                <table id="top-views-table" class="table table-striped table-bordered">
                    <tbody>
                    <!-- Table body will be populated by jQuery -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Top Produk</h5>
            </div>
            <div class="card-body">
                <table id="top-product-table" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.product') }}</th>
                            <th>{{ trans('labels.views') }}</th>
                            <th>{{ trans('labels.spend') }}</th>
                            <th>{{ trans('labels.total_content') }}</th>
                            <th data-toggle="tooltip" data-placement="top" title="{{ trans('labels.cpm') }}">
                                {{ trans('labels.cpm_short') }}
                            </th>
                            <th>{{ trans('labels.target') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    <!-- Table body will be populated by jQuery -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
