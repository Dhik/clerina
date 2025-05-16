<div class="modal fade" id="importShopeeAdsModal" tabindex="-1" role="dialog" aria-labelledby="importShopeeAdsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importShopeeAdsModalLabel">Import Shopee Ads</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importShopeeAdsForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="shopeeAdsCsvFile">Import CSV File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="shopeeAdsCsvFile" name="csv_file" accept=".csv,.txt" required>
                            <label class="custom-file-label" for="shopeeAdsCsvFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload a CSV file with Shopee ads data. The date and product code will be automatically extracted from the file.</small>
                    </div>
                    <div class="form-group d-none" id="errorImportShopeeAds"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import SKU Details Modal -->
<div class="modal fade" id="importShopeeSkuModal" tabindex="-1" role="dialog" aria-labelledby="importShopeeSkuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importShopeeSkuModalLabel">Import Shopee SKU Performance Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importShopeeSkuForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="shopeeSkuExcelFile">Import Excel File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="shopeeSkuExcelFile" name="excel_file" accept=".xlsx,.xls" required>
                            <label class="custom-file-label" for="shopeeSkuExcelFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload an Excel file with Shopee SKU performance data. The date will be automatically extracted from the filename (format: export_report.parentskudetail.YYYYMMDD_YYYYMMDD.xlsx).</small>
                    </div>
                    <div class="form-group d-none" id="errorImportShopeeSku"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>