<div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewCustomerModalLabel">Customer Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Display Customer Name and Phone Number -->
                <div class="row">
                    <div class="col-8">
                        <div class="row">
                            <!-- Customer Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_customer_name">Customer Name</label>
                                    <input type="text" id="view_customer_name" class="form-control" readonly>
                                </div>
                            </div>
                            
                            <!-- Phone Number -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_phone_number">Phone Number</label>
                                    <input type="text" id="view_phone_number" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- Address as Textarea -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="view_alamat">Address</label>
                                    <textarea id="view_alamat" class="form-control" rows="2" readonly></textarea>
                                </div>
                            </div>

                            <!-- Kota/Kabupaten -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_kota_kabupaten">Kota/Kabupaten</label>
                                    <input type="text" id="view_kota_kabupaten" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- Provinsi -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_provinsi">Provinsi</label>
                                    <input type="text" id="view_provinsi" class="form-control" readonly>
                                </div>
                            </div>

                            <!-- Total Quantity -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="view_quantity">Total Quantity</label>
                                    <input type="text" id="view_quantity" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card">
                            <div class="card-body">
                                <h5>Distribusi per Produk</h5>
                                <div style="height: 350px;">
                                <canvas id="productPieChartDetail"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- Section for Orders -->
                <h5>Orders</h5>
                <table id="ordersTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Order Date</th>
                            <th width="10%">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Orders will be dynamically appended here -->
                    </tbody>
                </table>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
