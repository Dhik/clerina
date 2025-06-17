<?php

namespace App\Domain\ContentAds\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\ContentAds\Models\ContentAds;
use App\Domain\ContentAds\Requests\ContentAdsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use DataTables;
use Carbon\Carbon;

class ContentAdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statusOptions = ContentAds::getStatusOptions();
        $productOptions = ContentAds::getProductOptions();
        $platformOptions = ContentAds::getPlatformOptions();
        $funnelingOptions = ContentAds::getFunnelingOptions();
        
        return view('admin.content_ads.index', compact(
            'statusOptions', 
            'productOptions', 
            'platformOptions', 
            'funnelingOptions'
        ));
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request): JsonResponse
    {
        $query = ContentAds::with('assignee');
        
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
            ->addColumn('created_date', function ($ads) {
                return $ads->created_at ? 
                    (is_string($ads->created_at) ? $ads->created_at : $ads->created_at->format('Y-m-d')) : 
                    '-';
            })
            ->addColumn('assignee_name', function ($ads) {
                return $ads->assignee ? $ads->assignee->name : '-';
            })
            ->addColumn('tugas_status', function ($ads) {
                return $ads->tugas_selesai ? 
                    '<span class="badge badge-success">Completed</span>' : 
                    '<span class="badge badge-warning">Pending</span>';
            })
            ->addColumn('action', function ($ads) {
                return $this->generateActionButtons($ads);
            })
            ->rawColumns(['status_badge', 'tugas_status', 'action'])
            ->make(true);
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
        $contentAds->load('assignee');
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

        // Daily content created per person
        $dailyPerPerson = ContentAds::with('assignee')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('assignee_id, DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('assignee_id', 'date')
            ->get()
            ->groupBy('assignee.name');

        // Per product statistics
        $perProduct = ContentAds::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('product, COUNT(*) as count')
            ->groupBy('product')
            ->get()
            ->keyBy('product');

        // Per funnel statistics
        $perFunnel = ContentAds::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('funneling, COUNT(*) as count')
            ->groupBy('funneling')
            ->get()
            ->keyBy('funneling');

        // Combined product and funnel
        $productFunnel = ContentAds::completed()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('product, funneling, COUNT(*) as count')
            ->groupBy('product', 'funneling')
            ->get()
            ->groupBy('product')
            ->map(function ($items) {
                return $items->keyBy('funneling');
            });

        return response()->json([
            'success' => true,
            'data' => [
                'daily_per_person' => $dailyPerPerson,
                'per_product' => $perProduct,
                'per_funnel' => $perFunnel,
                'product_funnel' => $productFunnel,
                'total_completed' => ContentAds::completed()
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'total_pending' => ContentAds::where('status', '!=', ContentAds::STATUS_COMPLETED)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
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
                    'assignee_id' => 'nullable|exists:users,id',
                ]);
                
            case 2: // Link Drive & Task
                return $request->validate([
                    'link_drive' => 'nullable|string|max:255',
                    'tugas_selesai' => 'nullable|boolean',
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
}