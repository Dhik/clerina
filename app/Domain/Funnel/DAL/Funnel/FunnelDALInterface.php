<?php

namespace App\Domain\Funnel\DAL\Funnel;

use App\Domain\Funnel\Models\Funnel;
use App\Domain\Funnel\Models\FunnelRecap;
use App\Domain\Funnel\Models\FunnelTotal;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface FunnelDALInterface extends BaseDALInterface
{
    /**
     * Return funnel for DataTable
     */
    public function getFunnelDataTable(): Builder;

    /**
     * Return funnel recap for DataTable
     */
    public function getFunnelRecapDataTable(): Builder;

    /**
     * Get funnel by date
     */
    public function getFunnelByDate(string $date, ?string $type = null): Collection;

    /**
     * Get funnel recap by date
     */
    public function getFunnelRecapByDate(string $date): Collection;

    /**
     * Return funnel total for DataTable
     */
    public function getFunnelTotalDataTable(): Builder;

    /**
     * Create funnel
     */
    public function createFunnel(array $funnelToCreate): Funnel;

    /**
     * Create funnel recap
     */
    public function createFunnelRecap(array $funnelRecapToCreate): FunnelRecap;

    /**
     * Create funnel total
     */
    public function createFunnelTotal(array $funnelTotalToCreate): FunnelTotal;
}
