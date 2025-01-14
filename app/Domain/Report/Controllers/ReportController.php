<?php

namespace App\Domain\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Report\Models\Report;
use App\Domain\Report\Requests\ReportRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $reports = Report::latest()->get();
        return view('admin.report.index_', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.report.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ReportRequest $request
     * @return JsonResponse
     */
    public function store(ReportRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('reports/thumbnails', 'public');
                $data['thumbnail'] = $thumbnailPath;
            }

            $report = Report::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Report created successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Report $report
     * @return JsonResponse
     */
    public function show(Report $report)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        }
        return view('admin.report.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Report  $report
     * @return View
     */
    public function edit(Report $report): View
    {
        return view('admin.report.edit', compact('report'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ReportRequest $request
     * @param Report $report
     * @return JsonResponse
     */
    public function update(ReportRequest $request, Report $report): JsonResponse
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                if ($report->thumbnail) {
                    Storage::disk('public')->delete($report->thumbnail);
                }
                
                $thumbnailPath = $request->file('thumbnail')->store('reports/thumbnails', 'public');
                $data['thumbnail'] = $thumbnailPath;
            }

            $report->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully',
                'data' => $report->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Report $report
     * @return JsonResponse
     */
    public function destroy(Report $report)
    {
        try {
            // Delete thumbnail if exists
            if ($report->thumbnail) {
                Storage::disk('public')->delete($report->thumbnail);
            }

            $report->delete();

            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the reports by filter.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getReportsByFilter(Request $request): JsonResponse
    {
        try {
            $query = Report::query();

            // Apply type filter
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            // Apply platform filter
            if ($request->filled('platform')) {
                $query->where('platform', $request->platform);
            }

            // Apply month filter
            if ($request->filled('month')) {
                $query->where('month', $request->month);
            }

            // Apply date range filter if needed
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);
            }

            $reports = $query->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reports: ' . $e->getMessage()
            ], 500);
        }
    }
}