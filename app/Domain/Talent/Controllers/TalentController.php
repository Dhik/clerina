<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Requests\TalentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use App\Domain\Talent\Import\TalentImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Talent\Exports\TalentTemplateExport;
/**
 * @property TalentBLLInterface talentBLL
 */
class TalentController extends Controller
{
    public function __construct(TalentBLLInterface $talentBLL)
    {
        $this->talentBLL = $talentBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.talent.index');
    }

    public function data(Request $request)
    {
        $talents = Talent::select(['id', 'username', 'talent_name', 'pengajuan_transfer_date', 'rate_final']);
    
        return DataTables::of($talents)
            ->addColumn('action', function ($talent) {
                return '
                    <button class="btn btn-sm btn-primary viewButton" 
                        data-id="' . $talent->id . '" 
                        data-toggle="modal" 
                        data-target="#viewTalentModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $talent->id . '">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" 
                        data-id="' . $talent->id . '">
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
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'username' => 'required|string|max:255',
            'talent_name' => 'required|string|max:255',
            'video_slot' => 'nullable|integer',
            'content_type' => 'nullable|string|max:255',
            'produk' => 'nullable|string|max:255',
            'rate_final' => 'nullable|integer',
            'pic' => 'nullable|string|max:255',
            'bulan_running' => 'nullable|string|max:255',
            'niche' => 'nullable|string|max:255',
            'followers' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:255',
            'no_rekening' => 'nullable|string|max:255',
            'nama_rekening' => 'nullable|string|max:255',
            'no_npwp' => 'nullable|string|max:255',
            'pengajuan_transfer_date' => 'nullable|date',
            'gdrive_ttd_kol_accepting' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
            'price_rate' => 'nullable|integer',
            'first_rate_card' => 'nullable|integer',
            'discount' => 'nullable|integer',
            'slot_final' => 'nullable|integer',
            'tax_deduction' => 'nullable|integer',
        ]);

        // Create a new Talent record
        Talent::create($request->all());

        // Redirect back to the talents index page with a success message
        return redirect()->route('talent.index')->with('success', 'Talent created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param Talent $talent
     */
    public function show(Talent $talent)
    {
        return response()->json($talent);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Talent  $talent
     */
    public function edit(Talent $talent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TalentRequest $request
     * @param  Talent  $talent
     */
    public function update(TalentRequest $request, Talent $talent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Talent $talent
     */
    public function destroy($id)
    {
        $talent = Talent::findOrFail($id);
        $talent->delete();
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        // Validate the incoming request for the file
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);

        // Perform the import using the TalentImport class
        Excel::import(new TalentImport, $request->file('file'));

        return redirect()->back()->with('success', 'Talent data imported successfully.');
    }
}
