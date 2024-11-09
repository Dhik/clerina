<!-- Modal -->
<div class="modal fade" id="detailModal" role="dialog" aria-labelledby="detailModal" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contentUpdateModalLabel">{{ trans('labels.content') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div id="contentEmbed">
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-12 col-sm-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">{{ trans('labels.like') }}</span>
                                        <span class="info-box-number text-center text-muted mb-0" id="likeModal">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">{{ trans('labels.comment') }}</span>
                                        <span class="info-box-number text-center text-muted mb-0" id="commentModal">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">{{ trans('labels.views') }}</span>
                                        <span class="info-box-number text-center text-muted mb-0" id="viewModal">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">{{ trans('labels.rate_card') }}</span>
                                        <span class="info-box-number text-center text-muted mb-0" id="rateCardModal">0</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12" id="postDateCard">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">{{ trans('labels.post_date') }}</span>
                                        <span class="info-box-number text-center text-muted mb-0" id="uploadDateModal">Belum Posting</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-12">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">Kode Ads</span>
                                        <span class="info-box-number text-center text-muted mb-0" id="kodeAdsModal">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <canvas id="statisticDetailChart" class="w-100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

