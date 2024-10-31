<?php

namespace App\Domain\Talent\Exports;

use Yajra\DataTables\Utilities\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Models\TalentPayment;

class TalentPaymentExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function headings(): array
    {
        return [
            'Tanggal Transfer',
            'Username',
            'Rate Card Per Slot',
            'Slot',
            'Jenis Konten',
            'Rate Harga',
            'Besar Diskon',
            'Harga Setelah Diskon',
            'NPWP',
            'PPh Deduction',
            'Final TF',
            'Total Payment',
            'Keterangan (DP 50%)',
            'Nama PIC',
            'No Rekening',
            'Nama Bank',
            'Nama Penerima',
            'NIK',
        ];
    }

    /**
     * Fetch the talent payment data based on filters.
     */
    public function collection()
    {
        $query = TalentPayment::with('talent');

        // Apply filters if provided
        if ($this->request->has('pic') && $this->request->pic != '') {
            $query->whereHas('talent', function($q) {
                $q->where('pic', $this->request->pic);
            });
        }

        if ($this->request->has('username') && $this->request->username != '') {
            $query->whereHas('talent', function($q) {
                $q->where('username', $this->request->username);
            });
        }

        $talentContents = $query->get();

        // Process each record
        return $talentContents->map(function ($content) {
            $rate_card_per_slot = $content->talent->price_rate;
            $slot = $content->talent->slot_final;
            $rate_harga = $rate_card_per_slot * $slot;
            $discount = $content->talent->discount;
            $harga_setelah_diskon = $rate_harga - $discount;
            $pphPercentage = $content->isPTorCV ? 0.02 : 0.025;
            $pphAmount = $harga_setelah_diskon * $pphPercentage;
            $final_tf = $harga_setelah_diskon - $pphAmount;
            $displayValue = $final_tf;
            if (in_array($content->status_payment, ["Termin 1", "Termin 3", "Termin 2"])) {
                $displayValue = $final_tf / 3;
            } elseif ($content->status_payment === "DP 50%") {
                $displayValue = $final_tf / 2;
            } elseif ($content->status_payment === "Full Payment") {
                $displayValue = $final_tf;
            } elseif ($content->status_payment === "Pelunasan 50%") {
                $displayValue = $final_tf / 2;
            }

            return [
                $content->done_payment,
                $content->talent->username,
                $rate_card_per_slot,
                $slot,
                $content->talent->content_type,
                $rate_harga,
                $discount,
                $harga_setelah_diskon,
                $content->talent->no_npwp,
                $pphAmount,
                $final_tf,
                $displayValue,
                $content->status_payment,
                $content->talent->pic,
                $content->talent->no_rekening,
                $content->talent->bank,
                $content->talent->nama_rekening,
                $content->talent->nik,
            ];
        });
    }

    /**
     * Set the title for the Excel sheet.
     */
    public function title(): string
    {
        return 'Talent';
    }

    /**
     * Register events to handle validation and styling after the sheet is created.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lengthValidationRow = 10000;

                // Get the active sheet
                $spreadsheet = $sheet->getDelegate();

                // Define numeric validation for the specific columns
                $numericValidation = function ($column) use ($spreadsheet, $lengthValidationRow) {
                    $validation = $spreadsheet->getCell($column . '2')->getDataValidation();
                    $validation->setType(DataValidation::TYPE_WHOLE)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(true)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Input Error')
                        ->setError('This field can only contain numbers')
                        ->setPromptTitle('Number Validation')
                        ->setPrompt('Please enter a valid number');

                    // Apply validation for all rows up to the specified row limit
                    for ($row = 2; $row <= $lengthValidationRow; $row++) {
                        $spreadsheet->getCell($column . $row)->setDataValidation(clone $validation);
                    }
                };

                // Apply numeric validation to necessary columns
                $numericValidation('K'); // Followers
                $numericValidation('F'); // Rate Final
                $numericValidation('T'); // Price Rate
                $numericValidation('U'); // First Rate Card
                $numericValidation('V'); // Discount
                $numericValidation('W'); // Slot Final
                $numericValidation('X'); // Tax Deduction
            },
        ];
    }
}
