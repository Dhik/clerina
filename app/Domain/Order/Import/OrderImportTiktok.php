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
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrderImportTiktok implements SkipsEmptyRows, ToModel, WithMapping, WithHeadingRow, WithUpserts, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected array $importedData = [];
    protected array $cleanedData = [];
    protected bool $loggedHeader = false;
    protected int $rowNumber = 1; // Row counter

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

        // Increment row counter
        $this->rowNumber++;

        // List of required fields
        $requiredFields = [
            'nomor_invoice',
            'no_resi_kode_booking',
            'nama_kurir',
            'tanggal_pembayaran',
            'metode_pembayaran',
            'nama_produk',
            'nomor_sku',
            'tipe_produk',
            'harga_jual_idr',
            'jumlah_produk_dibeli',
            'nama_pembeli',
            'nama_penerima',
            'no_telp_penerima',
            'alamat_pengiriman',
            'kota',
            'provinsi'
        ];

        try {
            $cleanedRow = [
                'id_order' => $this->cleanData($row['order_id']),
                'receipt_number' => $this->cleanData($row['tracking_id']),
                'shipment' => $this->cleanData($row['shipping_provider_name']),
                'date' => $this->formatDateForDatabase($row['paid_time']),
                'payment_method' => $this->cleanData($row['payment_method']),
                'product' => $this->cleanData($row['product_name']),
                'sku' => $this->cleanData($row['seller_sku']),
                'variant' => $this->cleanData($row['variation']),
                'price' => $this->convertCurrencyStringToNumber($this->cleanData($row['sku_subtotal_after_discount'])),
                'qty' => $this->cleanData($row['quantity']),
                'username' => $this->cleanData($row['recipient']),
                'customer_name' => $this->cleanData($row['recipient']),
                'customer_phone_number' => $this->cleanData($row['phone']),
                'shipping_address' => $this->cleanData($row['detail_address']),
                'city' => $this->cleanData($row['regency_and_city']),
                'province' => $this->cleanData($row['province']),
                'sales_channel_id' => $this->salesChannelId,
                'tenant_id' => $this->tenantId,
                'amount' => $this->cleanData($row['quantity']) * $this->convertCurrencyStringToNumber($this->cleanData($row['sku_subtotal_after_discount'])),
            ];

            $this->cleanedData[] = $cleanedRow;

            return $cleanedRow;
        } catch (Exception $e) {
            abort(500, "Error mapping row {$this->rowNumber}: " . json_encode($row) . " - Exception: " . $e->getMessage());
        }
    }

    public function model(array $row): Model
    {
        try {
            $price = $row['price'];

            // Check if the order already exists
            $existingOrder = Order::where('id_order', $row['id_order'])
                ->where('receipt_number', $row['receipt_number'])
                ->where('date', $row['date'])
                ->where('sku', $row['sku'])
                ->where('sales_channel_id', $row['sales_channel_id'])
                ->where('tenant_id', $row['tenant_id'])
                ->first();

            if (!$existingOrder) {
                $order = Order::create([
                    'id_order' => $row['id_order'],
                    'receipt_number' => $row['receipt_number'],
                    'date' => $row['date'],
                    'sku' => $row['sku'],
                    'sales_channel_id' => $row['sales_channel_id'],
                    'tenant_id' => $row['tenant_id'],
                    'shipment' => $row['shipment'],
                    'payment_method' => $row['payment_method'],
                    'product' => $row['product'],
                    'variant' => $row['variant'],
                    'price' => $price,
                    'qty' => $row['qty'],
                    'username' => $row['username'],
                    'customer_name' => $row['customer_name'],
                    'customer_phone_number' => $row['customer_phone_number'],
                    'shipping_address' => $row['shipping_address'],
                    'city' => $row['city'],
                    'province' => $row['province'],
                    'amount' => $row['amount'],
                ]);

                // Perform additional actions for order creation
                $data = [
                    'customer_name' => $order->customer_name,
                    'customer_phone_number' => $order->customer_phone_number,
                    'tenant_id' => $order->tenant_id,
                ];

                CreateCustomerJob::dispatch($data);

                $this->importedData[] = $order;
                return $order;
            } else {
                return $existingOrder;
            }
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
            'date' => 'required|date',
            'id_order' => 'required',
            'customer_name' => 'max:255',
            'customer_phone_number' => 'max:255',
            'product' => 'max:255',
            'qty' => 'required|numeric|integer',
            'receipt_number' => 'required',
            'sku' => 'required',
            'price' => 'required',
            'shipping_address' => 'required',
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

