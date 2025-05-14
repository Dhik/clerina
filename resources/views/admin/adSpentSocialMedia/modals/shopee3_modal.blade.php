<div class="modal fade" id="importShopee3AdsSpentModal" tabindex="-1" role="dialog" aria-labelledby="importShopee3AdsSpentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importShopee3AdsSpentModalLabel">Import Shopee 3 Ads Spent</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importShopee3AdsSpentForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="shopee3AdsFile">Import CSV or ZIP File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="shopee3AdsFile" name="shopee3AdsFile" accept=".csv,.zip" required>
                            <label class="custom-file-label" for="shopee3AdsFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload a CSV file or a ZIP file containing multiple CSV files with Shopee 3 ads data</small>
                    </div>
                    <div class="form-group">
                        <label for="kategoriProduk">Kategori Produk</label>
                        <select class="form-control" id="shopee3KategoriProduk" name="kategori_produk" required>
                            <option value="" selected disabled>Choose product category</option>
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
                    <div class="form-group d-none" id="errorImportShopee3AdsSpent"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>