<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\KOL\KeyOpinionLeaderBLLInterface;
use App\Domain\Campaign\Enums\KeyOpinionLeaderEnum;
use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Campaign\Exports\KeyOpinionLeaderExport;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\Statistic;
use App\Domain\Campaign\Requests\KeyOpinionLeaderRequest;
use App\Domain\Campaign\Requests\KolExcelRequest;
use App\Domain\User\BLL\User\UserBLLInterface;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
    ) {}

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
        // $this->authorize('viewKOL', KeyOpinionLeader::class);

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
            ->addColumn('refresh_follower', function ($row) {
                return '<button class="btn btn-info btn-xs refresh-follower" data-id="' . $row->username . '">
                            <i class="fas fa-sync-alt"></i>
                        </button>';
            })
            ->rawColumns(['actions', 'refresh_follower'])
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
        // $this->authorize('viewKOL', KeyOpinionLeader::class);
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
        // $this->authorize('viewKOL', KeyOpinionLeader::class);

        if ($keyOpinionLeader->followers >= 1000 && $keyOpinionLeader->followers < 10000) {
            $tiering = "Nano";
            $er_top = 0.1;
            $er_bottom = 0.04;
            $cpm_target = 35000;
        } elseif ($keyOpinionLeader->followers >= 10000 && $keyOpinionLeader->followers < 50000) {
            $tiering = "Micro";
            $er_top = 0.05;
            $er_bottom = 0.02;
            $cpm_target = 35000;
        } elseif ($keyOpinionLeader->followers >= 50000 && $keyOpinionLeader->followers < 250000) {
            $tiering = "Mid-Tier";
            $er_top = 0.03;
            $er_bottom = 0.015;
            $cpm_target = 25000;
        } elseif ($keyOpinionLeader->followers >= 250000 && $keyOpinionLeader->followers < 1000000) {
            $tiering = "Macro TOFU";
            $er_top = 0.025;
            $er_bottom = 0.01;
            $cpm_target = 10000;
        } elseif ($keyOpinionLeader->followers >= 1000000 && $keyOpinionLeader->followers < 2000000) {
            $tiering = "Mega TOFU";
            $er_top = 0.02;
            $er_bottom = 0.01;
            $cpm_target = 10000;
        } elseif ($keyOpinionLeader->followers >= 2000000) {
            $tiering = "Mega MOFU";
            $er_top = 0.02;
            $er_bottom = 0.01;
            $cpm_target = 35000;
        } else {
            $tiering = "Unknown";
            $er_top = null;
            $er_bottom = null;
            $cpm_target = null;
        }
        $statistics = Statistic::whereHas('campaignContent', function ($query) use ($keyOpinionLeader) {
            $query->where('username', $keyOpinionLeader->username);
        })->get();

        $total_views = $statistics->sum('view');
        $total_likes = $statistics->sum('like');
        $total_comments = $statistics->sum('comment');

        // Calculate cpm_actual
        $cpm_actual = $total_views > 0
            ? ($keyOpinionLeader->rate / $total_views) * $keyOpinionLeader->followers
            : 0;

        // Calculate er_actual
        $er_actual = $total_views > 0
            ? (($total_likes + $total_comments) / $total_views) * 100
            : 0;

        return view('admin.kol.show', compact('keyOpinionLeader', 'tiering', 'er_top', 'er_bottom', 'cpm_target', 'cpm_actual', 'er_actual'));
    }
    public function chart()
    {
        $data = KeyOpinionLeader::select('channel', DB::raw('count(*) as count'))
            ->groupBy('channel')
            ->get();
        $response = [
            'labels' => $data->pluck('channel'),
            'values' => $data->pluck('count'),
        ];
        return response()->json($response);
    }
    public function averageRate()
    {
        $data = KeyOpinionLeader::select('channel', DB::raw('AVG(rate) as average_rate'))
            ->groupBy('channel')
            ->get();

        $response = [
            'labels' => $data->pluck('channel'),
            'values' => $data->pluck('average_rate')
        ];

        return response()->json($response);
    }

    /**
     * show KOL Json
     */
    public function showJson(KeyOpinionLeader $keyOpinionLeader): JsonResponse
    {
        // $this->authorize('viewKOL', KeyOpinionLeader::class);

        return response()->json($keyOpinionLeader);
    }

    /**
     * Export KOL
     */
    public function export(Request $request): Response|BinaryFileResponse
    {
        // $this->authorize('viewKOL', KeyOpinionLeader::class);

        return (new KeyOpinionLeaderExport())
            ->forChannel($request->input('channel'))
            ->forNiche($request->input('niche'))
            ->forSkinType($request->input('skinType'))
            ->forSkinConcern($request->input('skinConcern'))
            ->forContentType($request->input('contentType'))
            ->forPic($request->input('pic'))
            ->download('kol.xlsx');
    }
    public function refreshFollowersFollowing(string $username): JsonResponse
    {
        $username = preg_replace('/\s*\(.*?\)\s*/', '', $username);
        $keyOpinionLeader = KeyOpinionLeader::where('username', $username)->first();

        if (!$keyOpinionLeader) {
            $kol = KeyOpinionLeader::where('username', 'LIKE', '%' . $username . '%')->first();
            $keyOpinionLeader = KeyOpinionLeader::create([
                'username' => $username,
                'channel' => $kol->channel,
                'niche' => '-',
                'average_view' => 0,
                'skin_type' => '-',
                'skin_concern' => '-',
                'content_type' => '-',
                'rate' => 0,
                'cpm' => 0,
                'created_by' => Auth::user()->id,
                'pic_contact' => Auth::user()->id,
                'followers' => 0,
                'following' => 0,
            ]);
            if (!$keyOpinionLeader) {
                return response()->json(['error' => 'Key Opinion Leader not found'], 404);
            }
        }

        try {
            $channel = $keyOpinionLeader->channel;
            $url = '';
            $headers = [];

            if ($channel === CampaignContentEnum::TiktokVideo) {
                $url = "https://tokapi-mobile-version.p.rapidapi.com/v1/user/@{$username}";
                $headers = [
                    'x-rapidapi-host' => 'tokapi-mobile-version.p.rapidapi.com',
                    'x-rapidapi-key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986',
                ];
            } elseif ($channel === CampaignContentEnum::InstagramFeed) {
                $url = "https://instagram-scraper-api2.p.rapidapi.com/v1/info?username_or_id_or_url={$username}";
                $headers = [
                    'x-rapidapi-host' => 'instagram-scraper-api2.p.rapidapi.com',
                    'x-rapidapi-key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986',
                ];
            } else {
                return response()->json(['error' => 'Unsupported channel type'], 400);
            }

            $response = Http::withHeaders($headers)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $followers = $data['user']['follower_count'] ?? 0;
                $following = $data['user']['following_count'] ?? 0;

                $keyOpinionLeader->update([
                    'followers' => $followers,
                    'following' => $following,
                ]);

                return response()->json(['followers' => $followers, 'following' => $following]);
            } else {
                return response()->json(['error' => 'Failed to fetch data'], $response->status());
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    public function refreshFollowersFollowingSingle(string $username): JsonResponse
    {
        $keyOpinionLeader = KeyOpinionLeader::where('username', $username)->first();

        if (!$keyOpinionLeader) {
            return response()->json(['error' => 'Key Opinion Leader not found'], 404);
        }
        try {
            if ($keyOpinionLeader->channel === 'tiktok_video') {
                $url = "https://tokapi-mobile-version.p.rapidapi.com/v1/user/@{$username}";
                $headers = [
                    'x-rapidapi-host' => 'tokapi-mobile-version.p.rapidapi.com',
                    'x-rapidapi-key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986',
                ];
            } elseif ($keyOpinionLeader->channel === 'instagram_feed') {
                $url = "https://instagram-scraper-api2.p.rapidapi.com/v1/info?username_or_id_or_url={$username}";
                $headers = [
                    'x-rapidapi-host' => 'instagram-scraper-api2.p.rapidapi.com',
                    'x-rapidapi-key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986',
                ];
            } else {
                return response()->json(['error' => 'Unsupported channel type'], 400);
            }
            $response = Http::withHeaders($headers)->get($url);
            if ($response->successful()) {
                $data = $response->json();
                if ($keyOpinionLeader->channel === 'tiktok_video') {
                    $followers = $data['user']['follower_count'] ?? 0;
                    $following = $data['user']['following_count'] ?? 0;
                } elseif ($keyOpinionLeader->channel === 'instagram_feed') {
                    $followers = $data['data']['follower_count'] ?? 0;
                    $following = $data['data']['following_count'] ?? 0;
                }
                $keyOpinionLeader->update([
                    'followers' => $followers,
                    'following' => $following,
                ]);

                return response()->json([
                    'followers' => $followers,
                    'following' => $following,
                    'message' => 'Followers and Following counts updated successfully.',
                ]);
            } else {
                return response()->json(['error' => 'Failed to fetch data from API'], $response->status());
            }
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while refreshing data', 'details' => $e->getMessage()], 500);
        }
    }
}
