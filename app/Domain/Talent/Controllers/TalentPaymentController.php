<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\TalentPayment\TalentPaymentBLLInterface;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Models\TalentPayment;
use App\Domain\Talent\Requests\TalentPaymentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;
use App\Domain\Talent\Models\Talent;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 */
class TalentPaymentController extends Controller
{
    public function __construct(TalentBLLInterface $talentPaymentsBLL)
    {
        $this->talentPaymentsBLL = $talentPaymentsBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        // Fetch unique PIC and Username values
        $uniquePics = Talent::select('pic')->distinct()->pluck('pic');
        $uniqueUsernames = Talent::select('username')->distinct()->pluck('username');

        return view('admin.talent_payment.index', compact('uniquePics', 'uniqueUsernames'));
    }

    public function data(Request $request)
    {
        $payments = TalentPayment::select([
                'talent_payments.id',
                'talent_payments.done_payment',
                'talent_payments.amount_tf',
                'talents.pic',
                'talents.username',
                'talent_payments.status_payment',
                'talents.talent_name',
                'talents.followers'
            ])
            ->join('talents', 'talent_payments.talent_id', '=', 'talents.id');

        // Apply filters if provided
        if ($request->has('pic') && $request->pic != '') {
            $payments->where('talents.pic', $request->pic);
        }

        if ($request->has('username') && $request->username != '') {
            $payments->where('talents.username', $request->username);
        }

        return DataTables::of($payments)
            ->addColumn('action', function ($payment) {
                return '
                    <button class="btn btn-sm btn-primary viewButton" 
                        data-id="' . $payment->id . '" 
                        data-toggle="modal" 
                        data-target="#viewPaymentModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $payment->id . '">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" 
                        data-id="' . $payment->id . '">
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

    /**
     * Store a newly created resource in storage.
     *
     * @param TalentPaymentsRequest $request
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'done_payment' => 'nullable|date',
                'talent_id' => 'required|integer',
                'amount_tf' => 'nullable',
                'status_payment' => 'nullable|string|max:255',
            ]);
            $payment = TalentPayment::create($validatedData);
            return redirect()->route('talent.index')->with('success', 'Talent payment created successfully.');
        } catch (\Exception $e) {
            \Log::error('Talent payment creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create talent payment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param TalentPayments $payment
     */
    public function show(TalentPayments $payment)
    {
        return response()->json($payment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TalentPayments $payment
     */
    public function edit(TalentPayments $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TalentPaymentsRequest $request
     * @param TalentPayments $payment
     */
    public function update(TalentPaymentsRequest $request, TalentPayments $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param TalentPayments $payment
     */
    public function destroy($id)
    {
        $payment = TalentPayment::findOrFail($id);
        $payment->delete();
        return response()->json(['success' => true]);
    }

    public function exportPengajuan(Request $request)
    {
        // Fetch the talent contents based on filters
        $query = TalentPayment::with('talent');

        // Apply filters if provided
        if ($request->has('pic') && $request->pic != '') {
            $query->whereHas('talent', function($q) use ($request) {
                $q->where('pic', $request->pic);
            });
        }

        if ($request->has('username') && $request->username != '') {
            $query->whereHas('talent', function($q) use ($request) {
                $q->where('username', $request->username);
            });
        }

        $talentContents = $query->get();

        // Generate PDF
        $pdf = PDF::loadView('admin.talent_payment.form_pengajuan', compact('talentContents'));
        $pdf->setPaper('A4', 'landscape');

        // Download the PDF
        return $pdf->download('form_pengajuan.pdf');
    }
}
