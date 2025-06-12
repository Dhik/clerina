<?php

namespace App\Domain\ContentPlan\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\ContentPlan\BLL\ContentPlan\ContentPlanBLLInterface;
use App\Domain\ContentPlan\Models\ContentPlan;
use App\Domain\ContentPlan\Requests\ContentPlanRequest;
use Illuminate\Http\Request;

/**
 * @property ContentPlanBLLInterface contentPlanBLL
 */
class ContentPlanController extends Controller
{
    public function __construct(ContentPlanBLLInterface $contentPlanBLL)
    {
        $this->contentPlanBLL = $contentPlanBLL;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ContentPlan::query();
        
        // Filter by status if provided
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('objektif', 'like', "%{$search}%")
                  ->orWhere('jenis_konten', 'like', "%{$search}%")
                  ->orWhere('pillar', 'like', "%{$search}%")
                  ->orWhere('platform', 'like', "%{$search}%");
            });
        }
        
        $contentPlans = $query->orderBy('created_at', 'desc')->paginate(15);
        $statusOptions = ContentPlan::getStatusOptions();
        
        return view('admin.content_plan.index', compact('contentPlans', 'statusOptions'));
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
    public function store(ContentPlanRequest $request)
    {
        $data = $request->validated();
        $data['status'] = ContentPlan::STATUS_DRAFT;
        $data['created_date'] = now();
        
        ContentPlan::create($data);
        
        return redirect()->route('contentPlan.index')
            ->with('success', 'Content plan created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ContentPlan $contentPlan)
    {
        return view('admin.content_plan.show', compact('contentPlan'));
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
    public function update(ContentPlanRequest $request, ContentPlan $contentPlan)
    {
        $step = $request->get('step', 1);
        $data = $request->validated();
        
        // Update status based on step completion
        switch($step) {
            case 1: // Social Media Strategist
                $data['status'] = ContentPlan::STATUS_CONTENT_WRITING;
                break;
            case 2: // Content Writer
                $data['status'] = ContentPlan::STATUS_CREATIVE_REVIEW;
                break;
            case 3: // Creative Leader
                $data['status'] = ContentPlan::STATUS_ADMIN_SUPPORT;
                break;
            case 4: // Admin Support
                $data['status'] = ContentPlan::STATUS_CONTENT_EDITING;
                break;
            case 5: // Content Editor
                $data['status'] = ContentPlan::STATUS_READY_TO_POST;
                break;
            case 6: // Admin Social Media
                $data['status'] = ContentPlan::STATUS_POSTED;
                $data['posting_date'] = now();
                break;
        }
        
        $contentPlan->update($data);
        
        return redirect()->route('contentPlan.index')
            ->with('success', 'Content plan updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ContentPlan $contentPlan)
    {
        $contentPlan->delete();
        
        return redirect()->route('contentPlan.index')
            ->with('success', 'Content plan deleted successfully!');
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
    public function updateStep(Request $request, ContentPlan $contentPlan, $step)
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
        
        $data = $this->getStepValidationRules($request, $step);
        
        // Update status based on step completion
        switch($step) {
            case 1: // Social Media Strategist
                $data['status'] = ContentPlan::STATUS_CONTENT_WRITING;
                break;
            case 2: // Content Writer
                $data['status'] = ContentPlan::STATUS_CREATIVE_REVIEW;
                break;
            case 3: // Creative Leader
                $data['status'] = ContentPlan::STATUS_ADMIN_SUPPORT;
                break;
            case 4: // Admin Support
                $data['status'] = ContentPlan::STATUS_CONTENT_EDITING;
                break;
            case 5: // Content Editor
                $data['status'] = ContentPlan::STATUS_READY_TO_POST;
                break;
            case 6: // Admin Social Media
                $data['status'] = ContentPlan::STATUS_POSTED;
                $data['posting_date'] = now();
                break;
        }
        
        $contentPlan->update($data);
        
        return redirect()->route('contentPlan.index')
            ->with('success', "Step {$step} completed successfully!");
    }

    private function getStepValidationRules($request, $step)
    {
        switch($step) {
            case 1: // Social Media Strategist
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
                ]);
                
            case 2: // Content Writer
                return $request->validate([
                    'brief_konten' => 'nullable|string',
                    'caption' => 'nullable|string',
                ]);
                
            case 3: // Creative Leader
                return $request->validate([
                    'platform' => 'nullable|string|max:255',
                    'akun' => 'nullable|string|max:255',
                ]);
                
            case 4: // Admin Support
                return $request->validate([
                    'kerkun' => 'nullable|string|max:255',
                    'link_raw_content' => 'nullable|string',
                    'assignee_content_editor' => 'nullable|string|max:255',
                ]);
                
            case 5: // Content Editor
                return $request->validate([
                    'link_hasil_edit' => 'nullable|string|max:255',
                ]);
                
            case 6: // Admin Social Media
                return $request->validate([
                    'input_link_posting' => 'nullable|string|max:255',
                ]);
                
            default:
                return [];
        }
    }
}