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
                        <small class="form-text text-muted">Please upload a CSV file with Shopee ads data</small>
                    </div>
                    <div class="form-group">
                        <label for="kodeProduk">Kode Produk</label>
                        <select class="form-control" id="kodeProduk" name="kode_produk" required>
                            <option value="" selected disabled>Choose product code</option>
                            <option value="Jelly Booster">Jelly Booster</option>
                            <option value="Glowsmooth">Glowsmooth</option>
                            <option value="Red Saviour">Red Saviour</option>
                            <option value="3 Minutes">3 Minutes</option>
                            <option value="Calendula">Calendula</option>
                            <option value="Natural Exfo">Natural Exfo</option>
                            <option value="Pore Glow">Pore Glow</option>
                            <option value="8X Hyalu">8X Hyalu</option>
                            <option value="Lain-lain">Lain-lain</option>
                        </select>
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