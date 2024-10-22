<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\TalentPayment\TalentPaymentBLLInterface;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Exports\TalentPaymentExport;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Models\TalentPayment;
use App\Domain\Talent\Requests\TalentPaymentRequest;
use Yajra\DataTables\Utilities\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;  
use Illuminate\Support\Facades\DB;
use Auth;

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
        $currentTenantId = Auth::user()->current_tenant_id;
        $payments = TalentPayment::select([
                'talent_payments.id',
                'talent_payments.done_payment',
                'talent_payments.amount_tf',
                'talent_payments.tanggal_pengajuan',
                'talents.pic',
                'talents.username',
                'talent_payments.status_payment',
                'talents.talent_name',
                'talents.followers'
            ])
            ->join('talents', 'talent_payments.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId);

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
            ->filterColumn('pic', function($query, $keyword) {
                $query->whereRaw("talents.pic like ?", ["%{$keyword}%"]);
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
                'talent_id' => 'required|integer',
                'status_payment' => 'nullable|string|max:255',
            ]);
            $validatedData['tanggal_pengajuan'] = Carbon::today();
            $validatedData['tenant_id'] = Auth::user()->current_tenant_id;
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
    public function update(Request $request, $id)
    {
        try {
            $payment = TalentPayment::findOrFail($id);

            $validatedData = $request->validate([
                'done_payment' => 'nullable|date',
            ]);
            $payment->update($validatedData);

            return redirect()->route('talent_payments.index')->with('success', 'Payment updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Payment update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update payment: ' . $e->getMessage());
        }
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

        // Determine the tax type for each talent
        $talentContents->each(function ($content) {
            $content->isPTorCV = \Illuminate\Support\Str::startsWith($content->talent->talent_name, ['PT', 'CV']);
        });

        // Generate PDF
        $pdf = PDF::loadView('admin.talent_payment.form_pengajuan', compact('talentContents'));
        $pdf->setPaper('A4', 'landscape');

        // Download the PDF
        return $pdf->download('form_pengajuan.pdf');
    }

    public function report()
    {
        $usernames = Talent::select('username')->distinct()->pluck('username');
        return view('admin.talent_payment.report', compact('usernames'));
    }

    public function getReportKPI()
    {
        $payments = TalentPayment::with('talent')->get();
        $totalRateFinal = 0;
        $totalSpent = 0;

        $result = $payments->map(function ($payment) use (&$totalRateFinal, &$totalSpent) {
            $rateFinal = $payment->talent ? $payment->talent->rate_final : null;
            if ($rateFinal !== null) {
                $rateFinal = $rateFinal - $payment->talent->tax_deduction;
                $totalRateFinal += $rateFinal;
            }
            if ($payment->done_payment !== null && $rateFinal !== null) {
                switch ($payment->status_payment) {
                    case 'Full Payment':
                        $totalSpent += $rateFinal;
                        break;
                    case 'DP 50%':
                    case 'Pelunasan 50%':
                        $totalSpent += $rateFinal * 0.5;
                        break;
                    case 'Termin 1':
                    case 'Termin 2':
                    case 'Termin 3':
                        $totalSpent += $rateFinal / 3;
                        break;
                    default:
                        $totalSpent += 0;
                        break;
                }
            }
        });

        return response()->json([
            'total_final_tf' => $totalRateFinal,
            'total_spent' => $totalSpent,
        ], 200);
    }
    public function getHutang(Request $request)
    {
        // Initialize totals
        $totalHutang = 0;
        $totalPiutang = 0;
        $totalSpent = 0;

        $query = Talent::with(['talentContents', 'talentPayments'])
        ->select('talents.*');

        if ($request->input('username')) {
            $query->where('username', $request->input('username'));
        }

        // Fetch all talents with their payments and content
        $talents = $query->get()->map(function($talent) use (&$totalHutang, &$totalPiutang, &$totalSpent) {
                $totalSpentForTalent = $talent->talentPayments->sum(function($payment) use ($talent) {
                    switch ($payment->status_payment) {
                        case 'Full Payment':
                            return $payment->done_payment ? $talent->rate_final * 1 : 0;
                        case 'DP 50%':
                        case 'Pelunasan 50%':
                            return $payment->done_payment ? $talent->rate_final * 0.5 : 0;
                        case 'Termin 1':
                        case 'Termin 2':
                        case 'Termin 3':
                            return $payment->done_payment ? $talent->rate_final / 3 : 0;
                        default:
                            return 0;
                    }
                });

                // Calculating talent_should_get based on the content count
                $contentCount = $talent->talentContents->count(); // Use the correct relationship
                $talentShouldGet = ($talent->slot_final > 0) ? ($talent->rate_final / $talent->slot_final) * $contentCount : 0;

                // Calculating hutang and piutang for the talent
                $hutang = $talentShouldGet > $totalSpentForTalent ? $talentShouldGet - $totalSpentForTalent : 0;
                $piutang = $talentShouldGet < $totalSpentForTalent ? $totalSpentForTalent - $talentShouldGet : 0;

                // Add to the global totals
                $totalSpent += $totalSpentForTalent;
                $totalHutang += $hutang;
                $totalPiutang += $piutang;

                // Return an object with all the calculations for this talent
                return (object) [
                    'talent_name'      => $talent->talent_name,
                    'username'      => $talent->username,
                    'total_spent'      => $totalSpentForTalent,
                    'talent_should_get'=> $talentShouldGet,
                    'hutang'           => $hutang,
                    'piutang'          => $piutang,
                ];
            });

        // Filter out talents where both total_spent and talent_should_get are 0
        $filteredTalents = $talents->filter(function($talent) {
            return $talent->total_spent != 0 || $talent->talent_should_get != 0;
        });

        // Return response with both the filtered talents and total values
        return response()->json([
            'talents' => $filteredTalents,
            'totals' => [
                'total_spent' => $totalSpent,
                'total_hutang' => $totalHutang,
                'total_piutang' => $totalPiutang,
            ]
        ]);
    }
    public function paymentReport(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $payments = TalentPayment::select([
                'talent_payments.id',
                'talent_payments.done_payment',
                'talent_payments.amount_tf',
                'talent_payments.tanggal_pengajuan',
                'talents.pic',
                'talents.username',
                'talent_payments.status_payment',
                'talents.talent_name',
                'talents.followers',
                'talents.rate_final',
            ])
            ->join('talents', 'talent_payments.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId);

            if ($request->input('username')) {
                $payments->where('talents.username', $request->input('username'));
            }

        return DataTables::of($payments)
            ->addColumn('spent', function ($payment) {
                $rateFinal = $payment->rate_final ?? 0; // Get the rate_final, default to 0 if null

                // Calculate spent based on the rules provided
                if ($payment->status_payment === 'Full Payment' && !is_null($payment->done_payment)) {
                    return $rateFinal * 1;
                } elseif (in_array($payment->status_payment, ['DP 50%', 'Pelunasan 50%']) && !is_null($payment->done_payment)) {
                    return $rateFinal * 0.5;
                } elseif (in_array($payment->status_payment, ['Termin 1', 'Termin 2', 'Termin 3']) && !is_null($payment->done_payment)) {
                    return $rateFinal / 3;
                } else {
                    return 0;
                }
            })
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
            ->filterColumn('pic', function($query, $keyword) {
                $query->whereRaw("talents.pic like ?", ["%{$keyword}%"]);
            })
            ->rawColumns(['action', 'spent'])
            ->make(true);
    }
    public function exportReport(){
        return Excel::download(new TalentPaymentExport, 'kol_payment_report.xlsx');
    }
}
