<div class="modal fade" id="editTalentModal" tabindex="-1" aria-labelledby="editTalentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editTalentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editTalentModalLabel">Edit Talent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_username">Username</label>
                                <input type="text" name="username" id="edit_username" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_talent_name">Talent Name</label>
                                <input type="text" name="talent_name" id="edit_talent_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_video_slot">Video Slot</label>
                                <input type="number" name="video_slot" id="edit_video_slot" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_content_type">Content Type</label>
                                <input type="text" name="content_type" id="edit_content_type" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_produk">Produk</label>
                                <input type="text" name="produk" id="edit_produk" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_pic">PIC</label>
                                <input type="text" name="pic" id="edit_pic" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_bulan_running">Bulan Running</label>
                                <select name="bulan_running" id="edit_bulan_running" class="form-control">
                                    <option value="">Select Month</option>
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_niche">Niche</label>
                                <input type="text" name="niche" id="edit_niche" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_followers">Followers</label>
                                <input type="number" name="followers" id="edit_followers" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_address">Address</label>
                                <input type="text" name="address" id="edit_address" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_tax_percentage">Persentase Pajak (1-100)</label>
                                <input type="number" name="tax_percentage" id="edit_tax_percentage" class="form-control" step="0.01" min="0" max="100">
                            </div>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_phone_number">Phone Number</label>
                                <input type="text" name="phone_number" id="edit_phone_number" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_bank">Bank</label>
                                <input type="text" name="bank" id="edit_bank" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_no_rekening">No Rekening</label>
                                <input type="text" name="no_rekening" id="edit_no_rekening" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_nama_rekening">Nama Rekening</label>
                                <input type="text" name="nama_rekening" id="edit_nama_rekening" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_no_npwp">No NPWP</label>
                                <input type="text" name="no_npwp" id="edit_no_npwp" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_pengajuan_transfer_date">Pengajuan Transfer Date</label>
                                <input type="date" name="pengajuan_transfer_date" id="edit_pengajuan_transfer_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_nik">NIK</label>
                                <input type="text" name="nik" id="edit_nik" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_price_rate">Price Rate</label>
                                <input type="text" name="price_rate" id="edit_price_rate" class="form-control money" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_slot_final">Slot Final</label>
                                <input type="number" name="slot_final" id="edit_slot_final" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_rate_final">Rate Final</label>
                                <input type="text" name="rate_final" id="edit_rate_final" class="form-control money" required>
                            </div>
                            <div class="form-group">
                                <label for="edit_scope_of_work">Scope of Work</label>
                                <input type="text" name="scope_of_work" id="edit_scope_of_work" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_masa_kerjasama">Masa Kerjasama</label>
                                <input type="text" name="masa_kerjasama" id="edit_masa_kerjasama" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="edit_platform">Platform</label>
                                <select name="platform" id="edit_platform" class="form-control">
                                    <option value="">Select Platform</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Tiktok">Tiktok</option>
                                    <option value="Twitter">Twitter</option>
                                    <option value="Youtube">Youtube</option>
                                    <option value="Shopee">Shopee</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
