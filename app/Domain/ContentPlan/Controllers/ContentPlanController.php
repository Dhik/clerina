<?php

namespace App\Domain\ContentPlan\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\ContentPlan\BLL\ContentPlan\ContentPlanBLLInterface;
use App\Domain\ContentPlan\Models\ContentPlan;
use App\Domain\ContentPlan\Requests\ContentPlanRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use DataTables;

/**
 * @property ContentPlanBLLInterface contentPlanBLL
 */
class ContentPlanController extends Controller
{
    public function __construct(ContentPlanBLLInterface $contentPlanBLL)
    {
        $this->contentPlanBLL = $contentPlanBLL;
    }

    public function calendar(Request $request)
    {
        $statusOptions = ContentPlan::getStatusOptions();
        return view('admin.content_plan.calendar', compact('statusOptions'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $statusOptions = ContentPlan::getStatusOptions();
        return view('admin.content_plan.index', compact('statusOptions'));
    }

    /**
     * Get data for DataTables or Calendar
     */
    public function data(Request $request): JsonResponse
    {
        $query = ContentPlan::query();
        
        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // If this is a calendar request (length = -1), return all data without pagination
        if ($request->get('length') == -1) {
            $contentPlans = $query->get();
            
            $data = $contentPlans->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    // Updated: target_posting_date is now datetime
                    'target_posting_date' => $plan->target_posting_date ? 
                        $plan->target_posting_date->format('Y-m-d H:i:s') : 
                        null,
                    'status' => $plan->status,
                    'status_label' => $plan->status_label,
                    'objektif' => $plan->objektif,
                    'jenis_konten' => $plan->jenis_konten,
                    'pillar' => $plan->pillar,
                    'platform' => $plan->platform,
                    'talent' => $plan->talent,
                    'caption' => $plan->caption,
                    'venue' => $plan->venue,
                    'hook' => $plan->hook,
                    'produk' => $plan->produk,
                    'referensi' => $plan->referensi,
                    'akun' => $plan->akun,
                    'kerkun' => $plan->kerkun,
                    'brief_konten' => $plan->brief_konten,
                    'link_raw_content' => $plan->link_raw_content,
                    'assignee_content_editor' => $plan->assignee_content_editor,
                    'link_hasil_edit' => $plan->link_hasil_edit,
                    'input_link_posting' => $plan->input_link_posting,
                    'posting_date' => $plan->posting_date ? 
                        $plan->posting_date->format('Y-m-d') : 
                        null,
                    // New fields
                    'talent_fix' => $plan->talent_fix,
                    'booking_talent_date' => $plan->booking_talent_date ? 
                        $plan->booking_talent_date->format('Y-m-d H:i:s') : 
                        null,
                    'booking_venue_date' => $plan->booking_venue_date ? 
                        $plan->booking_venue_date->format('Y-m-d H:i:s') : 
                        null,
                    'production_date' => $plan->production_date ? 
                        $plan->production_date->format('Y-m-d H:i:s') : 
                        null,
                    'created_at' => $plan->created_at ? 
                        $plan->created_at->format('Y-m-d H:i:s') : 
                        null,
                    'updated_at' => $plan->updated_at ? 
                        $plan->updated_at->format('Y-m-d H:i:s') : 
                        null,
                ];
            });
            
            return response()->json([
                'data' => $data
            ]);
        }

        // Original DataTables response
        return DataTables::of($query)
            ->addColumn('target_date', function ($plan) {
                // Updated: show datetime format for target_posting_date
                return $plan->target_posting_date ? 
                    $plan->target_posting_date->format('Y-m-d H:i') : 
                    '-';
            })
            ->addColumn('status_badge', function ($plan) {
                $badgeColor = $this->getStatusBadgeColor($plan->status);
                return '<span class="badge badge-' . $badgeColor . '">' . $plan->status_label . '</span>';
            })
            ->addColumn('created_date', function ($plan) {
                return $plan->created_at ? 
                    $plan->created_at->format('Y-m-d') : 
                    '-';
            })
            ->addColumn('action', function ($plan) {
                return $this->generateActionButtons($plan);
            })
            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $contentPlan = new ContentPlan();
        return view('admin.content_plan.create', compact('contentPlan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContentPlanRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['status'] = ContentPlan::STATUS_DRAFT;
            $data['created_date'] = now();
            
            $contentPlan = ContentPlan::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Content plan created successfully!',
                'data' => $contentPlan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating content plan: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentPlan $contentPlan)
    {
        return view('admin.content_plan.show', compact('contentPlan'));
    }

    /**
     * Get details for a specific content plan
     */
    public function getDetails(ContentPlan $contentPlan): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $contentPlan
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ContentPlan $contentPlan, Request $request)
    {
        $step = $request->get('step', 1);
        return view('admin.content_plan.edit', compact('contentPlan', 'step'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContentPlanRequest $request, ContentPlan $contentPlan): JsonResponse
    {
        try {
            $data = $request->validated();
            $contentPlan->update($data);
            
            return response()->json([
                'success' => true,
                'message' => 'Content plan updated successfully!',
                'data' => $contentPlan->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating content plan: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentPlan $contentPlan): JsonResponse
    {
        try {
            $contentPlan->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Content plan deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting content plan: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show step-specific edit forms
     */
    public function editStep(ContentPlan $contentPlan, $step)
    {
        // Validate step number
        if (!in_array($step, [1, 2, 3, 4, 5, 6])) {
            abort(404);
        }
        
        // Check if user can edit this step
        if (!$contentPlan->canEditByStep($step)) {
            return redirect()->route('contentPlan.index')
                ->with('error', 'This content plan is not ready for this step.');
        }
        
        return view('admin.content_plan.edit_step', compact('contentPlan', 'step'));
    }

    /**
     * Update specific step
     */
    public function updateStep(Request $request, ContentPlan $contentPlan, $step): JsonResponse
    {
        try {
            // Validate step number
            if (!in_array($step, [1, 2, 3, 4, 5, 6])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid step number'
                ], 422);
            }
            
            // Check if user can edit this step
            if (!$contentPlan->canEditByStep($step)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This content plan is not ready for this step.'
                ], 422);
            }
            
            $data = $this->getStepValidationRules($request, $step);
            
            // Updated status progression based on new workflow
            switch($step) {
                case 1: // Social Media Strategist (now includes platform/account)
                    $data['status'] = ContentPlan::STATUS_CONTENT_WRITING;
                    break;
                case 2: // Content Writer
                    $data['status'] = ContentPlan::STATUS_ADMIN_SUPPORT;
                    break;
                case 3: // Admin Support (now includes booking dates)
                    $data['status'] = ContentPlan::STATUS_CREATIVE_REVIEW;
                    break;
                case 4: // Creative Review (moved from step 3)
                    $data['status'] = ContentPlan::STATUS_CONTENT_EDITING;
                    break;
                case 5: // Content Editor
                    $data['status'] = ContentPlan::STATUS_READY_TO_POST;
                    break;
                case 6: // Store to Content Bank
                    $data['status'] = ContentPlan::STATUS_POSTED;
                    $data['posting_date'] = now();
                    break;
            }
            
            $contentPlan->update($data);
            
            return response()->json([
                'success' => true,
                'message' => "Step {$step} completed successfully!",
                'data' => $contentPlan->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating step: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get status counts for dashboard
     */
    public function getStatusCounts(): JsonResponse
    {
        $counts = ContentPlan::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusOptions = ContentPlan::getStatusOptions();
        $result = [];

        foreach ($statusOptions as $status => $label) {
            $result[$status] = [
                'label' => $label,
                'count' => $counts->get($status)->count ?? 0
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    private function getStepValidationRules($request, $step)
    {
        switch($step) {
            case 1: // Social Media Strategist (now includes platform/account)
                return $request->validate([
                    'objektif' => 'nullable|string|max:255',
                    'jenis_konten' => 'nullable|string|max:255',
                    'pillar' => 'nullable|string|max:255',
                    'sub_pillar' => 'nullable|string|max:255',
                    'talent' => 'nullable|string|max:255',
                    'venue' => 'nullable|string|max:255',
                    'hook' => 'nullable|string',
                    'produk' => 'nullable|string|max:255',
                    'referensi' => 'nullable|string|max:255',
                    'target_posting_date' => 'nullable|date',
                    // Moved from step 3
                    'platform' => 'nullable|string|max:255',
                    'akun' => 'nullable|string|max:255',
                ]);
                
            case 2: // Content Writer
                return $request->validate([
                    'brief_konten' => 'nullable|string',
                    'caption' => 'nullable|string',
                ]);
                
            case 3: // Admin Support (now only booking dates, no resource management)
                return $request->validate([
                    'talent_fix' => 'nullable|string|max:255',
                    'booking_talent_date' => 'nullable|date',
                    'booking_venue_date' => 'nullable|date',
                    'production_date' => 'nullable|date',
                ]);
                
            case 4: // Creative Review (now includes resource management)
                return $request->validate([
                    'kerkun' => 'nullable|string|max:255',
                    'link_raw_content' => 'nullable|string',
                    'assignee_content_editor' => 'nullable|string|max:255',
                    'review_comments' => 'nullable|string',
                ]);
                
            case 5: // Content Editor
                return $request->validate([
                    'link_hasil_edit' => 'nullable|string|max:255',
                ]);
                
            case 6: // Store to Content Bank
                return $request->validate([
                    'input_link_posting' => 'nullable|string|max:255',
                ]);
                
            default:
                return [];
        }
    }

    private function getStatusBadgeColor($status)
    {
        switch($status) {
            case 'draft': return 'secondary';
            case 'content_writing': return 'info';
            case 'admin_support': return 'primary';
            case 'creative_review': return 'warning';
            case 'content_editing': return 'dark';
            case 'ready_to_post': return 'success';
            case 'posted': return 'success';
            default: return 'light';
        }
    }

    private function generateActionButtons($plan)
    {
        $buttons = '<div class="btn-group" role="group">';
        
        // View button
        $buttons .= '<button type="button" class="btn btn-info btn-sm viewButton" data-id="' . $plan->id . '" title="View">
                        <i class="fas fa-eye"></i>
                     </button>';
        
        // Edit button
        $buttons .= '<button type="button" class="btn btn-warning btn-sm editButton" data-id="' . $plan->id . '" title="Edit">
                        <i class="fas fa-edit"></i>
                     </button>';
        
        // Step-specific edit buttons (updated for new workflow)
        if ($plan->status == 'draft') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $plan->id . '" data-step="1" title="Step 1: Strategy">
                            <i class="fas fa-clipboard-list"></i> 1
                         </button>';
        } elseif ($plan->status == 'content_writing') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $plan->id . '" data-step="2" title="Step 2: Content Writing">
                            <i class="fas fa-pen"></i> 2
                         </button>';
        } elseif ($plan->status == 'admin_support') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $plan->id . '" data-step="3" title="Step 3: Admin Support">
                            <i class="fas fa-users-cog"></i> 3
                         </button>';
        } elseif ($plan->status == 'creative_review') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $plan->id . '" data-step="4" title="Step 4: Creative Review">
                            <i class="fas fa-check-double"></i> 4
                         </button>';
        } elseif ($plan->status == 'content_editing') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $plan->id . '" data-step="5" title="Step 5: Content Editing">
                            <i class="fas fa-edit"></i> 5
                         </button>';
        } elseif ($plan->status == 'ready_to_post') {
            $buttons .= '<button type="button" class="btn btn-primary btn-sm stepButton" data-id="' . $plan->id . '" data-step="6" title="Step 6: Store to Content Bank">
                            <i class="fas fa-database"></i> 6
                         </button>';
        }
        
        // Delete button
        $buttons .= '<button type="button" class="btn btn-danger btn-sm deleteButton" data-id="' . $plan->id . '" title="Delete">
                        <i class="fas fa-trash"></i>
                     </button>';
        
        $buttons .= '</div>';
        
        return $buttons;
    }
}