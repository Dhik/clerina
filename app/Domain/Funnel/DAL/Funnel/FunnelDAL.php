<?php

namespace App\Domain\Funnel\DAL\Funnel;

use App\Domain\Funnel\Models\Funnel;
use App\Domain\Funnel\Models\FunnelRecap;
use App\Domain\Funnel\Models\FunnelTotal;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Funnel model
 */
class FunnelDAL extends BaseDAL implements FunnelDALInterface
{
    public function __construct(
        protected Funnel $funnel,
        protected FunnelRecap $funnelRecap,
        protected FunnelTotal $funnelTotal
    ) {
    }

    /**
     * Return funnel for DataTable
     */
    public function getFunnelDataTable(): Builder
    {
        return $this->funnel->query();
    }

    /**
     * Return funnel recap for DataTable
     */
    public function getFunnelRecapDataTable(): Builder
    {
        return $this->funnelRecap->query();
    }

    /**
     * Return funnel total for DataTable
     */
    public function getFunnelTotalDataTable(): Builder
    {
        return $this->funnelTotal->query();
    }

    /**
     * Get funnel by date
     */
    public function getFunnelByDate(string $date, ?string $type = null): Collection
    {
        return $this->funnel
            ->where('date', Carbon::parse($date))
            ->when(! is_null($type), function ($q) use ($type) {
                return $q->where('type', $type);
            })
            ->get();
    }

    /**
     * Get funnel recap by date
     */
    public function getFunnelRecapByDate(string $date): Collection
    {
        return $this->funnelRecap->where('date', Carbon::parse($date))->get();
    }

    /**
     * Create funnel
     */
    public function createFunnel(array $funnelToCreate): Funnel
    {
        return $this->funnel->updateOrCreate([
            'date' => Carbon::parse($funnelToCreate['date'])->format('Y-m-d'),
            'type' => $funnelToCreate['type'],
            'social_media_id' => $funnelToCreate['social_media_id'],
        ], [
            ...$funnelToCreate,
        ]);
    }

    /**
     * Create funnel recap
     */
    public function createFunnelRecap(array $funnelRecapToCreate): FunnelRecap
    {
        return $this->funnelRecap->updateOrCreate([
            'date' => Carbon::parse($funnelRecapToCreate['date'])->format('Y-m-d'),
            'type' => $funnelRecapToCreate['type'],
        ], [
            ...$funnelRecapToCreate,
        ]);
    }

    /**
     * Create funnel total
     */
    public function createFunnelTotal(array $funnelTotalToCreate): FunnelTotal
    {
        return $this->funnelTotal->updateOrCreate([
            'date' => Carbon::parse($funnelTotalToCreate['date'])->format('Y-m-d'),
        ], [
            ...$funnelTotalToCreate,
        ]);
    }
}
