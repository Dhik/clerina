<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Sales\Requests\SalesChannelRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class SalesChannelController extends Controller
{
    public function __construct(public SalesChannelBLLInterface $salesChannelBLL) {}

    /**
     * @throws Exception
     */
    public function get(): JsonResponse
    {
        $this->authorize('viewAnySalesChannel', SalesChannel::class);

        $salesChannelQuery = $this->salesChannelBLL->getSalesChannelDataTable();

        return DataTables::of($salesChannelQuery)
            ->addColumn(
                'actions',
                '<button class="btn btn-primary btn-xs updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-danger btn-xs deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>'
            )
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Show index page sales channel
     */
    public function index(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewAnySalesChannel', SalesChannel::class);

        return view('admin.salesChannel.index');
    }

    /**
     * Create new sales channel
     */
    public function store(SalesChannelRequest $request): JsonResponse
    {
        $this->authorize('createSalesChannel', SalesChannel::class);

        $this->salesChannelBLL->storeSalesChannel($request);

        return response()->json($request->all());
    }

    /**
     * Update sales channel
     */
    public function update(SalesChannel $salesChannel, SalesChannelRequest $request): JsonResponse
    {
        $this->authorize('updateSalesChannel', SalesChannel::class);

        $updatedData = $this->salesChannelBLL->updateSalesChannel($salesChannel, $request);

        return response()->json($updatedData);
    }

    /**
     * Delete sales channel
     */
    public function delete(SalesChannel $salesChannel): JsonResponse
    {
        $this->authorize('deleteSalesChannel', SalesChannel::class);

        $result = $this->salesChannelBLL->deleteSalesChannel($salesChannel);

        if (! $result) {
            return response()->json(['message' => trans('messages.sales_channel_failed_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }
}
