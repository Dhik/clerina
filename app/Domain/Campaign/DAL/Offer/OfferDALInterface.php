<?php

namespace App\Domain\Campaign\DAL\Offer;

use App\Domain\Campaign\Models\Offer;
use Illuminate\Database\Eloquent\Builder;

interface OfferDALInterface
{
    /**
     * Get offer datatable
     */
    public function getOfferDataTable(): Builder;

    /**
     * Get offer by campaign id
     */
    public function getOfferByCampaignId(int $campaignId): Builder;

    /**
     * Create a new offer
     */
    public function storeOffer(array $data): Offer;

    /**
     * Update offer
     */
    public function updateOffer(Offer $offer, array $data): Offer;

    /**
     * Delete offer
     */
    public function deleteOffer(Offer $offer): void;
}
