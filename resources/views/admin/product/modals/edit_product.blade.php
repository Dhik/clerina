<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <form id="editProductForm" action="#" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_product_name">Product Name</label>
                            <input type="text" id="edit_product_name" name="product" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_stock">Stock</label>
                            <input type="number" id="edit_stock" name="stock" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_sku">SKU</label>
                            <input type="text" id="edit_sku" name="sku" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_harga_jual">Harga Jual</label>
                            <input type="text" id="edit_harga_jual" name="harga_jual" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_harga_markup">Harga Markup</label>
                            <input type="text" id="edit_harga_markup" name="harga_markup" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit_harga_cogs">Harga COGS</label>
                            <input type="text" id="edit_harga_cogs" name="harga_cogs" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="edit_harga_batas_bawah">Harga Batas Bawah</label>
                            <input type="text" id="edit_harga_batas_bawah" name="harga_batas_bawah" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    
                </div>
            </div>
        </form>
    </div>
</div>
