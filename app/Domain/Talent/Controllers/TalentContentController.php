<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Requests\TalentContentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;

/**
 */
class TalentContentController extends Controller
{
    protected $talentContentBLL;
    public function __construct(TalentBLLInterface $talentContentBLL)
    {
        $this->talentContentBLL = $talentContentBLL;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.talent_content.index');
    }
    protected function statusAndLinkHtml($talentContent): string
    {
        $doneButton = $talentContent->done 
            ? '<button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Done">
                <i class="fas fa-check-circle text-success"></i>
            </button>'
            : '<button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Not Done">
                <i class="fas fa-times-circle text-secondary"></i>
            </button>';
        $uploadLinkButton = !empty($talentContent->upload_link) 
            ? '<a href="' . $talentContent->upload_link . '" target="_blank" class="btn btn-light" data-toggle="tooltip" data-placement="top" title="View Upload">
                <i class="fas fa-link"></i>
            </a>'
            : '<span class="text-black-50">No Link</span>';

        return $doneButton . ' ' . $uploadLinkButton;
    }
        
    public function data(Request $request)
    {
        $talentContents = TalentContent::select([
            'talent_content.id', 
            'talent_content.talent_id', 
            'talent_content.dealing_upload_date',
            'talent_content.posting_date', 
            'talent_content.done', 
            'talent_content.upload_link', 
            'talents.talent_name',
            'talent_content.final_rate_card'])
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id');

        return DataTables::of($talentContents)
            ->addColumn('status_and_link', function ($talentContent) {
                return $this->statusAndLinkHtml($talentContent);
            })
            ->addColumn('action', function ($talentContent) {
                return '
                    <button class="btn btn-sm btn-primary viewButton" 
                        data-id="' . $talentContent->id . '" 
                        data-toggle="modal" 
                        data-target="#viewTalentContentModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->rawColumns(['action', 'status_and_link']) // Ensure this includes your new column
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param TalentContentRequest $request
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'talent_id' => 'required|integer',
            'transfer_date' => 'nullable|date',
            'dealing_upload_date' => 'nullable|date',
            'posting_date' => 'nullable|date',
            'done' => 'required|boolean',
            'upload_link' => 'nullable|string|max:255',
            'pic_code' => 'nullable|string|max:255',
            'boost_code' => 'nullable|string|max:255',
            'kerkun' => 'required|boolean',
            'final_rate_card' => 'required',
        ]);

        // Create a new TalentContent record
        TalentContent::create($request->all());

        // Redirect back to the talent contents index page with a success message
        return redirect()->route('talent_content.index')->with('success', 'Talent content created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param TalentContent $talentContent
     */
    public function show(TalentContent $talentContent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TalentContent $talentContent
     */
    public function edit($id)
    {
        $talentContent = TalentContent::find($id);

        if (!$talentContent) {
            return response()->json(['error' => 'Talent content not found'], 404);
        }
        return response()->json(['talentContent' => $talentContent]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param TalentContentRequest $request
     * @param TalentContent $talentContent
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'transfer_date' => 'required|date',
            'dealing_upload_date' => 'nullable|date',
            'posting_date' => 'nullable|date',
            'done' => 'required|boolean',
            'upload_link' => 'nullable|url',
            'final_rate_card' => 'nullable|max:255',
            'pic_code' => 'nullable|string|max:255',
            'boost_code' => 'nullable|string|max:255',
            'kerkun' => 'required|boolean',
        ]);

        $talentContent = TalentContent::find($id);
        if (!$talentContent) {
            return response()->json(['error' => 'Talent content not found'], 404);
        }

        try {
            $talentContent->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Talent content updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update talent content. ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param TalentContent $talentContent
     */
    public function destroy($id)
    {
        $talentContent = TalentContent::findOrFail($id);
        $talentContent->delete();
        return response()->json(['success' => true]);
    }
    public function getTalents()
    {
        $talents = Talent::select('id', 'talent_name')->get();
        return response()->json($talents);
    }
    public function getTodayTalentNames()
    {
        $today = Carbon::today();
        $talentNames = TalentContent::whereDate('posting_date', $today)
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->pluck('talents.talent_name');

        return response()->json($talentNames);
    }

    public function calendar(): JsonResponse
    {
        $talentContents = TalentContent::join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->select('talent_content.id', 'talent_content.posting_date', 'talents.talent_name')
            ->get();
        $data = $talentContents->map(function ($content) {
            return [
                'id' => $content->id,
                'talent_name' => $content->talent_name, 
                'posting_date' => $content->posting_date ? (new \DateTime($content->posting_date))->format(DATE_ISO8601) : null, // Ensure ISO format
            ];
        });

        return response()->json(['data' => $data]);
    }
    public function countContent(): JsonResponse
    {
        $todayCount = TalentContent::whereDate('posting_date', today())->count();
        $doneFalseCount = TalentContent::where('done', false)->count();
        $doneTrueCount = TalentContent::where('done', true)->count();
        $totalCount = TalentContent::count();

        return response()->json([
            'today_count' => $todayCount,
            'done_false_count' => $doneFalseCount,
            'done_true_count' => $doneTrueCount,
            'total_count' => $totalCount,
        ]);
    }
}
