<div class="modal fade" id="addTalentModal" tabindex="-1" aria-labelledby="addTalentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
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
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="talent_name">Talent Name</label>
                        <input type="text" name="talent_name" id="talent_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="video_slot">Video Slot</label>
                        <input type="number" name="video_slot" id="video_slot" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="content_type">Content Type</label>
                        <input type="text" name="content_type" id="content_type" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="produk">Produk</label>
                        <input type="text" name="produk" id="produk" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="rate_final">Rate Final</label>
                        <input type="number" name="rate_final" id="rate_final" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="pic">PIC</label>
                        <input type="text" name="pic" id="pic" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="bulan_running">Bulan Running</label>
                        <input type="text" name="bulan_running" id="bulan_running" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="niche">Niche</label>
                        <input type="text" name="niche" id="niche" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="followers">Followers</label>
                        <input type="number" name="followers" id="followers" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" name="address" id="address" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" name="phone_number" id="phone_number" class="form-control">
                    </div>

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
                        <label for="gdrive_ttd_kol_accepting">GDrive TTD Kol Accepting</label>
                        <input type="text" name="gdrive_ttd_kol_accepting" id="gdrive_ttd_kol_accepting" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK</label>
                        <input type="text" name="nik" id="nik" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="price_rate">Price Rate</label>
                        <input type="number" name="price_rate" id="price_rate" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="first_rate_card">First Rate Card</label>
                        <input type="number" name="first_rate_card" id="first_rate_card" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="discount">Discount</label>
                        <input type="number" name="discount" id="discount" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="slot_final">Slot Final</label>
                        <input type="number" name="slot_final" id="slot_final" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="tax_deduction">Tax Deduction</label>
                        <input type="number" name="tax_deduction" id="tax_deduction" class="form-control">
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
