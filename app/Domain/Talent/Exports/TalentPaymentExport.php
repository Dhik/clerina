<?php

namespace App\Domain\Talent\Exports;

use Yajra\DataTables\Utilities\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Talent\Models\TalentPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TalentPaymentExport implements FromQuery, WithChunkReading, WithMapping, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    use Exportable;

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

    public function query()
    {
        $query = TalentPayment::query()
            ->with(['talent' => function ($query) {
                $query->select(
                    'id',
                    'username',
                    'price_rate',
                    'slot_final',
                    'content_type',
                    'discount',
                    'no_npwp',
                    'pic',
                    'no_rekening',
                    'bank',
                    'nama_rekening',
                    'nik'
                );
            }])
            ->select([
                'id',
                'talent_id',
                'done_payment',
                'status_payment',
                'amount_tf'
            ]);

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

        return $query;
    }

    public function map($payment): array
    {
        try {
            $talent = $payment->talent;
            if (!$talent) {
                Log::error('Talent not found for payment', ['payment_id' => $payment->id]);
                return array_fill(0, 18, 'N/A'); // Return empty row with N/A values
            }

            $rate_card_per_slot = $talent->price_rate;
            $slot = $talent->slot_final;
            $rate_harga = $rate_card_per_slot * $slot;
            $discount = $talent->discount;
            $harga_setelah_diskon = $rate_harga - $discount;
            
            // Calculate PPH based on nama_rekening
            $isPTorCV = Str::startsWith($talent->nama_rekening, ['PT', 'CV']);
            $pphPercentage = $isPTorCV ? 0.02 : 0.025;
            $pphAmount = $harga_setelah_diskon * $pphPercentage;
            $final_tf = $harga_setelah_diskon - $pphAmount;
            
            // Calculate display value based on payment status
            $displayValue = match($payment->status_payment) {
                "Termin 1", "Termin 2", "Termin 3" => $final_tf / 3,
                "DP 50%", "Pelunasan 50%" => $final_tf / 2,
                "Full Payment" => $final_tf,
                default => $final_tf
            };

            return [
                $payment->done_payment,
                $talent->username,
                $rate_card_per_slot,
                $slot,
                $talent->content_type,
                $rate_harga,
                $discount,
                $harga_setelah_diskon,
                $talent->no_npwp,
                $pphAmount,
                $final_tf,
                $displayValue,
                $payment->status_payment,
                $talent->pic,
                $talent->no_rekening,
                $talent->bank,
                $talent->nama_rekening,
                $talent->nik,
            ];
        } catch (\Exception $e) {
            Log::error('Error processing payment row: ' . $e->getMessage(), [
                'payment_id' => $payment->id ?? 'unknown'
            ]);
            throw $e;
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function title(): string
    {
        return 'Talent';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lengthValidationRow = 10000;
                $spreadsheet = $sheet->getDelegate();

                // Define numeric validation for specific columns
                $numericColumns = ['K', 'F', 'T', 'U', 'V', 'W', 'X'];
                foreach ($numericColumns as $column) {
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

                    // Apply validation in chunks
                    for ($row = 2; $row <= $lengthValidationRow; $row += 100) {
                        $endRow = min($row + 99, $lengthValidationRow);
                        for ($currentRow = $row; $currentRow <= $endRow; $currentRow++) {
                            $spreadsheet->getCell($column . $currentRow)
                                ->setDataValidation(clone $validation);
                        }
                    }
                }
            },
        ];
    }
}