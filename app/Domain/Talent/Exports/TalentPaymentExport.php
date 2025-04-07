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
            ->when($this->request->has('done_payment_start') && $this->request->has('done_payment_end'), function($query) {
                $query->whereDate('done_payment', '>=', $this->request->done_payment_start)
                      ->whereDate('done_payment', '<=', $this->request->done_payment_end);
            })
            ->when($this->request->has('tanggal_pengajuan_start') && $this->request->has('tanggal_pengajuan_end'), function($query) {
                $query->whereDate('tanggal_pengajuan', '>=', $this->request->tanggal_pengajuan_start)
                      ->whereDate('tanggal_pengajuan', '<=', $this->request->tanggal_pengajuan_end);
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
            $rate_card_per_slot = (int)$talent->price_rate; // Cast to integer
            $slot = (int)$talent->slot_final;
            $rate_harga = (int)($rate_card_per_slot * $slot); // Cast to integer
            $discount = (int)$talent->discount; // Cast to integer
            $harga_setelah_diskon = (int)($rate_harga - $discount); // Cast to integer

            if (!is_null($talent->tax_percentage) && $talent->tax_percentage > 0) {
                $pphPercentage = $talent->tax_percentage / 100;
            } else {
                $pphPercentage = Str::startsWith($talent->nama_rekening, ['PT', 'CV']) ? 0.02 : 0.025;
            }
            
            $pphAmount = (int)($harga_setelah_diskon * $pphPercentage); // Cast to integer
            $final_tf = (int)($harga_setelah_diskon - $pphAmount); // Cast to integer
            
            $displayValue = ($payment->amount_tf === null || $payment->amount_tf == 0) ? 
                match($payment->status_payment) {
                    "Termin 1", "Termin 2", "Termin 3" => (int)($final_tf / 3), // Cast to integer
                    "DP 50%", "Pelunasan 50%" => (int)($final_tf / 2), // Cast to integer
                    default => $final_tf
                } : (int)$payment->amount_tf; // Cast to integer

            // Return array directly without storing in variable
            return [
                $payment->done_payment,
                $payment->tanggal_pengajuan,
                $talent->username ?? '',
                $talent->talent_name ?? '',
                $rate_card_per_slot, // Integer
                $slot, // Integer
                $talent->content_type ?? '',
                $rate_harga, // Integer
                $discount, // Integer
                $harga_setelah_diskon, // Integer
                $talent->no_npwp ?? '',
                $pphAmount, // Integer
                $final_tf, // Integer
                $displayValue, // Integer
                $payment->status_payment ?? '',
                $talent->pic ?? '',
                $talent->no_rekening ?? '',
                $talent->bank ?? '',
                $talent->nama_rekening ?? '',
                (string)($talent->nik ?? ''),
            ];

        } catch (\Exception $e) {
            Log::error('Error processing payment row', [
                'payment_id' => $payment->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return array_fill(0, 20, 'ERROR'); // Fixed to match the 20 columns in the heading
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
                
                $lastRow = $sheet->getHighestRow();
                
                // Define all numeric columns including L, M, N
                $numericColumns = ['F', 'E', 'H', 'I', 'J', 'L', 'M', 'N', 'Q', 'R', 'S'];
                
                for ($row = 2; $row <= $lastRow; $row++) {
                    // Format NIK as text explicitly
                    $cell = $sheet->getCell('T' . $row);
                    $value = $cell->getValue();
                    $cell->setValueExplicit(
                        $value, 
                        \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                    );
                    
                    // Convert decimal values in all numeric columns to integers
                    foreach ($numericColumns as $column) {
                        $numericCell = $sheet->getCell($column . $row);
                        $numericValue = $numericCell->getValue();
                        if (is_numeric($numericValue)) {
                            $numericCell->setValue((int)$numericValue);
                        }
                    }
                }
                
                $this->applyValidations($spreadsheet);
                $sheet->getStyle($sheet->calculateWorksheetDimension())->setQuotePrefix(false);
            },
        ];
    }

    protected function applyValidations($spreadsheet)
    {
        // Include all columns that should only allow integer values
        $numericColumns = ['F', 'E', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Q', 'R', 'S', 'T'];
        $chunkSize = 50; // Smaller chunks for validation
        
        foreach ($numericColumns as $column) {
            $validation = $spreadsheet->getCell($column . '2')->getDataValidation();
            
            // Set validation to only allow whole numbers (integers)
            $validation->setType(DataValidation::TYPE_WHOLE)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setErrorTitle('Input Error')
                ->setError('Only whole numbers (integers) allowed')
                ->setPromptTitle('Validation')
                ->setPrompt('Enter an integer (whole number)');
                
            // Apply formula validation to ensure only integers
            $validation->setFormula1('0'); // Minimum value
            
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