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
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

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
            'talents.talent_name',
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
            ->addColumn('payment_action', function ($talentContent) {
                return '
                    <button class="btn btn-sm btn-info addPaymentButton" 
                        data-id="' . $talentContent->id . '"
                        data-talent_id="' . $talentContent->talent_id . '"
                        data-toggle="modal" 
                        data-target="#addPaymentModal">
                        <i class="fas fa-wallet"> Payment</i>
                    </button>
                    <button class="btn btn-sm bg-purple exportData" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-file-invoice"> Invoice</i>
                    </button>
                    <button class="btn btn-sm bg-maroon exportSPK" 
                        data-id="' . $talentContent->id . '">
                        <i class="fas fa-file"> SPK</i>
                    </button>
                ';
            })
            ->rawColumns(['action', 'status_and_link', 'done', 'payment_action'])
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

        $talentContent = TalentContent::find($id);
        if (!$talentContent) {
            return redirect()->route('talent_content.index')->with('error', 'Talent content not found.');
        }

        try {
            $talentContent->update($validated);

            return redirect()->route('talent_content.index')->with('success', 'Talent content updated successfully.');
        } catch (\Exception $e) {
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
        $talentNames = TalentContent::whereDate('posting_date', $today)
            ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->pluck('talents.talent_name');

        return response()->json($talentNames);
    }

    public function calendar(): JsonResponse
    {
        $talentContents = TalentContent::join('talents', 'talent_content.talent_id', '=', 'talents.id')
            ->select('talent_content.id', 'talent_content.posting_date', 'talents.username')
            ->get();
        $data = $talentContents->map(function ($content) {
            return [
                'id' => $content->id,
                'talent_name' => $content->username, 
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

    public function exportPDF($id)
    {
        $talentContent = TalentContent::with('talent')->findOrFail($id);

        $harga = $talentContent->final_rate_card; 
        $pph21 = $harga * 0.025; 
        $total = $harga - $pph21; 
        $downPayment = $total / 2; 
        $remainingBalance = $total - $downPayment; 
        
        $data = [
            'nik' => $talentContent->talent->nik,
            'nama_talent' => $talentContent->talent->talent_name,
            'tanggal_hari_ini' => now()->format('d/m/Y'),
            'alamat_talent' => $talentContent->talent->address,
            'no_hp_talent' => $talentContent->talent->phone_number,
            'nama_akun' => $talentContent->talent->username, 
            'quantity_slot' => $talentContent->talent->video_slot,
            'deskripsi' => $talentContent->talent->content_type,
            'harga' => $harga,
            'subtotal' => $harga,
            'pph21' => $pph21,
            'total' => $total,
            'down_payment' => $downPayment,
            'sisa' => $remainingBalance,
            'bank' => $talentContent->talent->bank,
            'nama_account' => $talentContent->talent->nama_rekening,
            'account_no' => $talentContent->talent->no_rekening,
            'npwp' => $talentContent->talent->no_npwp,
        ];

        $pdf = Pdf::loadView('admin.talent_content.invoice', $data);
        $pdf->setPaper('A4', 'portrait');
        return $pdf->download('invoice.pdf');
    }
    public function showInvoice()
    {
        $talentContents = TalentContent::with('talent')->get();
        return view('admin.talent_content.invoice', compact('talentContents'));
    }
    public function exportPengajuan()
    {
        $talentContents = TalentContent::with('talent')->get();
        $pdf = PDF::loadView('admin.talent_content.form_pengajuan', compact('talentContents'));
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('form_pengajuan.pdf');
    }
    public function generateDocx()
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add header and company info
        $section->addText(
            'PT Summer Cantika Indonesia',
            array('name' => 'Arial', 'size' => 16, 'bold' => true)
        );
        $section->addText(
            'Ruko Garden City No. 05, Jl. Ciganitri, Ds. Cipagalo, Kec. Bojongsoang, Kab. Bandung,',
            array('name' => 'Arial', 'size' => 12)
        );
        $section->addText(
            'Prov. Jawa Barat, Kode Pos 40287',
            array('name' => 'Arial', 'size' => 12)
        );
        $section->addText(
            'clerinawijayaindonesia@gmail.com | 085172352324',
            array('name' => 'Arial', 'size' => 12)
        );

        // Add a line break
        $section->addTextBreak(1);

        // Add heading for the agreement
        $section->addText(
            'PERJANJIAN KERJASAMA',
            array('name' => 'Arial', 'size' => 14, 'bold' => true, 'underline' => 'single'),
            array('alignment' => 'center')
        );

        // Add parties information table
        $table = $section->addTable();
        $table->addRow();
        $table->addCell(2000)->addText("Pihak Pertama");
        $table->addCell(8000)->addText(" ");

        $table->addRow();
        $table->addCell(2000)->addText("Nama Perusahaan");
        $table->addCell(8000)->addText("PT Summer Cantika Indonesia");

        $table->addRow();
        $table->addCell(2000)->addText("Alamat");
        $table->addCell(8000)->addText("Ruko Garden City No 05, (Ruko Warna Pink) Cipagalo, Bojongsoang, Bandung");

        // Pihak Kedua
        $section->addTextBreak(1);
        $section->addText(
            'Pihak Kedua',
            array('name' => 'Arial', 'size' => 14, 'bold' => true)
        );
        
        $section->addText("Nama KOL: Nirfana Okta Via Alhusna", array('name' => 'Arial', 'size' => 12));
        $section->addText("Alamat: Jawa Timur, Kabupaten Malang, Kecamatan Wajak, Jalan Semeru gang 2 RT 001, RW 012", array('name' => 'Arial', 'size' => 12));
        $section->addText("No. Telepon: 085745531853", array('name' => 'Arial', 'size' => 12));

        // Agreement content
        $section->addTextBreak(1);
        $section->addText(
            'Berdasarkan prinsip kesetaraan dan saling menguntungkan...',
            array('name' => 'Arial', 'size' => 12)
        );

        // Add table for TikTok account and followers
        $section->addTextBreak(1);
        $table = $section->addTable();
        $table->addRow();
        $table->addCell(2000)->addText("No.");
        $table->addCell(5000)->addText("Nama Akun TikTok");
        $table->addCell(3000)->addText("Jumlah Followers");

        $table->addRow();
        $table->addCell(2000)->addText("1.");
        $table->addCell(5000)->addText("@kelayxzy");
        $table->addCell(3000)->addText("2.650");

        // Add contract content
        $section->addTextBreak(1);
        $section->addText(
            'Isi',
            array('name' => 'Arial', 'size' => 12, 'bold' => true)
        );
        $section->addText(
            'Pihak Pertama dengan ini telah mengonfirmasi saudari Nirfana Okta Via Alhusna
(untuk selanjutnya disebut Pihak Kedua) setelah bernegosiasi dan bersepakat bersama,
dan menunjuk Pihak Kedua sebagai yang mempromosikan video endorsement dari
Pihak Pertama.',
            array('name' => 'Arial', 'size' => 12)
        );

        $section->addText(
            'Hak dan Kewajiban',
            array('name' => 'Arial', 'size' => 12, 'bold' => true)
        );
        $section->addText(
            'Pihak Pertama dengan ini telah mengonfirmasi saudari Nirfana Okta Via Alhusna
(untuk selanjutnya disebut Pihak Kedua) setelah bernegosiasi dan bersepakat bersama,
dan menunjuk Pihak Kedua sebagai yang mempromosikan video endorsement dari
Pihak Pertama.',
            array('name' => 'Arial', 'size' => 12)
        );


        // Save as a .docx file
        $fileName = 'Perjanjian-Kerjasama.docx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
}
