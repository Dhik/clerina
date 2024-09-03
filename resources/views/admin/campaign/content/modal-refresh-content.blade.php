<div class="modal fade" id="refreshAllModal" tabindex="-1" aria-labelledby="refreshAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refreshAllModalLabel">Content to be Refreshed</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nama Influencer</th>
                            <th>Nama Task</th>
                            <th>Social Media</th>
                            <th>Produk</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="refreshAllContentList">
                        <!-- Content will be loaded here dynamically -->
                    </tbody>
                </table>
                <div class="progress mt-3">
                    <div id="refreshProgressBar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmRefreshAll">Refresh All</button>
            </div>
        </div>
    </div>
</div>