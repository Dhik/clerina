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
use App\Domain\Sales\Services\GoogleSheetService;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class KeyOpinionLeaderController extends Controller
{
    protected $googleSheetService;

    public function __construct(
        protected KeyOpinionLeaderBLLInterface $kolBLL,
        protected UserBLLInterface $userBLL,
        GoogleSheetService $googleSheetService
    ) {
        $this->googleSheetService = $googleSheetService;
    }

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
                $waButton = '';
                if (!empty($row->phone_number)) {
                    // Format phone number for WhatsApp (remove non-digits and ensure it starts with country code)
                    $phoneNumber = preg_replace('/[^0-9]/', '', $row->phone_number);
                    // If phone starts with 0, replace with 62 (Indonesia country code)
                    if (substr($phoneNumber, 0, 1) === '0') {
                        $phoneNumber = '62' . substr($phoneNumber, 1);
                    }
                    $waLink = 'https://wa.me/' . $phoneNumber;
                    
                    $waButton = '<a href="' . $waLink . '" class="btn btn-success btn-xs" target="_blank" title="WhatsApp">
                                    <i class="fab fa-whatsapp"></i>
                                </a> ';
                }
                
                return $waButton . 
                    '<a href=' . route('kol.show', $row->id) . ' class="btn btn-success btn-xs" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button onclick="openEditModal(' . $row->id . ')" class="btn btn-primary btn-xs" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>';
            })
            ->addColumn('refresh_follower', function ($row) {
                return '<button class="btn btn-info btn-xs refresh-follower" data-id="' . $row->username . '">
                            <i class="fas fa-sync-alt"></i>
                        </button>';
            })
            ->addColumn('engagement_rate_display', function ($row) {
                return $row->engagement_rate ? number_format($row->engagement_rate, 2) . '%' : '-';
            })
            ->addColumn('views_last_9_post_display', function ($row) {
                if ($row->views_last_9_post === null) {
                    return '<span class="badge badge-secondary">Not Set</span>';
                }
                return $row->views_last_9_post ? 
                    '<span class="badge badge-success">Yes</span>' : 
                    '<span class="badge badge-danger">No</span>';
            })
            ->addColumn('activity_posting_display', function ($row) {
                if ($row->activity_posting === null) {
                    return '<span class="badge badge-secondary">Not Set</span>';
                }
                return $row->activity_posting ? 
                    '<span class="badge badge-success">Active</span>' : 
                    '<span class="badge badge-warning">Inactive</span>';
            })
            ->addColumn('status_affiliate_display', function ($row) {
                if (!$row->status_affiliate) {
                    return '<span class="badge badge-secondary">Not Set</span>';
                }
                
                $badgeClass = match($row->status_affiliate) {
                    'Qualified' => 'badge-success',
                    'Waiting List' => 'badge-warning', 
                    'Not Qualified' => 'badge-danger',
                    default => 'badge-secondary'
                };
                
                return '<span class="badge ' . $badgeClass . '">' . $row->status_affiliate . '</span>';
            })
            ->editColumn('program', function ($row) {
                return $row->program ?? '-';
            })
            ->editColumn('rate', function ($row) {
                return number_format($row->rate, 0, ',', '.');
            })
            ->rawColumns([
                'actions', 
                'refresh_follower', 
                'views_last_9_post_display', 
                'activity_posting_display', 
                'status_affiliate_display'
            ])
            ->toJson();
    }

    public function getKpiData(Request $request): JsonResponse
    {
        // Apply the same filters as your main datatable query
        $query = $this->kolBLL->getKOLDatatable($request);
        
        // Get filtered results for KPI calculation
        $filteredKols = $query->get();
        
        $totalKol = $filteredKols->count();
        $totalAffiliate = $filteredKols->whereNotNull('status_affiliate')->count();
        $activeAffiliate = $filteredKols->where('status_affiliate', 'active')->count();
        $activePosting = $filteredKols->where('activity_posting', true)->count();
        $hasViews = $filteredKols->where('views_last_9_post', true)->count();
        
        // Calculate average engagement rate (only for KOLs with engagement data)
        $kolsWithEngagement = $filteredKols->whereNotNull('engagement_rate');
        $avgEngagement = $kolsWithEngagement->count() > 0 
            ? $kolsWithEngagement->avg('engagement_rate') 
            : 0;
        
        return response()->json([
            'total_kol' => $totalKol,
            'total_affiliate' => $totalAffiliate,
            'active_affiliate' => $activeAffiliate,
            'active_posting' => $activePosting,
            'has_views' => $hasViews,
            'avg_engagement' => round($avgEngagement, 2)
        ]);
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
    public function getEditData(KeyOpinionLeader $keyOpinionLeader): JsonResponse
    {
        $this->authorize('updateKOL', KeyOpinionLeader::class);
        
        return response()->json([
            'id' => $keyOpinionLeader->id,
            'username' => $keyOpinionLeader->username,
            'phone_number' => $keyOpinionLeader->phone_number,
            'views_last_9_post' => $keyOpinionLeader->views_last_9_post,
            'activity_posting' => $keyOpinionLeader->activity_posting,
            // Add all required fields to preserve existing data
            'channel' => $keyOpinionLeader->channel,
            'niche' => $keyOpinionLeader->niche,
            'average_view' => $keyOpinionLeader->average_view,
            'skin_type' => $keyOpinionLeader->skin_type,
            'skin_concern' => $keyOpinionLeader->skin_concern,
            'content_type' => $keyOpinionLeader->content_type,
            'rate' => $keyOpinionLeader->rate,
            'pic_contact' => $keyOpinionLeader->pic_contact,
            'name' => $keyOpinionLeader->name,
            'address' => $keyOpinionLeader->address,
            'bank_name' => $keyOpinionLeader->bank_name,
            'bank_account' => $keyOpinionLeader->bank_account,
            'bank_account_name' => $keyOpinionLeader->bank_account_name,
            'npwp' => $keyOpinionLeader->npwp,
            'npwp_number' => $keyOpinionLeader->npwp_number,
            'nik' => $keyOpinionLeader->nik,
            'notes' => $keyOpinionLeader->notes,
            'product_delivery' => $keyOpinionLeader->product_delivery,
            'product' => $keyOpinionLeader->product,
        ]);
    }

    /**
     * Update KOL
     */
    public function update(KeyOpinionLeader $keyOpinionLeader, KeyOpinionLeaderRequest $request): JsonResponse|RedirectResponse
    {
        $this->authorize('updateKOL', KeyOpinionLeader::class);
        
        try {
            $kol = $this->kolBLL->updateKOL($keyOpinionLeader, $request);
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => trans('messages.success_update', ['model' => trans('labels.key_opinion_leader')]),
                    'data' => $kol
                ]);
            }
            
            return redirect()
                ->route('kol.show', $kol->id)
                ->with([
                    'alert' => 'success',
                    'message' => trans('messages.success_update', ['model' => trans('labels.key_opinion_leader')]),
                ]);
                
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update KOL information.'
                ], 500);
            }
            
            return redirect()->back()->withErrors(['error' => 'Failed to update KOL information.']);
        }
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
            ->forStatusAffiliate($request->input('statusAffiliate'))
            ->forFollowersRange(
                $request->input('followersMin') ? (int) $request->input('followersMin') : null,
                $request->input('followersMax') ? (int) $request->input('followersMax') : null
            )
            ->download('kol-affiliate-data.xlsx');
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
                    $totalLikes = $data['user']['total_favorited'] ?? 0;
                    $videoCount = $data['user']['aweme_count'] ?? 0;
                    
                    // Calculate engagement rate (likes-based)
                    $engagementRate = null;
                    if ($followers > 0 && $videoCount > 0) {
                        $avgLikesPerVideo = $totalLikes / $videoCount;
                        $engagementRate = round(($avgLikesPerVideo / $followers) * 100, 2);
                    }
                    
                } elseif ($keyOpinionLeader->channel === 'instagram_feed') {
                    $followers = $data['data']['follower_count'] ?? 0;
                    $following = $data['data']['following_count'] ?? 0;
                    $totalLikes = $data['data']['total_likes'] ?? 0; // Adjust field name as needed for Instagram
                    $videoCount = $data['data']['media_count'] ?? 0; // Adjust field name as needed for Instagram
                    
                    // Calculate engagement rate for Instagram (if applicable)
                    $engagementRate = null;
                    if ($followers > 0 && $videoCount > 0) {
                        $avgLikesPerPost = $totalLikes / $videoCount;
                        $engagementRate = round(($avgLikesPerPost / $followers) * 100, 2);
                    }
                }
                
                $updateData = [
                    'followers' => $followers,
                    'following' => $following,
                    'total_likes' => $totalLikes,
                    'video_count' => $videoCount,
                ];
                
                // Only update engagement rate if we calculated it
                if ($engagementRate !== null) {
                    $updateData['engagement_rate'] = $engagementRate;
                }
                
                $keyOpinionLeader->update($updateData);
                
                return response()->json([
                    'followers' => $followers,
                    'following' => $following,
                    'total_likes' => $totalLikes,
                    'video_count' => $videoCount,
                    'engagement_rate' => $engagementRate,
                    'message' => 'Profile data updated successfully.',
                ]);
                
            } else {
                return response()->json(['error' => 'Failed to fetch data from API'], $response->status());
            }
            
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while refreshing data', 'details' => $e->getMessage()], 500);
        }
    }
    public function importKeyOpinionLeaders()
    {
        $this->googleSheetService->setSpreadsheetId('11ob241Vwz7EuvhT0V9mBo7u_GDLSIkiVZ_sgKpQ4GfA');
        set_time_limit(0);
        $range = 'Sheet1!A2:H'; // Adjust range based on your data
        $sheetData = $this->googleSheetService->getSheetData($range);

        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Skip if username (column C) is empty
                if (empty($row[2])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process username from column C
                $username = $this->processUsername($row[2]);
                
                // Skip if username processing failed
                if (!$username) {
                    $skippedRows++;
                    continue;
                }

                $kolData = [
                    'username'         => $username,
                    'name'             => $row[3] ?? null, // Column D
                    'phone_number'     => $row[4] ?? null, // Column E
                    'address'          => $row[5] ?? null, // Column F
                    'niche'            => $row[6] ?? null, // Column G
                    'content_type'     => $row[7] ?? null, // Column H
                    'channel'          => 'tiktok_video',
                    'type'             => 'affiliate',
                    'cpm'              => 0,
                    'followers'        => 0,
                    'following'        => 0,
                    'average_view'     => 0,
                    'skin_type'        => '', // Set default or adjust as needed
                    'skin_concern'     => '', // Set default or adjust as needed
                    'rate'             => 0,
                    'updated_at'       => now(),
                ];

                // Check for duplicate by username
                $existingKol = KeyOpinionLeader::where('username', $username)->first();

                if ($existingKol) {
                    // Update existing record
                    $existingKol->update($kolData);
                    $updatedRows++;
                } else {
                    // Create new record
                    $kolData['created_at'] = now();
                    KeyOpinionLeader::create($kolData);
                    $processedRows++;
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Key Opinion Leaders imported successfully',
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows
        ]);
    }

    /**
     * Process username from column C
     * Handle TikTok URLs, @ prefixed usernames, and plain usernames
     */
    private function processUsername($rawUsername)
    {
        if (empty($rawUsername)) {
            return null;
        }

        $username = trim($rawUsername);

        // Case 1: TikTok URL - extract username from URL
        if (strpos($username, 'tiktok.com/@') !== false) {
            preg_match('/tiktok\.com\/@([^?&\/]+)/', $username, $matches);
            if (isset($matches[1])) {
                return $matches[1];
            }
            return null;
        }

        // Case 2: Username with @ prefix - remove the @
        if (strpos($username, '@') === 0) {
            return substr($username, 1);
        }

        // Case 3: Plain username (like user2724318011378) - return as is
        // This handles usernames that don't have @ at first or are not URL type
        return $username;
    }
    public function getBulkUsernames(Request $request): JsonResponse
    {
        $query = $this->kolBLL->getKOLDatatable($request);
        $usernames = $query->whereIn('channel', ['tiktok_video'])
                        ->where('type', 'affiliate')
                        ->where('followers', 0)
                        // ->where('following', 0)
                        // ->where('total_likes', '>', 0)
                        ->whereDate('updated_at', '2025-06-04')
                        ->pluck('username')
                        ->toArray();

        return response()->json([
            'usernames' => $usernames,
            'count' => count($usernames)
        ]);
    }
}
