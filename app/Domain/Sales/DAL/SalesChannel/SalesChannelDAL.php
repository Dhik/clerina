<?php

namespace App\Domain\Sales\DAL\SalesChannel;

use App\Domain\Sales\Enums\SalesChannelEnum;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Sales\Requests\SalesChannelRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * @property SalesChannel model
 */
class SalesChannelDAL extends BaseDAL implements SalesChannelDALInterface
{
    public function __construct(protected SalesChannel $salesChannel)
    {
    }

    /**
     * Find sales channel by name
     */
    public function findByName(string $name): SalesChannel
    {
        return $this->salesChannel->where('name', $name)->first();
    }

    /**
     * Return sales channel for DataTable
     */
    public function getSalesChannelDataTable(): Builder
    {
        return $this->salesChannel->query();
    }

    /**
     * Create new sales channel
     */
    public function storeSalesChannel(SalesChannelRequest $request): SalesChannel
    {
        $this->forgetCache();

        return $this->salesChannel->create($request->only('name'));
    }

    /**
     * Update sales channel
     */
    public function updateSalesChannel(SalesChannel $salesChannel, SalesChannelRequest $request): SalesChannel
    {
        $salesChannel->name = $request->name;
        $salesChannel->update();

        $this->forgetCache();

        return $salesChannel;
    }

    /**
     * Delete sales channel
     */
    public function deleteSalesChannel(SalesChannel $salesChannel): void
    {
        $salesChannel->delete();
        $this->forgetCache();
    }

    /**
     * Return all sales channel
     */
    public function getSalesChannels(): mixed
    {
        return Cache::rememberForever(SalesChannelEnum::AllSalesChannelCacheTag, function () {
            return SalesChannel::orderBy('name')->get();
        });
    }

    /**
     * Forget cache
     */
    protected function forgetCache(): void
    {
        Cache::forget(SalesChannelEnum::AllSalesChannelCacheTag);
    }
}
