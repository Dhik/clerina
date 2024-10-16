<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Models\Approval;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Requests\TalentContentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;

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
        $uploadLinkButton = !empty($talentContent->upload_link) 
            ? '<a href="' . $talentContent->upload_link . '" target="_blank" class="btn btn-light" data-toggle="tooltip" data-placement="top" title="View Upload">
                <i class="fas fa-link"></i>
            </a>'
            : '<span class="text-black-50">No Link</span>';

        $viewButton = '<button class="btn btn-light viewButton" 
            data-id="' . $talentContent->id . '" 
            data-toggle="modal" 
            data-target="#viewTalentContentModal" 
            data-placement="top" title="View">
            <i class="fas fa-eye"></i>
        </button>';

        return $uploadLinkButton . ' ' . $viewButton;
    }

    protected function doneHtml($talentContent): string
    {
        $doneButton = $talentContent->done 
            ? '<button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Done">
                <i class="fas fa-check-circle text-success"></i>
            </button>'
            : '<button class="btn btn-light" data-toggle="tooltip" data-placement="top" title="Not Done">
                <i class="fas fa-times-circle text-secondary"></i>
            </button>';
        return $doneButton;
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
            'talents.username',
            'talent_content.final_rate_card'])
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id');

        return DataTables::of($talentContents)
            ->addColumn('status_and_link', function ($talentContent) {
                return $this->statusAndLinkHtml($talentContent);
            })
            ->addColumn('done', function ($talentContent) {
                return $this->doneHtml($talentContent);
            })
            ->addColumn('action', function ($talentContent) {
                return '
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-pencil-alt"> Add link</i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-trash-alt"> Delete</i>
                    </button>
                ';
            })
            ->rawColumns(['action', 'status_and_link', 'done'])
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
        $validatedData = $request->validate([
            'talent_id' => 'required|integer',
            'dealing_upload_date' => 'nullable|date',
            'pic_code' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
            'kerkun' => 'required|boolean',
        ]);
        $validatedData['done'] = 0;
        $validatedData['upload_link'] = null;
        $validatedData['boost_code'] = null;

        // Create a new TalentContent record
        TalentContent::create($validatedData);

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
        if (!$talentContent) {
            return response()->json(['message' => 'Talent content not found'], 404);
        }
        return response()->json([
            'talentContent' => [
                'id' => $talentContent->id,
                'talent_name' => $talentContent->talent_name,
                'dealing_upload_date' => $talentContent->dealing_upload_date,
                'posting_date' => $talentContent->posting_date,
                'final_rate_card' => $talentContent->final_rate_card,
                'done' => $talentContent->done,
                'upload_link' => $talentContent->upload_link,
                'pic_code' => $talentContent->pic_code,
                'boost_code' => $talentContent->boost_code,
                'kerkun' => $talentContent->kerkun,
            ],
        ]);
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
            'dealing_upload_date' => 'nullable|date',
            'posting_date' => 'nullable|date',
            'done' => 'required|boolean',
            'upload_link' => 'nullable|url',
            'final_rate_card' => 'nullable|max:255',
            'pic_code' => 'nullable|string|max:255',
            'boost_code' => 'nullable|string|max:255',
            'kerkun' => 'required|boolean',
        ]);

        $validated['transfer_date'] = $request->dealing_upload_date;

        // Set posting_date based on upload_link
        if (!empty($validated['upload_link'])) {
            $validated['posting_date'] = Carbon::today()->toDateString();
        } else {
            $validated['posting_date'] = null;
        }

        $talentContent = TalentContent::find($id);
        if (!$talentContent) {
            return redirect()->route('talent_content.index')->with('error', 'Talent content not found.');
        }

        try {
            DB::beginTransaction();

            $talentContent->update($validated);

            DB::commit();

            return redirect()->route('talent_content.index')->with('success', 'Talent content updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('talent_content.index')->with('error', 'Failed to update talent content. ' . $e->getMessage());
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
        $talents = Talent::select('id', 'username')->get();
        return response()->json($talents);
    }
    public function getTodayTalentNames()
    {
        $today = Carbon::today();
        $talentNames = TalentContent::whereDate('dealing_upload_date', $today)
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->pluck('talents.talent_name');

        return response()->json($talentNames);
    }

    public function calendar(): JsonResponse
    {
        $talentContents = TalentContent::join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->select('talent_content.id', 'talent_content.dealing_upload_date', 'talents.username')
            ->get();
        $data = $talentContents->map(function ($content) {
            return [
                'id' => $content->id,
                'talent_name' => $content->username, 
                'posting_date' => $content->dealing_upload_date ? (new \DateTime($content->dealing_upload_date))->format(DATE_ISO8601) : null, // Ensure ISO format
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