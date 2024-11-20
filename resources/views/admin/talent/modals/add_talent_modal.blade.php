<div class="modal fade" id="addTalentModal" tabindex="-1" aria-labelledby="addTalentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addTalentForm" method="POST" action="{{ route('talent.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addTalentModalLabel">Add Talent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div id="username-error" class="invalid-feedback"></div>
                            </div>
                            <div class="form-group">
                                <label for="talent_name">Talent Name</label>
                                <input type="text" name="talent_name" id="talent_name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="content_type">Content Type</label>
                                <input type="text" name="content_type" id="content_type" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="produk">Produk</label>
                                <input type="text" name="produk" id="produk" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="pic">PIC</label>
                                <input type="text" name="pic" id="pic" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="bulan_running">Bulan Running</label>
                                <select name="bulan_running" id="bulan_running" class="form-control">
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
                                <label for="niche">Niche</label>
                                <input type="text" name="niche" id="niche" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="followers">Followers</label>
                                <input type="text" name="followers" id="followers" class="form-control money">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" name="address" id="address" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="phone_number">Phone Number</label>
                                <input type="text" name="phone_number" id="phone_number" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank">Bank</label>
                                <input type="text" name="bank" id="bank" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="no_rekening">No Rekening</label>
                                <input type="text" name="no_rekening" id="no_rekening" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nama_rekening">Nama Rekening</label>
                                <input type="text" name="nama_rekening" id="nama_rekening" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="no_npwp">No NPWP</label>
                                <input type="text" name="no_npwp" id="no_npwp" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="pengajuan_transfer_date">Pengajuan Transfer Date</label>
                                <input type="date" name="pengajuan_transfer_date" id="pengajuan_transfer_date" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="nik">NIK</label>
                                <input type="text" name="nik" id="nik" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="price_rate">Price Rate</label>
                                <input type="text" name="price_rate" id="price_rate" class="form-control money" required>
                            </div>
                            <div class="form-group">
                                <label for="slot_final">Slot Final</label>
                                <input type="number" name="slot_final" id="slot_final" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="rate_final">Rate Final</label>
                                <input type="text" name="rate_final" id="rate_final" class="form-control money" required>
                            </div>
                            <div class="form-group">
                                <label for="scope_of_work">Scope of Work</label>
                                <input type="text" name="scope_of_work" id="scope_of_work" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="masa_kerjasama">Masa Kerjasama</label>
                                <input type="text" name="masa_kerjasama" id="masa_kerjasama" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control" required>
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
