<div class="modal fade" id="addLinkContentModal" tabindex="-1" aria-labelledby="addLinkContentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addLinkContentForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addLinkContentModalLabel">Add Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="task_name">Task Name</label>
                        <select name="task_name" id="task_name" class="form-control" required>
                            <option value="">Select Task Name</option>
                            <option value="Soft Selling">Soft Selling</option>
                            <option value="Hard Selling">Hard Selling</option>
                            <option value="Awareness">Awareness</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="channel">Channel</label>
                        <select name="channel" id="channel" class="form-control" required>
                            <option value="">Select Channel</option>
                            <option value="instagram_feed">Instagram Feed</option>
                            <option value="tiktok_video">Tiktok Video</option>
                            <option value="twitter_post">Twitter Post</option>
                            <option value="shopee_video">Shopee Video</option>
                            <option value="instagram_story">Instagram Story</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="upload_link">Upload Link</label>
                        <input type="url" name="upload_link" id="upload_link" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="posting_date">Posting Date</label>
                        <input type="date" name="posting_date" id="posting_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="kode_ads">Kode Ads</label>
                        <input type="text" name="kode_ads" id="kode_ads" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
