<?php

namespace App\Domain\Campaign\BLL\Offer;

use App\Domain\Campaign\Models\Offer;
use App\Domain\Campaign\Requests\ChatProofRequest;
use App\Domain\Campaign\Requests\FinanceOfferRequest;
use App\Domain\Campaign\Requests\OfferRequest;
use App\Domain\Campaign\Requests\OfferStatusRequest;
use App\Domain\Campaign\Requests\OfferUpdateRequest;
use App\Domain\Campaign\Requests\ReviewOfferRequest;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface OfferBLLInterface
{
    /**
     * Get offer datatable
     */
    public function getOfferDataTable(Request $request): Builder;

    /**
     * Get offer by campaign id
     */
    public function getOfferByCampaignId(int $campaignId, Request $request): Builder;

    /**
     * Store new Offer
     */
    public function storeOffer(int $campaignId, OfferRequest $request): Offer;

    /**
     * Update Offer
     */
    public function updateOffer(Offer $offer, OfferUpdateRequest $request): Offer;

    /**
     * Update Status Offer
     */
    public function updateStatusOffer(Offer $offer, OfferStatusRequest $request): Offer;

    /**
     * Review offering
     */
    public function reviewOffering(Offer $offer, ReviewOfferRequest $request): Offer;

    /**
     * Store sign
     */
    public function storeSign(Offer $offer, Request $request): void;

    /**
     * Upload chat proof
     */
    public function storeChatProof(Offer $offer, ChatProofRequest $request): void;

    /**
     * Update finance offer
     */
    public function updateFinanceOffer(Offer $offer, FinanceOfferRequest $request): Offer;

    /**
     * Upload chat proof
     */
    public function storeTransferProof(Offer $offer, ChatProofRequest $request): void;
}
