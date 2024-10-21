<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Models\Approval;
use App\Domain\Talent\Requests\TalentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use App\Domain\Talent\Import\TalentImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Talent\Exports\TalentTemplateExport;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;  

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
            ->addColumn('payment_action', function ($talent) {
                return '
                    <button class="btn btn-sm btn-info addPaymentButton" 
                        data-id="' . $talent->id . '"
                        data-toggle="modal" 
                        data-target="#addPaymentModal">
                        <i class="fas fa-wallet"> Payment</i>
                    </button>
                    <button class="btn btn-sm bg-purple exportData" 
                        data-id="' . $talent->id . '">
                        <i class="fas fa-file-invoice"> Invoice</i>
                    </button>
                    <button class="btn btn-sm bg-maroon exportSPK" 
                        data-id="' . $talent->id . '">
                        <i class="fas fa-file"> SPK</i>
                    </button>
                ';
            })
            ->rawColumns(['action', 'payment_action'])
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
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:talents,username',
            'talent_name' => 'required|string|max:255',
            'video_slot' => 'nullable|integer',
            'content_type' => 'nullable|string|max:255',
            'produk' => 'nullable|string|max:255',
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
            'nik' => 'nullable|string|max:255',
            'price_rate' => 'nullable|integer',
            'slot_final' => 'nullable|integer',
            'rate_final' => 'nullable|integer',
            'scope_of_work' => 'nullable|string|max:255',
            'masa_kerjasama' => 'nullable|string|max:255',
        ]);

        // Calculate discount
        $discount = 0;
        if ($validatedData['price_rate'] && $validatedData['slot_final'] && $validatedData['rate_final']) {
            $discount = ($validatedData['price_rate'] * $validatedData['slot_final']) - $validatedData['rate_final'];
        }

        // Calculate tax deduction
        $tax_rate = (strpos($validatedData['talent_name'], 'PT') === 0 || strpos($validatedData['talent_name'], 'CV') === 0) ? 0.02 : 0.025;
        $tax_deduction = $validatedData['rate_final'] ? $validatedData['rate_final'] * $tax_rate : 0;

        // Calculate final transfer
        $final_transfer = $validatedData['rate_final'] ? $validatedData['rate_final'] - $tax_deduction : 0;

        // Add calculated fields to the validated data
        $validatedData['discount'] = $discount;
        $validatedData['tax_deduction'] = $tax_deduction;
        $validatedData['final_transfer'] = $final_transfer;

        // Create a new Talent record
        Talent::create($validatedData);

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
        return response()->json($talent);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TalentRequest $request
     * @param  Talent  $talent
     */
    public function update(TalentRequest $request, Talent $talent)
    {
        try {
            DB::beginTransaction();

            // Prepare the data for update
            $data = $request->validated();

            // Handle money inputs (assuming they come in as formatted strings)
            $data['price_rate'] = (int) str_replace(['Rp', '.', ' '], '', $data['price_rate']);
            $data['rate_final'] = (int) str_replace(['Rp', '.', ' '], '', $data['rate_final']);

            // Calculate discount
            $data['discount'] = ($data['price_rate'] * $data['slot_final']) - $data['rate_final'];

            // Calculate tax deduction
            $taxRate = (strpos($data['talent_name'], 'PT') === 0 || strpos($data['talent_name'], 'CV') === 0) ? 0.02 : 0.025;
            $data['tax_deduction'] = (int) ($data['rate_final'] * $taxRate);

            // Update the talent
            $talent->update($data);

            DB::commit();

            return redirect()->route('talent.index')->with('success', 'Talent updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('talent.index')->with('error', 'Failed to update talent: ' . $e->getMessage());
        }
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

    public function exportInvoice(Request $request, $id)
    {
        $talent = Talent::findOrFail($id);
        $approvalId = $request->query('approval');

        if ($approvalId) {
            $approval = Approval::findOrFail($approvalId);
        }

        $harga = $talent->rate_final; 
        $isPTorCV = \Illuminate\Support\Str::startsWith($talent->nama_rekening, ['PT', 'CV']);
        if ($isPTorCV) {
            $pphPercentage = 2;
            $pphLabel = 'PPh 23 (2%)';
            $pph = $harga * 0.02;
        } else {
            $pphPercentage = 2.5;
            $pphLabel = 'PPh 21 (2.5%)';
            $pph = $harga * 0.025;
        }
        $total = $harga - $pph; 
        $downPayment = $total / 2; 
        $remainingBalance = $total - $downPayment;
        $ttd = $approval->photo;
        $approval_name = $approval->name;
        
        $data = [
            'nik' => $talent->nik,
            'nama_talent' => $talent->talent_name,
            'tanggal_hari_ini' => now()->format('d/m/Y'),
            'alamat_talent' => $talent->address,
            'no_hp_talent' => $talent->phone_number,
            'nama_akun' => $talent->username, 
            'quantity_slot' => $talent->video_slot,
            'deskripsi' => $talent->content_type,
            'harga' => $harga,
            'subtotal' => $harga,
            'pphLabel' => $pphLabel,
            'pphPercentage' => $pphPercentage,
            'pph' => $pph,
            'total' => $total,
            'down_payment' => $downPayment,
            'sisa' => $remainingBalance,
            'bank' => $talent->bank,
            'nama_account' => $talent->nama_rekening,
            'account_no' => $talent->no_rekening,
            'npwp' => $talent->no_npwp,
            'ttd' => $ttd,
            'approval_name' => $approval_name,
        ];

        $pdf = Pdf::loadView('admin.talent.invoice', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice.pdf');
    }
    public function showInvoice()
    {
        $talent = Talent::with('talent')->get();
        return view('admin.talent.invoice', compact('talent'));
    }
    public function exportSPK($id)
    {
        $talent = Talent::findOrFail($id);
        $tanggal_hari_ini = Carbon::now()->isoFormat('D MMMM YYYY');
        $pdf = PDF::loadView('admin.talent.mou', compact('talent', 'tanggal_hari_ini'));
        $pdf->setPaper('A4', 'potrait');
        return $pdf->download('SPK.pdf');
    }
    public function showSPK()
    {
        $talentContents = TalentContent::with('talent')->get();
        return view('admin.talent.mou', compact('talentContents'));
    }
}
