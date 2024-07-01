<?php

namespace App\Domain\Sales\BLL\Visit;

use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Requests\VisitStoreRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface VisitBLLInterface extends BaseBLLInterface
{
    /**
     * Return visit data for DataTable
     */
    public function getVisitDataTable(Request $request, int $tenantId): Builder;

    /**
     * Return visit by date
     */
    public function getVisitByDate(string $date, int $tenantId): Collection;

    /**
     * Create new visit data
     */
    public function createVisit(VisitStoreRequest $request, int $tenantId): Visit;

    /**
     * Update visit data
     */
    public function updateVisit(Visit $visit, VisitStoreRequest $request): Visit;

    /**
     * Delete visit data
     */
    public function deleteVisit(Visit $visit): void;

    /**
     * Retrieves sales recap information based on the provided request.
     */
    public function getVisitRecap(Request $request, int $tenantId): array;
}
