<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\TalentPayment\TalentPaymentBLLInterface;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\TalentPayment;
use App\Domain\Talent\Requests\TalentPaymentRequest;
use Yajra\DataTables\Utilities\Request;
use Yajra\DataTables\DataTables;

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
        return view('admin.talent_payment.index');
    }

    public function data(Request $request)
    {
        $payments = TalentPayment::select([
                'talent_payments.id',
                'talent_payments.done_payment',
                'talent_payments.status_payment',
                'talents.talent_name',
                'talents.followers',
                'talent_content.final_rate_card'
            ])
            ->join('talents', 'talent_payments.talent_id', '=', 'talents.id')
            ->join('talent_content', 'talent_payments.talent_content_id', '=', 'talent_content.id');

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
        $validatedData = $request->validate([
            'done_payment' => 'nullable|date',
            'talent_id' => 'required|integer',
            'amount_tf' => 'nullable',
            'talent_content_id' => 'nullable|integer',
            'status_payment' => 'nullable|string|max:255',
        ]);

        $payment = TalentPayment::create($validatedData);
        return redirect()->route('talent_content.index')->with('success', 'Talent payment created successfully.');
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
        $payment = TalentPayments::findOrFail($id);
        $payment->delete();
        return response()->json(['success' => true]);
    }
}
