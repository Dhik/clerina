<div class="modal fade" id="showAdSpentModal" tabindex="-1" role="dialog" aria-labelledby="showAdSpentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showAdSpentModalLabel">{{ trans('labels.ad_spent') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.social_media') }}</th>
                            <th>{{ trans('labels.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody id="adspent-social-media-table-body">
                        <!-- Data rows will be inserted here using jQuery -->
                    </tbody>
                </table>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.channel') }}</th>
                            <th>{{ trans('labels.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody id="adspent-market-place-table-body">
                        <!-- Data rows will be inserted here using jQuery -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

