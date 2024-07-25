<div class="modal fade" id="showOmsetModal" tabindex="-1" role="dialog" aria-labelledby="showOmsetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showOmsetModalLabel">{{ trans('labels.turnover') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.market_place') }}</th>
                            <th>{{ trans('labels.turnover') }}</th>
                        </tr>
                    </thead>
                    <tbody id="omset-table-body">
                        <!-- Data rows will be inserted here using jQuery -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

