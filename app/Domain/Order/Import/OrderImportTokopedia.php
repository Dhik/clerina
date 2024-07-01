<?php

namespace App\Domain\Order\Import;

use App\Domain\Order\Job\CreateCustomerJob;
use App\Domain\Customer\BLL\Customer\CustomerBLL;
use App\Domain\Order\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrderImportTokopedia implements SkipsEmptyRows, ToModel, WithMapping, WithHeadingRow, WithUpserts, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected array $importedData = [];
    protected array $cleanedData = [];
    protected bool $loggedHeader = false;

    public function __construct(protected int $salesChannelId, protected int $tenantId)
    {
        $this->customerBLL = App::make(CustomerBLL::class);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function uniqueBy(): string
    {
        return 'id_order';
    }

    public function map($row): array
    {
        // Log the column names (keys) of the first row for debugging
        if (!$this->loggedHeader) {
            Log::info('Column names: ' . json_encode(array_keys($row)));
            $this->loggedHeader = true;
        }

        try {
            $cleanedRow = [
                'no_pesanan' => $this->cleanData($row['nomor_invoice']),
                'no_resi' => $this->cleanData($row['no_resi_kode_booking']) ?? 'N/A',
                'opsi_pengiriman' => $this->cleanData($row['nama_kurir']),
                'tanggal' => $this->formatDateForDatabase($row['tanggal_pembayaran']),
                'metode_pembayaran' => $this->cleanData($row['metode_pembayaran']),
                'nama_produk' => $this->cleanData($row['nama_produk']),
                'sku' => $this->cleanData($row['nomor_sku']),
                'nama_variasi' => $this->cleanData($row['tipe_produk']),
                'harga' => $this->convertCurrencyStringToNumber($this->cleanData($row['harga_jual_idr'])),
                'jumlah_qty' => $this->cleanData($row['jumlah_produk_dibeli']),
                'username' => $this->cleanData($row['nama_pembeli']),
                'nama_penerima' => $this->cleanData($row['nama_penerima']),
                'nomor_telepon' => $this->cleanData($row['no_telp_penerima']),
                'alamat_pengiriman' => $this->cleanData($row['alamat_pengiriman']),
                'kotakabupaten' => $this->cleanData($row['kota']),
                'provinsi' => $this->cleanData($row['provinsi']),
                'sales_channel_id' => $this->salesChannelId,
            ];

            $this->cleanedData[] = $cleanedRow;

            return $cleanedRow;
        } catch (Exception $e) {
            abort(500, "Error mapping row: " . json_encode($row) . " - Exception: " . $e->getMessage());
        }
    }

    public function model(array $row): Model
    {
        try {
            $price = $row['harga'];

            $order = Order::updateOrCreate([
                'id_order' => $row['no_pesanan'],
                'receipt_number' => $row['no_resi'],
                'date' => $row['tanggal'],
                'sku' => $row['sku'],
                'sales_channel_id' => $this->salesChannelId,
                'tenant_id' => $this->tenantId,
            ], [
                'shipment' => $row['opsi_pengiriman'],
                'payment_method' => $row['metode_pembayaran'],
                'product' => $row['nama_produk'],
                'variant' => $row['nama_variasi'],
                'price' => $price,
                'qty' => $row['jumlah_qty'],
                'username' => $row['username'],
                'customer_name' => $row['nama_penerima'],
                'customer_phone_number' => $row['nomor_telepon'],
                'shipping_address' => $row['alamat_pengiriman'],
                'city' => $row['kotakabupaten'],
                'province' => $row['provinsi'],
                'amount' => $row['jumlah_qty'] * $price,
            ]);

            // Additional actions after order creation or update
            if ($order->wasRecentlyCreated) {
                // Perform additional actions for order creation
                $data = [
                    'customer_name' => $order->customer_name,
                    'customer_phone_number' => $order->customer_phone_number,
                    'tenant_id' => $order->tenant_id
                ];

                CreateCustomerJob::dispatch($data);
            }

            $this->importedData[] = $order;
            return $order;
        } catch (Exception $e) {
            abort(500, "Error processing row: " . json_encode($row) . " - Exception: " . $e->getMessage());
        }
    }

    public function getImportedData(): array
    {
        return $this->importedData;
    }

    public function getCleanedData(): array
    {
        return $this->cleanedData;
    }

    public function rules(): array
    {
        return [
            'tanggal' => 'required',
            'no_pesanan' => 'required',
            'nama_penerima' => 'max:255',
            'nomor_telepon' => 'max:255',
            'nama_produk' => 'max:255',
            'jumlah_qty' => 'required|numeric|integer',
            'no_resi' => 'required',
            'sku' => 'required',
            'harga' => 'required',
            'alamat_pengiriman' => 'required',
        ];
    }

    protected function cleanData($data)
    {
        if (is_string($data)) {
            return trim(preg_replace('/\s+/', ' ', $data));
        }
        return $data;
    }

    protected function convertCurrencyStringToNumber($currencyString): int
    {
        // Extract numeric part
        preg_match("/[0-9,.]+/", $currencyString, $matches);

        // Remove dots
        $cleanedString = str_replace('.', '', $matches[0]);

        // If comma is present and there are more digits after it, remove the comma
        if (strpos($cleanedString, ',') !== false && preg_match('/,\d{3}/', $cleanedString)) {
            $cleanedString = str_replace(',', '', $cleanedString);
        }

        // Replace comma with dot if it's a decimal separator
        if (strpos($cleanedString, ',') !== false) {
            $cleanedString = str_replace(',', '.', $cleanedString);
        }

        // Convert to integer
        return intval($cleanedString);
    }

    protected function formatDateForDatabase($dateString)
    {
        if (is_numeric($dateString)) {
            // Convert Excel numeric date to PHP date
            $date = Date::excelToDateTimeObject($dateString);
            return $date->format('Y-m-d H:i:s');
        }

        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'Y-m-d H:i',
            'd M Y H:i',
            'd/m/Y',
            'Y-m-d',
            'd-m-Y H:i:s', // Add new format
            'd-m-Y'        // Add new format
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString)->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                // Continue to the next format if this one fails
            }
        }

        // Optionally, handle the case where no formats match
        throw new Exception("Date format not recognized: $dateString");
    }
}
