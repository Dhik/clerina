<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\Models\Approval;
use App\Domain\Talent\Requests\ApprovalRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

/**
 * @property TalentBLLInterface talentBLL
 */
class ApprovalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.approval.index');
    }

    public function data(Request $request)
    {
        $approvals = Approval::select(['id', 'name', 'photo']);

        return DataTables::of($approvals)
            ->addColumn('action', function ($approval) {
                return '
                    <button class="btn btn-sm btn-primary viewButton" 
                        data-id="' . $approval->id . '" 
                        data-toggle="modal" 
                        data-target="#viewApprovalModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $approval->id . '">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" 
                        data-id="' . $approval->id . '">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }
    public function downloadTalentTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new TalentTemplateExport(), 'Talent Template.xlsx');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param TalentRequest $request
     */
    public function store(ApprovalRequest $request)
    {
        $validatedData = $request->validated();

        if ($request->hasFile('photo')) {
            $validatedData['photo'] = $request->file('photo')->store('approvals', 'public');
        }

        Approval::create($validatedData);

        return redirect()->route('approval.index')->with('success', 'Approval created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param Talent $talent
     */
    public function show(Approval $approval)
    {
        return response()->json($approval);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Talent  $talent
     */
    public function edit(Approval $approval)
    {
        return response()->json($approval);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TalentRequest $request
     * @param  Talent  $talent
     */
    public function update(ApprovalRequest $request, Approval $approval)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            if ($request->hasFile('photo')) {
                $data['photo'] = $request->file('photo')->store('approvals', 'public');
            }

            $approval->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Approval updated successfully',
                'approval' => $approval
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update approval',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Talent $talent
     */
    public function destroy($id)
    {
        $approval = Approval::findOrFail($id);
        $approval->delete();
        return response()->json(['success' => true]);
    }
}
