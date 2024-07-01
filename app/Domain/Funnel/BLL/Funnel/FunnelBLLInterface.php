<?php

namespace App\Domain\Funnel\BLL\Funnel;

use App\Domain\Funnel\Models\Funnel;
use App\Domain\Funnel\Models\FunnelTotal;
use App\Domain\Funnel\Requests\CreateFunnelBofuRequest;
use App\Domain\Funnel\Requests\CreateFunnelMofuRequest;
use App\Domain\Funnel\Requests\CreateFunnelTofuRequest;
use App\Domain\Funnel\Requests\StoreScreenShotRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface FunnelBLLInterface extends BaseBLLInterface
{
    /**
     * Return sales for DataTable
     */
    public function getFunnelDataTable(Request $request): Builder;

    /**
     * Return funnel recap for DataTable
     */
    public function getFunnelRecapDataTable(Request $request): Builder;

    /**
     * Return funnel total for DataTable
     */
    public function getFunnelTotalDataTable(Request $request): Builder;

    /**
     * Create TOFU
     *
     * @throws Exception
     */
    public function createTOFU(CreateFunnelTofuRequest $request): void;

    /**
     * Create MOFU
     *
     * @throws Exception
     */
    public function createMOFU(CreateFunnelMofuRequest $request): void;

    /**
     * Create BOFU
     *
     * @throws Exception
     */
    public function createBOFU(CreateFunnelBofuRequest $request): void;

    /**
     * Sync recap tofu
     *
     * @throws Exception
     */
    public function syncRecapTOFU(string $date): void;

    /**
     * Sync recap mofu
     *
     * @throws Exception
     */
    public function syncRecapMofu(string $date): void;

    /**
     * Sync recap bofu
     *
     * @throws Exception
     */
    public function syncRecapBofu(string $date): void;

    /**
     * Sync total recap
     */
    public function syncTotalFunnel(string $date): void;

    /**
     * Store screenshot
     */
    public function storeScreenshot(FunnelTotal $funnelTotal, StoreScreenShotRequest $request): void;
}
