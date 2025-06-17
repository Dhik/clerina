<div class="modal fade" id="editContentAdsModal" tabindex="-1" role="dialog" aria-labelledby="editContentAdsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editContentAdsModalLabel">Edit Content Ads</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editContentAdsForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Basic Information</h6>
                            <div class="form-group">
                                <label for="edit_link_ref">Link Reference</label>
                                <input type="text" class="form-control" name="link_ref" id="edit_link_ref">
                            </div>
                            <div class="form-group">
                                <label for="edit_product">Product</label>
                                <select class="form-control" name="product" id="edit_product">
                                    <option value="">Select Product</option>
                                    @foreach($productOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_platform">Platform</label>
                                <select class="form-control" name="platform" id="edit_platform">
                                    <option value="">Select Platform</option>
                                    @foreach($platformOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_funneling">Funneling</label>
                                <select class="form-control" name="funneling" id="edit_funneling">
                                    <option value="">Select Funneling</option>
                                    @foreach($funnelingOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Additional Details</h6>
                            <div class="form-group">
                                <label for="edit_request_date">Request Date</label>
                                <input type="date" class="form-control" name="request_date" id="edit_request_date">
                            </div>
                            <div class="form-group">
                                <label for="edit_link_drive">Link Drive</label>
                                <input type="text" class="form-control" name="link_drive" id="edit_link_drive">
                            </div>
                            <div class="form-group">
                                <label for="edit_editor">Editor</label>
                                <select class="form-control" name="editor" id="edit_editor">
                                    <option value="">Select Editor</option>
                                    @foreach($editorOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="edit_filename">File Name</label>
                                <input type="text" class="form-control" name="filename" id="edit_filename">
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="tugas_selesai" id="edit_tugas_selesai" value="1">
                                    <label class="custom-control-label" for="edit_tugas_selesai">Tugas Selesai</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_desc_request">Description Request</label>
                        <textarea class="form-control" name="desc_request" id="edit_desc_request" rows="6"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Content Ads</button>
                </div>
            </form>
        </div>
    </div>
</div>
