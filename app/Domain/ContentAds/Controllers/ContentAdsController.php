<?php

namespace App\Domain\ContentAds\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\ContentAds\Models\ContentAds;
use App\Domain\ContentAds\Requests\ContentAdsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Domain\Sales\Services\GoogleSheetService;

class ContentAdsController extends Controller
{
    protected $googleSheetService;

    public function __construct(
        GoogleSheetService $googleSheetService
    ) { 
        $this->googleSheetService = $googleSheetService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statusOptions = ContentAds::getStatusOptions();
        $productOptions = ContentAds::getProductOptions();
        $platformOptions = ContentAds::getPlatformOptions();
        $funnelingOptions = ContentAds::getFunnelingOptions();
        $editorOptions = ContentAds::getEditorOptions();
        
        return view('admin.content_ads.index', compact(
            'statusOptions', 
            'productOptions', 
            'platformOptions', 
            'funnelingOptions',
            'editorOptions'
        ));
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request): JsonResponse
    {
        $query = ContentAds::query();
        
        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('product') && $request->product != '') {
            $query->where('product', $request->product);
        }
        
        if ($request->has('platform') && $request->platform != '') {
            $query->where('platform', $request->platform);
        }

        // Add funneling filter
        if ($request->has('funneling') && $request->funneling != '') {
            $query->where('funneling', $request->funneling);
        }
        
        return DataTables::of($query)
            ->addColumn('request_date_formatted', function ($ads) {
                return $ads->request_date ? 
                    (is_string($ads->request_date) ? $ads->request_date : $ads->request_date->format('Y-m-d')) : 
                    '-';
            })
            ->addColumn('status_badge', function ($ads) {
                $badgeColor = $this->getStatusBadgeColor($ads->status);
                return '<span class="badge badge-' . $badgeColor . '">' . $ads->status_label . '</span>';
            })
            ->addColumn('funneling_button', function ($ads) {
                return $this->getFunnelingButton($ads->funneling);
            })
            ->addColumn('created_date', function ($ads) {
                return $ads->created_at ? 
                    (is_string($ads->created_at) ? $ads->created_at : $ads->created_at->format('Y-m-d')) : 
                    '-';
            })
            ->addColumn('action', function ($ads) {
                return $this->generateActionButtons($ads);
            })
            ->rawColumns(['status_badge', 'funneling_button', 'action'])
            ->make(true);
    }

    /**
     * Get funneling button with appropriate color
     */
    private function getFunnelingButton($funneling)
    {
        if (!$funneling) {
            return '<span class="badge badge-light">-</span>';
        }

        switch (strtoupper($funneling)) {
            case 'TOFU':
                return '<span class="btn btn-success btn-xs" style="pointer-events: none; font-size: 0.75rem; padding: 2px 8px;">
                            <i class="fas fa-funnel-dollar"></i> TOFU
                        </span>';
            case 'MOFU':
                return '<span class="btn btn-info btn-xs" style="pointer-events: none; font-size: 0.75rem; padding: 2px 8px;">
                            <i class="fas fa-filter"></i> MOFU
                        </span>';
            case 'BOFU':
                return '<span class="btn btn-primary btn-xs" style="pointer-events: none; font-size: 0.75rem; padding: 2px 8px;">
                            <i class="fas fa-bullseye"></i> BOFU
                        </span>';
            default:
                return '<span class="badge badge-secondary">' . htmlspecialchars($funneling) . '</span>';
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContentAdsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['status'] = ContentAds::STATUS_STEP1;
            
            $contentAds = ContentAds::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Content ads created successfully!',
                'data' => $contentAds
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating content ads: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentAds $contentAds)
    {
        return view('admin.content_ads.show', compact('contentAds'));
    }

    /**
     * Get details for a specific content ads
     */
    public function getDetails(ContentAds $contentAds): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $contentAds
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContentAdsRequest $request, ContentAds $contentAds): JsonResponse
    {
        try {
            $data = $request->validated();
            $contentAds->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Content ads updated successfully!',
                'data' => $contentAds->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating content ads: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentAds $contentAds): JsonResponse
    {
        try {
            $contentAds->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Content ads deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting content ads: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show step-specific edit forms
     */
    public function editStep(ContentAds $contentAds, $step)
    {
        // Validate step number
        if (!in_array($step, [1, 2, 3])) {
            abort(404);
        }
        
        // Check if user can edit this step
        if (!$contentAds->canEditByStep($step)) {
            return redirect()->route('contentAds.index')
                ->with('error', 'This content ads is not ready for this step.');
        }
        
        return view('admin.content_ads.edit_step', compact('contentAds', 'step'));
    }

    /**
     * Update specific step
     */
    public function updateStep(Request $request, ContentAds $contentAds, $step): JsonResponse
    {
        try {
            // Validate step number
            if (!in_array($step, [1, 2, 3])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid step number'
                ], 422);
            }
            
            $data = $this->getStepValidationRules($request, $step);
            
            // Update status based on step completion
            switch($step) {
                case 1: // Initial Request
                    $data['status'] = ContentAds::STATUS_STEP2;
                    break;
                case 2: // Link Drive & Task
                    $data['status'] = ContentAds::STATUS_STEP3;
                    break;
                case 3: // File Naming
                    $data['status'] = ContentAds::STATUS_COMPLETED;
                    break;
            }
            
            $contentAds->update($data);
            
            return response()->json([
                'success' => true,
                'message' => "Step {$step} completed successfully!",
                'data' => $contentAds->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating step: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get KPI data for dashboard
     */
    public function getKpiData(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        // Daily content created per editor
        $dailyPerEditor = ContentAds::selectRaw('editor, DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('editor', 'date')
            ->get()
            ->groupBy('editor');

        // Per product statistics
        $perProduct = ContentAds::where('status', ContentAds::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('product, COUNT(*) as count')
            ->groupBy('product')
            ->get()
            ->keyBy('product');

        // Per funnel statistics
        $perFunnel = ContentAds::where('status', ContentAds::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('funneling, COUNT(*) as count')
            ->groupBy('funneling')
            ->get()
            ->keyBy('funneling');

        // Combined product and funnel
        $productFunnel = ContentAds::where('status', ContentAds::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('product, funneling, COUNT(*) as count')
            ->groupBy('product', 'funneling')
            ->get()
            ->groupBy('product')
            ->map(function ($items) {
                return $items->keyBy('funneling');
            });

        // Per editor statistics
        $perEditor = ContentAds::where('status', ContentAds::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('editor, COUNT(*) as count')
            ->groupBy('editor')
            ->get()
            ->keyBy('editor');

        return response()->json([
            'success' => true,
            'data' => [
                'daily_per_editor' => $dailyPerEditor,
                'per_product' => $perProduct,
                'per_funnel' => $perFunnel,
                'per_editor' => $perEditor,
                'product_funnel' => $productFunnel,
                'total_completed' => ContentAds::where('status', ContentAds::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'total_pending' => ContentAds::where('status', '!=', ContentAds::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'total_created_today' => ContentAds::whereDate('created_at', Carbon::today())
                    ->count(),
                'total_by_platform' => ContentAds::where('status', ContentAds::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('platform, COUNT(*) as count')
                    ->groupBy('platform')
                    ->get()
                    ->keyBy('platform'),
            ]
        ]);
    }

    private function getStepValidationRules($request, $step)
    {
        switch($step) {
            case 1: // Initial Request
                return $request->validate([
                    'link_ref' => 'nullable|string|max:255',
                    'desc_request' => 'nullable|string',
                    'product' => 'nullable|string|max:255',
                    'platform' => 'nullable|string|max:255',
                    'funneling' => 'nullable|string|max:255',
                    'request_date' => 'nullable|date',
                ]);
                
            case 2: // Link Drive & Task
                return $request->validate([
                    'link_drive' => 'nullable|string|max:255',
                    'editor' => 'nullable|string|in:RAFI,HENDRA',
                ]);
                
            case 3: // File Naming
                return $request->validate([
                    'filename' => 'nullable|string|max:255',
                ]);
                
            default:
                return [];
        }
    }

    private function getStatusBadgeColor($status)
    {
        switch($status) {
            case 'step1': return 'secondary';
            case 'step2': return 'info';
            case 'step3': return 'warning';
            case 'completed': return 'success';
            default: return 'light';
        }
    }

    private function generateActionButtons($ads)
    {
        $buttons = '<div class="btn-group" role="group">';
        
        // View button
        $buttons .= '<button type="button" class="btn btn-info btn-sm viewButton" data-id="' . $ads->id . '" title="View">
                        <i class="fas fa-eye"></i>
                     </button>';
        
        // Edit button
        $buttons .= '<button type="button" class="btn btn-warning btn-sm editButton" data-id="' . $ads->id . '" title="Edit">
                        <i class="fas fa-edit"></i>
                     </button>';
        
        // Step-specific edit buttons
        if ($ads->status == 'step1') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $ads->id . '" data-step="1" title="Step 1: Initial Request">
                            <i class="fas fa-clipboard-list"></i> 1
                         </button>';
        } elseif ($ads->status == 'step2') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $ads->id . '" data-step="2" title="Step 2: Link Drive & Task">
                            <i class="fas fa-link"></i> 2
                         </button>';
        } elseif ($ads->status == 'step3') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $ads->id . '" data-step="3" title="Step 3: File Naming">
                            <i class="fas fa-file-alt"></i> 3
                         </button>';
        }
        
        // Delete button
        $buttons .= '<button type="button" class="btn btn-danger btn-sm deleteButton" data-id="' . $ads->id . '" title="Delete">
                        <i class="fas fa-trash"></i>
                     </button>';
        
        $buttons .= '</div>';
        
        return $buttons;
    }
public function importContentAdsFromGSheet()
{
    $this->googleSheetService->setSpreadsheetId('1VWc_2OoW2W_l-o-itXw9ZfXS0xl1QF__58Q8Tnt3M8Y');
    $range = 'Sheet1!A2:L'; // From A2 to cover all columns up to L
    $sheetData = $this->googleSheetService->getSheetData($range);

    $totalRows = count($sheetData);
    $processedRows = 0;
    $skippedRows = 0;
    $newContentAdsCreated = 0;
    $contentAdsUpdated = 0;

    DB::beginTransaction();
    
    try {
        foreach ($sheetData as $rowIndex => $row) {
            // Skip if essential fields are empty
            if (empty($row[1]) || empty($row[2]) || empty($row[4])) { // editor, platform, product
                \Log::info("Skipping row " . ($rowIndex + 2) . ": Missing essential data - Editor: " . ($row[1] ?? 'empty') . ", Platform: " . ($row[2] ?? 'empty') . ", Product: " . ($row[4] ?? 'empty'));
                $skippedRows++;
                continue;
            }
            
            try {
                // Map only the required columns
                $editor = isset($row[1]) ? trim($row[1]) : null; // Column B = editor
                $platform = isset($row[2]) ? trim($row[2]) : null; // Column C = platform
                $funneling = isset($row[3]) ? trim($row[3]) : null; // Column D = funneling
                $product = isset($row[4]) ? trim($row[4]) : null; // Column E = product
                $filename = isset($row[5]) ? trim($row[5]) : null; // Column F = filename
                $linkDrive = isset($row[6]) ? trim($row[6]) : null; // Column G = link_drive
                // Column H = Evidence (skip)
                $requestDate = null;
                if (isset($row[8]) && !empty($row[8])) { // Column I = request_date
                    try {
                        // Handle DD/MM/YYYY format
                        if (strpos($row[8], '/') !== false) {
                            $requestDate = Carbon::createFromFormat('d/m/Y', $row[8])->format('Y-m-d');
                        } else {
                            $requestDate = Carbon::parse($row[8])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Invalid date format in row " . ($rowIndex + 2) . ": " . $row[8]);
                    }
                }
                // Columns J, K are skipped
                $status = isset($row[11]) ? trim($row[11]) : 'step1'; // Column L = status

                // Validate and normalize editor values
                $validEditors = ['RAFI', 'HENDRA'];
                $editor = strtoupper($editor);
                if (!in_array($editor, $validEditors)) {
                    \Log::warning("Invalid editor value: " . $editor . " in row " . ($rowIndex + 2) . ", skipping.");
                    $skippedRows++;
                    continue;
                }

                // Validate and normalize platform values
                $validPlatforms = ['META', 'TIKTOK'];
                $platform = strtoupper($platform);
                if (!in_array($platform, $validPlatforms)) {
                    \Log::warning("Invalid platform value: " . $platform . " in row " . ($rowIndex + 2) . ", skipping.");
                    $skippedRows++;
                    continue;
                }

                // Validate funneling values
                $validFunnelings = ['TOFU', 'MOFU', 'BOFU', 'None'];
                if ($funneling && !in_array($funneling, $validFunnelings)) {
                    \Log::warning("Invalid funneling value: " . $funneling . " in row " . ($rowIndex + 2) . ", skipping.");
                    $skippedRows++;
                    continue;
                }

                // Validate product values - be more flexible with product matching
                $validProducts = ['3MIN', 'JB', 'CAL', 'RS', 'GS', 'PG', '30SEC', 'AcneS', 'RSXJB', '3MINXJB', 'None'];
                
                // Map common variations
                $productMappings = [
                    '3 MIN' => '3MIN',
                    '30 SEC' => '30SEC',
                    'Acne S' => 'AcneS',
                ];
                
                if (isset($productMappings[$product])) {
                    $product = $productMappings[$product];
                }
                
                if (!in_array($product, $validProducts)) {
                    \Log::warning("Invalid product value: " . $product . " in row " . ($rowIndex + 2) . ", skipping.");
                    $skippedRows++;
                    continue;
                }

                // Normalize status values
                $statusMappings = [
                    'Done' => 'completed',
                    'done' => 'completed',
                    'DONE' => 'completed',
                    'Completed' => 'completed',
                    'COMPLETED' => 'completed',
                ];
                
                if (isset($statusMappings[$status])) {
                    $status = $statusMappings[$status];
                }
                
                $validStatuses = ['step1', 'step2', 'step3', 'completed'];
                if (!in_array($status, $validStatuses)) {
                    $status = 'completed'; // Default to completed since most seem to be "Done"
                }

                // Try to find existing content ads with similar data
                $existingContentAds = ContentAds::where('editor', $editor)
                    ->where('platform', $platform)
                    ->where('product', $product)
                    ->where('filename', $filename)
                    ->first();
                
                if ($existingContentAds) {
                    // Update existing content ads
                    $existingContentAds->funneling = $funneling;
                    $existingContentAds->request_date = $requestDate;
                    $existingContentAds->status = $status;
                    $existingContentAds->link_drive = $linkDrive;
                    $existingContentAds->updated_at = now();
                    $existingContentAds->save();
                    
                    $contentAdsUpdated++;
                } else {
                    // Create new content ads - only with the required fields
                    $contentAdsData = [
                        'link_ref'              => null,
                        'desc_request'          => null,
                        'product'               => $product,
                        'platform'              => $platform,
                        'funneling'             => $funneling,
                        'request_date'          => $requestDate,
                        'link_drive'            => $linkDrive,
                        'editor'                => $editor,
                        'status'                => $status,
                        'filename'              => $filename,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                    
                    ContentAds::create($contentAdsData);
                    $newContentAdsCreated++;
                }
                
                $processedRows++;
                
            } catch (\Exception $e) {
                \Log::warning("Error processing content ads row " . ($rowIndex + 2) . ": " . json_encode($row) . " - " . $e->getMessage());
                $skippedRows++;
            }
        }
        
        // Commit all changes
        DB::commit();
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error processing Content Ads data: " . $e->getMessage());
        
        return response()->json([
            'message' => 'Error processing Content Ads data', 
            'error' => $e->getMessage()
        ], 500);
    }

    return response()->json([
        'message' => 'Content Ads data imported successfully', 
        'total_rows' => $totalRows,
        'processed_rows' => $processedRows,
        'new_content_ads_created' => $newContentAdsCreated,
        'content_ads_updated' => $contentAdsUpdated,
        'skipped_rows' => $skippedRows
    ]);
}
}