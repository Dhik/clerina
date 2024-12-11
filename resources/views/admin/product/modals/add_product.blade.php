<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addProductForm" method="POST" action="{{ route('product.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="product">Product Name</label>
                        <input type="text" class="form-control" id="product" name="product" required>
                        <div id="product-error" class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock</label>
                        <input type="number" class="form-control" id="stock" name="stock" required>
                        <div id="stock-error" class="invalid-feedback"></div>
                    </div>
                    <div class="form-group">
                        <label for="sku">SKU</label>
                        <input type="text" class="form-control" id="sku" name="sku" required>
                    </div>
                    <div class="form-group">
                        <label for="harga_jual">Harga Jual</label>
                        <input type="number" step="0.01" class="form-control" id="harga_jual" name="harga_jual" required>
                    </div>
                    <div class="form-group">
                        <label for="harga_markup">Harga Markup</label>
                        <input type="number" step="0.01" class="form-control" id="harga_markup" name="harga_markup">
                    </div>
                    <div class="form-group">
                        <label for="harga_cogs">Harga COGS</label>
                        <input type="number" step="0.01" class="form-control" id="harga_cogs" name="harga_cogs">
                    </div>
                    <div class="form-group">
                        <label for="harga_batas_bawah">Harga Batas Bawah</label>
                        <input type="number" step="0.01" class="form-control" id="harga_batas_bawah" name="harga_batas_bawah">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
