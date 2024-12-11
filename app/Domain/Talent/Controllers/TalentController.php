<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Models\TalentPayment;
use App\Domain\Talent\Models\Approval;
use App\Domain\Talent\Requests\TalentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Talent\Exports\TalentTemplateExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Auth;

/**
 * @property TalentBLLInterface talentBLL
 */
class TalentController extends Controller
{
    protected $talentBLL;
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
        $talents = $this->talentBLL->getAllTalentsWithContent();

        return DataTables::of($talents)
            ->addColumn('content_count', function ($talent) {
                return $talent->content_count;
            })
            ->addColumn('remaining', function ($talent) {
                return $talent->remaining;
            })
            ->addColumn('action', function ($talent) {
                return $this->generateActionButtons($talent);
            })
            ->addColumn('payment_action', function ($talent) {
                return $this->generatePaymentButtons($talent);
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
        $tenant_id = Auth::user()->current_tenant_id;

        // Modify the validation rule to check both username and tenant_id
        $validatedData = $request->validate([
            'username' => "required|string|max:255|unique:talents,username,NULL,id,tenant_id,$tenant_id", // Custom validation rule
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
            'platform' => 'nullable|string|max:255',
        ]);

        // Automatically add tenant_id based on Auth::user()->current_tenant_id
        $validatedData['tenant_id'] = $tenant_id;
        $validatedData['tax_percentage'] = 0;

        // Check if username already exists for this tenant
        $existingTalent = Talent::where('tenant_id', $tenant_id)
            ->where('username', $validatedData['username'])
            ->first();

        // If the username already exists, return an error
        if ($existingTalent) {
            return back()->withErrors(['username' => 'This username is already taken by another talent in your tenant.'])->withInput();
        }

        // Proceed to create the talent if validation passes
        $this->talentBLL->createTalent($validatedData);

        return redirect()->route('talent.index')->with('success', 'Talent created successfully.');
    }



    /**
     * Display the specified resource.
     *
     * @param Talent $talent
     */
    public function show(Talent $talent)
    {
        $discount = $talent->price_rate * $talent->slot_final - $talent->rate_final;
        return response()->json([
            'talent' => $talent,
            'discount' => $discount,
        ]);
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
            $this->talentBLL->updateTalent($talent, $request->validated());
            return redirect()->route('talent.index')->with('success', 'Talent updated successfully');
        } catch (\Exception $e) {
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
        $this->talentBLL->deleteTalent($id);
        return response()->json(['success' => true]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv,xls'
        ]);
        $this->talentBLL->handleTalentImport($request->file('file'));
        return redirect()->back()->with('success', 'Talent data imported successfully.');
    }

    private function generateActionButtons($talent)
    {
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
    }

    private function generatePaymentButtons($talent)
    {
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
    }

    public function exportInvoice(Request $request, $id)
    {
        $talent = Talent::findOrFail($id);
        $approvalId = $request->query('approval');

        if ($approvalId) {
            $approval = Approval::findOrFail($approvalId);
        }

        $harga = $talent->rate_final;
        $slot_final = $talent->slot_final;
        if (!is_null($talent->tax_percentage) && $talent->tax_percentage > 0) {
            $pphPercentage = $talent->tax_percentage;
            $pphLabel = 'Custom Tax (' . $pphPercentage . '%)';
            $pph = $harga * ($pphPercentage / 100);
        } else {
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
        }
        $total = $harga - $pph;
        $downPayment = $talent->dp_amount ?? ($total / 2);
        $remainingBalance = $total - $downPayment;

        $latestPayment = TalentPayment::where('talent_id', $talent->id)
            ->latest()
            ->first();

        $statusPayment = $latestPayment ? $latestPayment->status_payment : null;

        $ttd = $approval->photo;
        $approval_name = $approval->name;

        $data = [
            'nik' => $talent->nik,
            'nama_talent' => $talent->talent_name,
            'tanggal_hari_ini' => now()->format('d/m/Y'),
            'alamat_talent' => $talent->address,
            'no_hp_talent' => $talent->phone_number,
            'nama_akun' => $talent->username,
            'quantity_slot' => $talent->slot_final,
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
            'status_payment' => $statusPayment,
            'tenant_id' => Auth::user()->current_tenant_id,
        ];

        $pdf = Pdf::loadView('admin.talent.invoice', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice.pdf');
    }
    public function exportInvData(Request $request, $id)
    {
        $talent = Talent::findOrFail($id);

        $harga = $talent->rate_final;
        $slot_final = $talent->slot_final;
        if (!is_null($talent->tax_percentage) && $talent->tax_percentage > 0) {
            $pphPercentage = $talent->tax_percentage;
            $pphLabel = 'Custom Tax (' . $pphPercentage . '%)';
            $pph = $harga * ($pphPercentage / 100);
        } else {
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
        }
        $total = $harga - $pph;
        $downPayment = $talent->dp_amount ?? ($total / 2);
        $remainingBalance = $total - $downPayment;

        $latestPayment = TalentPayment::where('talent_id', $talent->id)
            ->latest()
            ->first();

        $statusPayment = $latestPayment ? $latestPayment->status_payment : null;

        $data = [
            'nik' => $talent->nik,
            'nama_talent' => $talent->talent_name,
            'tanggal_hari_ini' => now()->format('d/m/Y'),
            'alamat_talent' => $talent->address,
            'no_hp_talent' => $talent->phone_number,
            'nama_akun' => $talent->username,
            'quantity_slot' => $talent->slot_final,
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
            'status_payment' => $statusPayment,
        ];
        return response()->json($data);
    }
    public function showInvoice()
    {
        $talent = Talent::with('talent')->get();
        return view('admin.talent.invoice', compact('talent'));
    }
    public function exportSPK($id)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $talent = Talent::findOrFail($id);
        $tanggal_hari_ini = Carbon::now()->isoFormat('D MMMM YYYY');
        $harga = $talent->rate_final;
        $isPTorCV = \Illuminate\Support\Str::startsWith($talent->nama_rekening, ['PT', 'CV']);
        if ($isPTorCV) {
            $pph = $harga * 0.02;
        } else {
            $pph = $harga * 0.025;
        }
        $total = $harga - $pph;
        if ($talent->tenant_id == 1) {
            $pdf = PDF::loadView('admin.talent.mou_cleora', compact('talent', 'tanggal_hari_ini', 'total'));
        } else {
            $pdf = PDF::loadView('admin.talent.mou_azrina', compact('talent', 'tanggal_hari_ini', 'total'));
        }
        $pdf->setPaper('A4', 'potrait');
        return $pdf->download('SPK.pdf');
    }
    public function showSPK()
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $talent = Talent::findOrFail(516);
        $tanggal_hari_ini = Carbon::now()->isoFormat('D MMMM YYYY');
        $harga = $talent->rate_final;
        $isPTorCV = \Illuminate\Support\Str::startsWith($talent->nama_rekening, ['PT', 'CV']);
        if ($isPTorCV) {
            $pph = $harga * 0.02;
        } else {
            $pph = $harga * 0.025;
        }
        $total = $harga - $pph;
        return view('admin.talent.mou_azrina', compact('talent', 'tanggal_hari_ini', 'total'));
    }
}
