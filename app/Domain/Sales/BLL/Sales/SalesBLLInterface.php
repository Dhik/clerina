<?php

namespace App\Domain\Sales\BLL\Sales;

use App\Domain\Sales\Models\Sales;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface SalesBLLInterface extends BaseBLLInterface
{
    /**
     * Return sales for DataTable
     */
    public function getSalesDataTable(Request $request, int $tenantId): Builder;

    /**
     * Retrieves sales recap information based on the provided request.
     */
    public function getSalesRecap(Request $request, int $tenantId): array;

    /**
     * Create sales
     */
    public function createSales($date, int $tenantId): Sales;
}
