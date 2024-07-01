<div class="modal fade" id="showVisitorModal" tabindex="-1" role="dialog" aria-labelledby="showVisitorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showVisitorModalLabel">{{ trans('labels.visit') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.sales_channel') }}</th>
                            <th>{{ trans('labels.visit') }}</th>
                        </tr>
                    </thead>
                    <tbody id="visit-table-body">
                    <!-- Data rows will be inserted here using jQuery -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

