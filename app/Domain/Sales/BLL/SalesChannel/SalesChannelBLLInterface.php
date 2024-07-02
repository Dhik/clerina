<?php

namespace App\Domain\Sales\BLL\SalesChannel;

use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Sales\Requests\SalesChannelRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;

interface SalesChannelBLLInterface extends BaseBLLInterface
{
    /**
     * Find sales channel by name
     */
    public function findByName(string $name): SalesChannel;

    /**
     * Return sales channel for DataTable
     */
    public function getSalesChannelDataTable(): Builder;

    /**
     * Create new sales channel
     */
    public function storeSalesChannel(SalesChannelRequest $request): SalesChannel;

    /**
     * Update sales channel
     */
    public function updateSalesChannel(SalesChannel $salesChannel, SalesChannelRequest $request): SalesChannel;

    /**
     * Delete sales channel
     */
    public function deleteSalesChannel(SalesChannel $salesChannel): bool;

    /**
     * Return sales channel
     */
    public function getSalesChannel(): mixed;
}
