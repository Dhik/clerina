<div class="modal fade" id="importMetaAdsSpentModal" tabindex="-1" role="dialog" aria-labelledby="importMetaAdsSpentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importMetaAdsSpentModalLabel">Import Meta Ads Spent</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importMetaAdsSpentForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="metaAdsCsvFile">Import CSV File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="metaAdsCsvFile" name="meta_ads_csv_file" accept=".csv" required>
                            <label class="custom-file-label" for="metaAdsCsvFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload a CSV file containing your Meta ads spent data</small>
                    </div>
                    <div class="form-group d-none" id="errorImportMetaAdsSpent"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>