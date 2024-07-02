<?php

namespace App\Domain\Sales\BLL\AdSpentMarketPlace;

use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Requests\AdSpentMarketPlaceRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface AdSpentMarketPlaceBLLInterface extends BaseBLLInterface
{
    /**
     * Return AdSpentMarketPlace data for DataTable
     */
    public function getAdSpentMarketPlaceDataTable(Request $request, int $tenantId): Builder;

    /**
     * Retrieves AdSpent marketplace recap information based on the provided request.
     */
    public function getAdSpentMarketPlaceRecap(Request $request, int $tenantId): array;

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentMarketPlaceByDate(string $date, int $tenantId): Collection;

    /**
     * Create new AdSpentMarketPlace data
     */
    public function createAdSpentMarketPlace(AdSpentMarketPlaceRequest $request, int $tenantId): AdSpentMarketPlace;

    /**
     * Update AdSpentMarketPlace data
     */
    public function updateAdSpentMarketPlace(
        AdSpentMarketPlace $adSpentMarketPlace,
        AdSpentMarketPlaceRequest $request
    ): AdSpentMarketPlace;

    /**
     * Delete AdSpentMarketPlace data
     */
    public function deleteAdSpentMarketPlace(AdSpentMarketPlace $adSpentMarketPlace): void;
}
