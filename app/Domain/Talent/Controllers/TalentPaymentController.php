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
use \Illuminate\Support\Str;
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
            'talents.nama_rekening',
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

        if ($request->has('done_payment') && $request->done_payment != '') {
            $payments->whereDate('talent_payments.done_payment', $request->done_payment);
        }
        
        if ($request->has('tanggal_pengajuan') && $request->tanggal_pengajuan != '') {
            $payments->whereDate('talent_payments.tanggal_pengajuan', $request->tanggal_pengajuan);
        }        

        if ($request->has('username') && is_array($request->username)) {
            $payments->whereIn('talents.username', $request->username);
        }

        if ($request->has('status_payment') && $request->status_payment != '') {
            $payments->where('talent_payments.status_payment', $request->status_payment);
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
            ->filterColumn('pic', function ($query, $keyword) {
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
            $talent = Talent::findOrFail($validatedData['talent_id']);
            $rate_card_per_slot = $talent->price_rate;
            $slot = $talent->slot_final;
            $rate_harga = $rate_card_per_slot * $slot;
            $discount = $talent->discount;
            $harga_setelah_diskon = $rate_harga - $discount;

            if (!is_null($talent->tax_percentage) && $talent->tax_percentage > 0) {
                $pphPercentage = $talent->tax_percentage / 100;
            } else {
                $pphPercentage = $talent->isPTorCV ? 0.02 : 0.025;
            }
            $pphAmount = $harga_setelah_diskon * $pphPercentage;
            $final_tf = $harga_setelah_diskon - $pphAmount;

            if ($talent->dp_amount == 0) {
                $amount_tf = 0;
            } else {
                $amount_tf = $final_tf - $talent->dp_amount;
            }
            $validatedData['tanggal_pengajuan'] = Carbon::today();
            $validatedData['tenant_id'] = Auth::user()->current_tenant_id;
            $validatedData['amount_tf'] = $amount_tf;
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
    public function show(TalentPayment $payment)
    {
        return response()->json($payment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param TalentPayment $payment
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

            // Validate input data
            $validatedData = $request->validate([
                'done_payment' => 'nullable|date',
            ]);
            $payment->update($validatedData);

            if ($payment->done_payment !== null) {
                $talent = $payment->talent;

                $rate_harga = $talent->price_rate * $talent->slot_final;
                $harga_setelah_diskon = $rate_harga - $talent->discount;

                $pphPercentage = (Str::startsWith($talent->nama_rekening, ['PT', 'CV'])) ? 0.02 : 0.025;
                $pphAmount = $harga_setelah_diskon * $pphPercentage;
                $final_tf = $harga_setelah_diskon - $pphAmount;

                $paymentStatuses = ['Termin 1', 'Termin 2', 'Termin 3', 'DP 50%', 'Pelunasan 50%'];

                $relevantPaymentsCount = TalentPayment::where('talent_id', $talent->id)
                    ->whereIn('status_payment', $paymentStatuses)
                    ->count();

                if ($relevantPaymentsCount > 0) {
                    if (in_array($payment->status_payment, ['Termin 1', 'Termin 2', 'Termin 3'])) {
                        $talent->dp_amount = ($final_tf / 3) * $relevantPaymentsCount;
                    } elseif (in_array($payment->status_payment, ["DP 50%", "Pelunasan 50%"])) {
                        $talent->dp_amount = ($final_tf / 2) * $relevantPaymentsCount;
                    } elseif ($payment->status_payment === "Full Payment") {
                        $talent->dp_amount = $final_tf;
                    }
                }
                $talent->save();
            }
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
        $query = TalentPayment::with('talent');
        $query->whereHas('talent', function ($q) {
            $q->where('tenant_id', Auth::user()->current_tenant_id);
        });

        if ($request->has('pic') && $request->pic != '') {
            $query->whereHas('talent', function ($q) use ($request) {
                $q->where('pic', $request->pic);
            });
        }

        if ($request->has('username') && is_array($request->username)) {
            $usernames = is_array($request->username) ? $request->username : json_decode($request->username, true);

            $query->whereHas('talent', function ($q) use ($usernames) {
                $q->whereIn('username', $usernames);
            });
        }

        if ($request->has('status_payment') && $request->status_payment != '') {
            $query->where('status_payment', $request->status_payment);
        }

        if ($request->has('done_payment') && $request->done_payment != '') {
            $query->whereDate('done_payment', $request->done_payment);
        }
        
        if ($request->has('tanggal_pengajuan') && $request->tanggal_pengajuan != '') {
            $query->whereDate('tanggal_pengajuan', $request->tanggal_pengajuan);
        }   

        $talentContents = $query->get();
        $talentContents->each(function ($content) {
            $content->isPTorCV = \Illuminate\Support\Str::startsWith($content->talent->nama_rekening, ['PT', 'CV']);
        });

        $pdf = PDF::loadView('admin.talent_payment.form_pengajuan', compact('talentContents'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('form_pengajuan.pdf');
    }


    public function exportPengajuanExcel(Request $request)
    {
        try {
            ini_set('memory_limit', '512M');
            $tenantId = Auth::user()->current_tenant_id;

            $export = new TalentPaymentExport($request, $tenantId);
            $data = $export->query()->get();
            $mappedData = $data->map(function ($payment) use ($export) {
                return $export->map($payment);
            })->filter(function ($item) {
                return !empty($item);
            });
            return Excel::download(new TalentPaymentExport($request, $tenantId, $mappedData), 'form_pengajuan.xlsx');
        } catch (\Exception $e) {
            return back()->with('error', 'Export failed. Please try again.');
        }
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
    public function getHutangDatatable(Request $request)
    {
        $startDate = null;
        $endDate = null;

        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->input('dateRange'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
        }

        $query = Talent::with(['talentContents', 'talentPayments' => function ($q) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $q->whereBetween('done_payment', [$startDate, $endDate]);
            }
        }])->select('talents.*');

        if ($request->input('username')) {
            $query->where('username', $request->input('username'));
        }

        $talents = $query->get()->map(function ($talent) use ($startDate, $endDate) {
            $totalSpentForeachTalent = $this->calculateSpentForeachTalent($talent);
            $totalSpentForeachTalent = $this->adjustSpentForTax($totalSpentForeachTalent, $talent->nama_rekening);
            $totalSpentForTalent = $this->calculateSpentForTalent($talent, $startDate, $endDate);
            $totalSpentForTalent = $this->adjustSpentForTax($totalSpentForTalent, $talent->nama_rekening);
            if ($startDate && $endDate) {
                $filteredTalentContents = $talent->talentContents->filter(function ($content) use ($startDate, $endDate) {
                    return $content->posting_date >= $startDate && $content->posting_date <= $endDate;
                });
                $contentCount = $filteredTalentContents->count();
            } else {
                $contentCount = $talent->talentContents->count();
            }

            $totalPerSlot = ($talent->slot_final > 0)
                ? $talent->rate_final / $talent->slot_final
                : 0;

            $totalPerSlot = $this->adjustSpentForTax($totalPerSlot, $talent->nama_rekening);

            $talentShouldGet = ($talent->slot_final > 0)
                ? ($totalPerSlot) * $contentCount
                : 0;

            $hutang = $talentShouldGet > $totalSpentForTalent ? $talentShouldGet - $totalSpentForTalent : 0;
            $piutang = $talentShouldGet < $totalSpentForTalent ? $totalSpentForTalent - $talentShouldGet : 0;

            return (object) [
                'talent_name' => $talent->talent_name,
                'username' => $talent->username,
                'total_spent' => $totalSpentForeachTalent,
                'talent_should_get' => $talentShouldGet,
                'hutang' => $hutang,
                'piutang' => $piutang,
            ];
        });

        $filteredTalents = $talents->filter(function ($talent) {
            return $talent->total_spent != 0 || $talent->talent_should_get != 0;
        });
        return DataTables::of($filteredTalents)->make(true);
    }

    private function adjustSpentForTax($spent, $accountName)
    {
        $isPTorCV = \Illuminate\Support\Str::startsWith($accountName, ['PT', 'CV']);
        $pph = $isPTorCV ? $spent * 0.02 : $spent * 0.025;
        return $spent - $pph;
    }

    public function calculateTotals(Request $request)
    {
        $hutangDatatable = $this->getHutangDatatable($request);
        $datatableCollection = collect($hutangDatatable->getData()->data);
        $totalHutang = $datatableCollection->sum('hutang');
        $totalPiutang = $datatableCollection->sum('piutang');
        $totalSpent = $datatableCollection->sum('total_spent');

        return response()->json([
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

        $baseQuery = TalentPayment::join('talents', 'talent_payments.talent_id', '=', 'talents.id')
            ->where('talents.tenant_id', $currentTenantId);

        if ($request->input('username')) {
            $baseQuery->where('talents.username', $request->input('username'));
        }

        if ($request->filled('dateRange')) {
            $dates = explode(' - ', $request->input('dateRange'));
            $startDate = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();

            $baseQuery->whereBetween('talent_payments.done_payment', [$startDate, $endDate]);
        }

        $payments = $baseQuery->select([
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
        ]);

        return DataTables::of($payments)
            ->addColumn('spent', function ($payment) {
                $rateFinal = $payment->rate_final ?? 0;
                $netRateFinal = $this->adjustSpentForTax($rateFinal, $payment->nama_rekening);

                if ($payment->status_payment === 'Full Payment' && !is_null($payment->done_payment)) {
                    return $netRateFinal * 1;
                } elseif (in_array($payment->status_payment, ['DP 50%', 'Pelunasan 50%']) && !is_null($payment->done_payment)) {
                    return $netRateFinal * 0.5;
                } elseif (in_array($payment->status_payment, ['Termin 1', 'Termin 2', 'Termin 3']) && !is_null($payment->done_payment)) {
                    return $netRateFinal / 3;
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
            ->filterColumn('pic', function ($query, $keyword) {
                $query->whereRaw("talents.pic like ?", ["%{$keyword}%"]);
            })
            ->rawColumns(['action', 'spent'])
            ->make(true);
    }

    protected function calculateSpentForeachTalent($talent)
    {
        return $talent->talentPayments->sum(function ($payment) use ($talent) {
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
    }

    protected function calculateSpentForTalent($talent, $startDate = null, $endDate = null)
    {
        return $talent->talentPayments->sum(function ($payment) use ($talent, $startDate, $endDate) {
            if (!$payment->done_payment) {
                return 0;
            }

            $multiplier = match ($payment->status_payment) {
                'Full Payment' => 1,
                'DP 50%' => 0.5,
                'Pelunasan 50%' => $this->isMissingDP50($talent, $startDate, $endDate) ? 1 : 0.5,
                'Termin 1', 'Termin 2', 'Termin 3' => 1 / 3,
                default => 0,
            };

            return $talent->rate_final * $multiplier;
        });
    }
    protected function isMissingDP50($talent, $startDate, $endDate)
    {
        return !$talent->talentPayments->contains(function ($payment) use ($startDate, $endDate) {
            return $payment->status_payment === 'DP 50%' &&
                (!$startDate || $payment->done_payment >= $startDate) &&
                (!$endDate || $payment->done_payment <= $endDate);
        });
    }
    public function exportReport()
    {
        return Excel::download(new TalentPaymentExport, 'kol_payment_report.xlsx');
    }
}
