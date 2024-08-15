<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\KOL\KeyOpinionLeaderBLLInterface;
use App\Domain\Campaign\Enums\KeyOpinionLeaderEnum;
use App\Domain\Campaign\Exports\KeyOpinionLeaderExport;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Requests\KeyOpinionLeaderRequest;
use App\Domain\Campaign\Requests\KolExcelRequest;
use App\Domain\User\BLL\User\UserBLLInterface;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use LaravelLang\Publisher\Services\Filesystem\Json;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class KeyOpinionLeaderController extends Controller
{
    public function __construct(
        protected KeyOpinionLeaderBLLInterface $kolBLL,
        protected UserBLLInterface $userBLL
    )
    {}

    /**
     * Get common data
     */
    protected function getCommonData(): array
    {
        $channels = KeyOpinionLeaderEnum::Channel;
        $niches = KeyOpinionLeaderEnum::Niche;
        $skinTypes = KeyOpinionLeaderEnum::SkinType;
        $skinConcerns = KeyOpinionLeaderEnum::SkinConcern;
        $contentTypes = KeyOpinionLeaderEnum::ContentType;
        $marketingUsers = $this->userBLL->getMarketingUsers();

        return compact('channels', 'niches', 'skinTypes', 'skinConcerns', 'contentTypes', 'marketingUsers');
    }

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewKOL', KeyOpinionLeader::class);

        $query = $this->kolBLL->getKOLDatatable($request);

        return DataTables::of($query)
            ->addColumn('pic_contact_name', function ($row) {
                return $row->picContact->name ?? 'empty';
            })
            ->addColumn('actions', function ($row) {
                return '<a href=' . route('kol.show', $row->id) . ' class="btn btn-success btn-xs">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href=' . route('kol.edit', $row->id) . ' class="btn btn-primary btn-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </a>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }


    /**
     * Select KOl by username
     */
    public function select(Request $request): JsonResponse
    {
        $this->authorize('viewKOL', KeyOpinionLeader::class);

        return response()->json($this->kolBLL->selectKOL($request->input('search')));
    }

    /**
     * Show list KOL
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewKOL', KeyOpinionLeader::class);

        return view('admin.kol.index', $this->getCommonData());
    }

    /**
     * Create a new KOL
     */
    public function create(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('createKOL', KeyOpinionLeader::class);

        return view('admin.kol.create', $this->getCommonData());
    }

    /**
     * Create with excel form
     */
    public function createExcelForm(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('createKOL', KeyOpinionLeader::class);

        return view('admin.kol.create-excel', $this->getCommonData());
    }

    /**
     * store KOL
     */
    public function store(KeyOpinionLeaderRequest $request): RedirectResponse
    {
        $this->authorize('createKOL', KeyOpinionLeader::class);

        $kol = $this->kolBLL->storeKOL($request);
        return redirect()
            ->route('kol.show', $kol->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_save', ['model' => trans('labels.key_opinion_leader')]),
            ]);
    }

    /**
     * store KOL via excel
     */
    protected function storeExcel(KolExcelRequest $request): JsonResponse
    {
        $this->authorize('createKOL', KeyOpinionLeader::class);

        $result = $this->kolBLL->storeExcel($request->input('data'));

        if (! $result) {
            return response()->json('failed', 500);
        }

        return response()->json('success');
    }

    /**
     * Edit a new KOL
     */
    public function edit(KeyOpinionLeader $keyOpinionLeader): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('updateKOL', KeyOpinionLeader::class);

        return view('admin.kol.edit', array_merge(['keyOpinionLeader' => $keyOpinionLeader], $this->getCommonData()));
    }

    /**
     * Update KOL
     */
    public function update(KeyOpinionLeader $keyOpinionLeader, KeyOpinionLeaderRequest $request): RedirectResponse
    {
        $this->authorize('updateKOL', KeyOpinionLeader::class);

        $kol = $this->kolBLL->updateKOL($keyOpinionLeader, $request);
        return redirect()
            ->route('kol.show', $kol->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_update', ['model' => trans('labels.key_opinion_leader')]),
            ]);
    }

    /**
     * show KOL
     */
    public function show(KeyOpinionLeader $keyOpinionLeader): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewKOL', KeyOpinionLeader::class);

        return view('admin.kol.show', compact('keyOpinionLeader'));
    }

    /**
     * show KOL Json
     */
    public function showJson(KeyOpinionLeader $keyOpinionLeader): JsonResponse
    {
        $this->authorize('viewKOL', KeyOpinionLeader::class);

        return response()->json($keyOpinionLeader);
    }

    /**
     * Export KOL
     */
    public function export(Request $request): Response|BinaryFileResponse
    {
        $this->authorize('viewKOL', KeyOpinionLeader::class);

        return (new KeyOpinionLeaderExport())
            ->forChannel($request->input('channel'))
            ->forNiche($request->input('niche'))
            ->forSkinType($request->input('skinType'))
            ->forSkinConcern($request->input('skinConcern'))
            ->forContentType($request->input('contentType'))
            ->forPic($request->input('pic'))
            ->download('kol.xlsx');
    }
}
