<?php

namespace App\Domain\Funnel\Controllers;

use App\Domain\Funnel\BLL\Funnel\FunnelBLLInterface;
use App\Domain\Funnel\Models\Funnel;
use App\Domain\Funnel\Models\FunnelTotal;
use App\Domain\Funnel\Requests\CreateFunnelBofuRequest;
use App\Domain\Funnel\Requests\CreateFunnelMofuRequest;
use App\Domain\Funnel\Requests\CreateFunnelTofuRequest;
use App\Domain\Funnel\Requests\StoreScreenShotRequest;
use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View as ViewAlias;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class FunnelController extends Controller
{
    public function __construct(
        protected FunnelBLLInterface $funnelBLL,
        protected SocialMediaBLLInterface $socialMediaBLL
    ) {

    }

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewFunnel', Funnel::class);

        $funnelQuery = $this->funnelBLL->getFunnelDataTable($request);

        return $this->getToJson($funnelQuery)
            ->addColumn('actions', function ($row) {
                return '<button class="btn btn-success btn-xs updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }


    /**
     * @throws Exception
     */
    public function getRecap(Request $request): JsonResponse
    {
        $this->authorize('viewFunnel', Funnel::class);

        $funnelQuery = $this->funnelBLL->getFunnelRecapDataTable($request);

        return $this->getToJson($funnelQuery)->toJson();
    }

    /**
     * @throws Exception
     */
    public function getTotal(Request $request): JsonResponse
    {
        $this->authorize('viewFunnel', Funnel::class);

        $funnelQuery = $this->funnelBLL->getFunnelTotalDataTable($request);

        return DataTables::of($funnelQuery)
            ->addColumn('reachFormatted', function ($row) {
                return number_format($row->total_reach, 0, ',', '.');
            })
            ->addColumn('impressionFormatted', function ($row) {
                return number_format($row->total_impression, 0, ',', '.');
            })
            ->addColumn('engagementFormatted', function ($row) {
                return number_format($row->total_engagement, 0, ',', '.');
            })
            ->addColumn('cpmFormatted', function ($row) {
                return number_format($row->total_cpm, 0, ',', '.');
            })
            ->addColumn('roasFormatted', function ($row) {
                return number_format($row->total_roas, 2, ',', '.');
            })
            ->addColumn('spendFormatted', function ($row) {
                return number_format($row->total_spend, 0, ',', '.');
            })
            ->addColumn('screenshot_url', function ($row) {
                if (! empty($row->getFirstMedia('screenshot'))) {
                    return '<a href="'.route('funnel.get-screenshot', $row->id).'" target="_blank" class="btn btn-success btn-xs">
                    <i class="fas fa-eye"></i>
                </a>';
                }
            })
            ->addColumn(
                'actions',
                '<button class="btn btn-primary btn-xs uploadScreenshot">
                            <i class="fas fa-upload"></i>
                        </button>'
            )
            ->rawColumns(['actions', 'screenshot_url'])
            ->toJson();
    }

    /**
     * View list input funnel
     */
    public function input(): ViewAlias|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewFunnel', Funnel::class);

        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.funnel.input.index', compact('socialMedia'));
    }

    /**
     * View list recap funnel
     */
    public function recap(): ViewAlias|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewFunnel', Funnel::class);

        return view('admin.funnel.recap.index');
    }

    /**
     * View list total funnel
     */
    public function total(): ViewAlias|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewFunnel', Funnel::class);

        return view('admin.funnel.total.index');
    }

    /**
     * Create new tofu
     */
    public function storeTofu(CreateFunnelTofuRequest $request): JsonResponse
    {
        $this->authorize('createFunnel', Funnel::class);

        $this->funnelBLL->createTOFU($request);

        return response()->json($request->all());
    }

    /**
     * Create new Mofu
     */
    public function storeMofu(CreateFunnelMofuRequest $request): JsonResponse
    {
        $this->authorize('createFunnel', Funnel::class);

        $this->funnelBLL->createMOFU($request);

        return response()->json($request->all());
    }

    /**
     * Create new Bofu
     */
    public function storeBofu(CreateFunnelBofuRequest $request): JsonResponse
    {
        $this->authorize('createFunnel', Funnel::class);

        $this->funnelBLL->createBOFU($request);

        return response()->json($request->all());
    }

    /**
     * Store screenshot
     */
    public function storeScreenshot(FunnelTotal $funnelTotal, StoreScreenShotRequest $request): JsonResponse
    {
        $this->authorize('createFunnel', Funnel::class);

        $this->funnelBLL->storeScreenshot($funnelTotal, $request);

        return response()->json($request->all());
    }

    /**
     * Provide link private file
     */
    public function getScreenshot(FunnelTotal $funnelTotal): BinaryFileResponse
    {
        $media = $funnelTotal->getFirstMedia('screenshot');

        if (is_null($media)) {
            abort(404);
        }

        $pathToFile = Storage::disk('private')->path($media->id.'/'.$media->file_name);

        return response()->file($pathToFile);
    }

    /**
     * @throws Exception
     */
    protected function getToJson(Builder $funnelQuery): DataTableAbstract
    {
        return DataTables::of($funnelQuery)
            ->addColumn('spendFormatted', function ($row) {
                return number_format($row->spend, 0, ',', '.');
            })
            ->addColumn('reachFormatted', function ($row) {
                return number_format($row->reach, 0, ',', '.');
            })
            ->addColumn('cprFormatted', function ($row) {
                return number_format($row->cpr, 0, ',', '.');
            })
            ->addColumn('impressionFormatted', function ($row) {
                return number_format($row->impression, 0, ',', '.');
            })
            ->addColumn('cpmFormatted', function ($row) {
                return number_format($row->cpm, 0, ',', '.');
            })
            ->addColumn('frequencyFormatted', function ($row) {
                return number_format($row->frequency, 2, ',', '.');
            })
            ->addColumn('cpvFormatted', function ($row) {
                return number_format($row->cpv, 0, ',', '.');
            })
            ->addColumn('playVideoFormatted', function ($row) {
                return number_format($row->play_video, 0, ',', '.');
            })
            ->addColumn('linkClickFormatted', function ($row) {
                return number_format($row->link_click, 0, ',', '.');
            })
            ->addColumn('cpcFormatted', function ($row) {
                return number_format($row->cpc, 0, ',', '.');
            })
            ->addColumn('engagementFormatted', function ($row) {
                return number_format($row->engagement, 0, ',', '.');
            })
            ->addColumn('cpeFormatted', function ($row) {
                return number_format($row->cpe, 0, ',', '.');
            })
            ->addColumn('cpmFormatted', function ($row) {
                return number_format($row->cpm, 0, ',', '.');
            })
            ->addColumn('ctrFormatted', function ($row) {
                return number_format($row->ctr, 2, ',', '.');
            })
            ->addColumn('cplvFormatted', function ($row) {
                return number_format($row->cplv, 0, ',', '.');
            })
            ->addColumn('cpaFormatted', function ($row) {
                return number_format($row->cpa, 0, ',', '.');
            })
            ->addColumn('atcFormatted', function ($row) {
                return number_format($row->atc, 0, ',', '.');
            })
            ->addColumn('initiatedCheckoutNumberFormatted', function ($row) {
                return number_format($row->initiated_checkout_number, 0, ',', '.');
            })
            ->addColumn('purchaseNumberFormatted', function ($row) {
                return number_format($row->purchase_number, 0, ',', '.');
            })
            ->addColumn('costPerIcFormatted', function ($row) {
                return number_format($row->cost_per_ic, 0, ',', '.');
            })
            ->addColumn('costPerAtcFormatted', function ($row) {
                return number_format($row->cost_per_atc, 0, ',', '.');
            })
            ->addColumn('costPerPurchaseFormatted', function ($row) {
                return number_format($row->cost_per_purchase, 0, ',', '.');
            })
            ->addColumn('roasFormatted', function ($row) {
                return number_format($row->roas, 2, ',', '.');
            });
    }
}
