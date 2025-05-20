<div class="modal fade" id="importTiktokAdsSpentModal" tabindex="-1" role="dialog" aria-labelledby="importTiktokAdsSpentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importTiktokAdsSpentModalLabel">Import TikTok Ads Spent</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importTiktokAdsSpentForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="tiktokAdsFile">Import XLSX or ZIP File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="tiktokAdsFile" name="tiktokAdsFile" accept=".xlsx,.zip" required>
                            <label class="custom-file-label" for="tiktokAdsFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">Please upload an XLSX file or a ZIP file containing multiple XLSX files with TikTok ads data</small>
                    </div>
                    <div class="form-group">
                        <label for="kategoriProduk">Kategori Produk</label>
                        <select class="form-control" id="tiktokKategoriProduk" name="kategori_produk" required>
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
                    <div class="form-group">
                        <label for="pic">PIC</label>
                        <select class="form-control" id="tiktokPIC" name="pic" required>
                            <option value="" selected disabled>Choose PIC</option>
                            <option value="NABILA">Nabila</option>
                            <option value="REZA">Reza</option>
                            <option value="FEBRY">Febry</option>
                            <option value="LAIN-LAIN">Lain-lain</option>
                        </select>
                    </div>
                    <div class="form-group d-none" id="errorImportTiktokAdsSpent"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="importTiktokGmvMaxModal" tabindex="-1" role="dialog" aria-labelledby="importTiktokGmvMaxModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importTiktokGmvMaxModalLabel">Import TikTok GMV Max Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="importTiktokGmvMaxForm" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="tiktokGmvMaxFile">Import Excel or ZIP File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="tiktokGmvMaxFile" name="tiktokGmvMaxFile" accept=".xlsx,.csv,.zip" required>
                            <label class="custom-file-label" for="tiktokGmvMaxFile">Choose file</label>
                        </div>
                        <small class="form-text text-muted">
                            Please upload an Excel file with GMV Max data or a ZIP file containing multiple Excel files. 
                            The date will be extracted from each filename (e.g., "Product campaign data 2025-05-12 - 2025-05-12.xlsx" will use 2025-05-12)
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="kategoriProduk">Kategori Produk</label>
                        <select class="form-control" id="tiktokGmvMaxKategoriProduk" name="kategori_produk" required>
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
                    <div class="form-group">
                        <label for="pic">PIC</label>
                        <select class="form-control" id="tiktokGmvMaxPIC" name="pic" required>
                            <option value="" selected disabled>Choose PIC</option>
                            <option value="NABILA">Nabila</option>
                            <option value="REZA">Reza</option>
                            <option value="FEBRY">Febry</option>
                            <option value="LAIN-LAIN">Lain-lain</option>
                        </select>
                    </div>
                    <div class="form-group d-none" id="errorImportTiktokGmvMax"></div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Import GMV Max Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>