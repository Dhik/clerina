<?php

namespace App\Domain\Campaign\DAL\Offer;

use App\Domain\Campaign\Models\Offer;
use Illuminate\Database\Eloquent\Builder;

class OfferDAL implements OfferDALInterface
{
    public function __construct(protected Offer $offer)
    {
    }

    /**
     * Get offer datatable
     */
    public function getOfferDataTable(): Builder
    {
        return $this->offer->query()
            ->with(['campaign', 'createdBy', 'keyOpinionLeader']);
    }

    /**
     * Get offer by campaign id
     */
    public function getOfferByCampaignId(int $campaignId): Builder
    {
        return $this->offer->query()
            ->with(['createdBy', 'keyOpinionLeader'])
            ->where('campaign_id', $campaignId);
    }

    /**
     * Create a new offer
     */
    public function storeOffer(array $data): Offer
    {
        return $this->offer->create($data);
    }

    /**
     * Update offer
     */
    public function updateOffer(Offer $offer, array $data): Offer
    {
        $offer->update($data);
        return $offer;
    }

    /**
     * Delete offer
     */
    public function deleteOffer(Offer $offer): void
    {
        $offer->delete();
    }
}
