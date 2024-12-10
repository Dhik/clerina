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
use App\Domain\Talent\Models\TalentPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Auth;

class TalentPaymentExport implements FromQuery, WithChunkReading, WithMapping, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    use Exportable;

    protected $request;
    protected $tenantId;
    protected $chunkSize = 100; // Reduced chunk size
    
    public function __construct(Request $request, $tenantId)
    {
        $this->request = $request;
        $this->tenantId = $tenantId;
    }

    public function headings(): array
    {
        return [
            'Tanggal Transfer',
            'Tanggal Pengajuan',
            'Username',
            'Nama Talent',
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
        return TalentPayment::query()
            ->select('talent_payments.*') // Select only necessary columns
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
                    'nik',
                    'tax_percentage',
                    'talent_name',
                )
                ->where('tenant_id', $this->tenantId);
            }])
            ->when($this->request->has('pic') && $this->request->pic != '', function($query) {
                $query->whereHas('talent', function($q) {
                    $q->where('pic', $this->request->pic);
                });
            })
            ->when($this->request->has('username') && is_array($this->request->username) && count($this->request->username) > 0, function($query) {
                $query->whereHas('talent', function($q) {
                    $q->whereIn('username', $this->request->username);
                });
            })
            ->when($this->request->has('status_payment') && $this->request->status_payment != '', function($query) {
                $query->where('status_payment', $this->request->status_payment);
            })
            ->when($this->request->has('done_payment') && $this->request->done_payment != '', function($query) {
                $query->where('done_payment', $this->request->done_payment);
            })
            ->when($this->request->has('tanggal_pengajuan') && $this->request->tanggal_pengajuan != '', function($query) {
                $query->whereDate('tanggal_pengajuan', $this->request->tanggal_pengajuan);
            })
            ->orderBy('id'); 
    }

    public function map($payment): array
    {
        try {
            $talent = $payment->talent;
            
            if (!$talent) {
                return [];
            }

            // Pre-calculate values to reduce memory usage
            $rate_card_per_slot = (float)$talent->price_rate;
            $slot = (int)$talent->slot_final;
            $rate_harga = $rate_card_per_slot * $slot;
            $discount = (float)$talent->discount;
            $harga_setelah_diskon = $rate_harga - $discount;

            if (!is_null($talent->tax_percentage) && $talent->tax_percentage > 0) {
                $pphPercentage = $talent->tax_percentage / 100;
            } else {
                $pphPercentage = Str::startsWith($talent->nama_rekening, ['PT', 'CV']) ? 0.02 : 0.025;
            }
            
            $pphAmount = $harga_setelah_diskon * $pphPercentage;
            $final_tf = $harga_setelah_diskon - $pphAmount;
            
            // Calculate display value
            $displayValue = match($payment->status_payment) {
                "Termin 1", "Termin 2", "Termin 3" => $final_tf / 3,
                "DP 50%", "Pelunasan 50%" => $final_tf / 2,
                default => $final_tf
            };

            // Return array directly without storing in variable
            return [
                $payment->done_payment,
                $payment->tanggal_pengajuan,
                $talent->username ?? '',
                $talent->talent_name ?? '',
                $rate_card_per_slot,
                $slot,
                $talent->content_type ?? '',
                $rate_harga,
                $discount,
                $harga_setelah_diskon,
                $talent->no_npwp ?? '',
                $pphAmount,
                $final_tf,
                $displayValue,
                $payment->status_payment ?? '',
                $talent->pic ?? '',
                $talent->no_rekening ?? '',
                $talent->bank ?? '',
                $talent->nama_rekening ?? '',
                $talent->nik ?? '',
            ];

        } catch (\Exception $e) {
            Log::error('Error processing payment row', [
                'payment_id' => $payment->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return array_fill(0, 18, 'ERROR');
        }
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
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
                $spreadsheet = $sheet->getDelegate();
                
                $this->applyValidations($spreadsheet);
                $sheet->getStyle($sheet->calculateWorksheetDimension())->setQuotePrefix(false);
            },
        ];
    }

    protected function applyValidations($spreadsheet)
    {
        $numericColumns = ['K', 'F', 'T', 'U', 'V', 'W', 'X'];
        $chunkSize = 50; // Smaller chunks for validation
        
        foreach ($numericColumns as $column) {
            $validation = $spreadsheet->getCell($column . '2')->getDataValidation();
            $validation->setType(DataValidation::TYPE_WHOLE)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Input Error')
                ->setError('Numbers only')
                ->setPromptTitle('Validation')
                ->setPrompt('Enter number');

            // Apply validation in smaller chunks
            for ($row = 2; $row <= 1000; $row += $chunkSize) {
                $endRow = min($row + $chunkSize - 1, 1000);
                for ($currentRow = $row; $currentRow <= $endRow; $currentRow++) {
                    $spreadsheet->getCell($column . $currentRow)
                        ->setDataValidation(clone $validation);
                }
                gc_collect_cycles();
            }
        }
    }
}