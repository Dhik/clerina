<?php

namespace App\Domain\Sales\BLL\SalesChannel;

use App\Domain\Marketing\DAL\SocialMedia\SocialMediaDALInterface;
use App\Domain\Order\DAL\Order\OrderDAL;
use App\Domain\Sales\DAL\AdSpentMarketPlace\AdSpentMarketPlaceDAL;
use App\Domain\Sales\DAL\SalesChannel\SalesChannelDALInterface;
use App\Domain\Sales\DAL\Visit\VisitDAL;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Sales\Requests\SalesChannelRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property SocialMediaDALInterface DAL
 */
class SalesChannelBLL extends BaseBLL implements SalesChannelBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(
        SalesChannelDALInterface $salesChannelDAL,
        protected AdSpentMarketPlaceDAL $adSpentMarketPlaceDAL,
        protected OrderDAL $orderDAL,
        protected VisitDAL $visitDAL
    ) {
        $this->dal = $salesChannelDAL;
    }

    /**
     * Find sales channel by name
     */
    public function findByName(string $name): SalesChannel
    {
        return $this->dal->findByName($name);
    }

    /**
     * Return sales channel for DataTable
     */
    public function getSalesChannelDataTable(): Builder
    {
        return $this->dal->getSalesChannelDataTable();
    }

    /**
     * Create new sales channel
     */
    public function storeSalesChannel(SalesChannelRequest $request): SalesChannel
    {
        return $this->dal->storeSalesChannel($request);
    }

    /**
     * Update sales channel
     */
    public function updateSalesChannel(SalesChannel $salesChannel, SalesChannelRequest $request): SalesChannel
    {
        return $this->dal->updateSalesChannel($salesChannel, $request);
    }

    /**
     * Delete sales channel
     */
    public function deleteSalesChannel(SalesChannel $salesChannel): bool
    {
        $checkOrder = $this->orderDAL->checkOrderBySalesChannel($salesChannel->id);

        if (! empty($checkOrder)) {
            return false;
        }

        $checkAdSpent = $this->adSpentMarketPlaceDAL->checkAdSpentBySalesChannel($salesChannel->id);

        if (! empty($checkAdSpent)) {
            return false;
        }

        $checkVisit = $this->visitDAL->checkVisitBySalesChannel($salesChannel->id);

        if (! empty($checkVisit)) {
            return false;
        }

        $this->dal->deleteSalesChannel($salesChannel);

        return true;
    }

    /**
     * Return sales channel
     */
    public function getSalesChannel(): mixed
    {
        return $this->dal->getSalesChannels();
    }
}
