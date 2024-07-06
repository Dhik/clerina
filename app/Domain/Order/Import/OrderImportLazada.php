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

class OrderImportLazada implements SkipsEmptyRows, ToModel, WithMapping, WithHeadingRow, WithUpserts, WithValidation, WithBatchInserts, WithChunkReading
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
            // Validate essential fields
            $requiredFields = ['updatetime'];

            foreach ($requiredFields as $field) {
                if (!isset($row[$field]) || is_null($row[$field])) {
                    Log::warning("Skipping row due to missing required field: $field");
                    return []; // Skip this row
                }
            }

            $cleanedRow = [
                'order_id' => $this->cleanData($row['ordernumber']),
                'tracking_number' => $this->cleanData($row['trackingcode']) ?? 'N/A',
                'shipping_provider' => $this->cleanData($row['shippingprovider']),
                'payment_date' => $this->formatDateForDatabase($row['updatetime']) ,
                'payment_method' => $this->cleanData($row['paymethod']),
                'product_name' => $this->cleanData($row['itemname']),
                'sku' => $this->cleanData($row['sellersku']),
                'variation' => $this->cleanData($row['variation']),
                'price' => $this->convertCurrencyStringToNumber($this->cleanData($row['paidprice'])),
                'quantity' => $this->cleanData($row['qty']),
                'buyer_username' => $this->cleanData($row['customername']),
                'recipient_name' => $this->cleanData($row['customername']),
                'recipient_phone' => $this->cleanData($row['billingphone']),
                'shipping_address' => $this->cleanData($row['shippingaddress']),
                'city' => $this->cleanData($row['shippingcity']),
                'province' => $this->cleanData($row['billingaddr4']),
                'sales_channel_id' => $this->salesChannelId,
            ];

            $this->cleanedData[] = $cleanedRow;

            return $cleanedRow;
        } catch (Exception $e) {
            Log::error("Error mapping row: " . json_encode($row) . " - Exception: " . $e->getMessage());
            return null; // Skip the row that caused the error
        }
    }

    public function model(array $row): Model
    {
        try {
            $price = $row['price'];

            $order = Order::updateOrCreate([
                'id_order' => $row['order_id'],
                'receipt_number' => $row['tracking_number'],
                'date' => $row['payment_date'],
                'sku' => $row['sku'],
                'sales_channel_id' => $this->salesChannelId,
                'tenant_id' => $this->tenantId,
            ], [
                'shipment' => $row['shipping_provider'],
                'payment_method' => $row['payment_method'],
                'product' => $row['product_name'],
                'variant' => $row['variation'],
                'price' => $price,
                'qty' => $row['quantity'],
                'username' => $row['buyer_username'],
                'customer_name' => $row['recipient_name'],
                'customer_phone_number' => $row['recipient_phone'],
                'shipping_address' => $row['shipping_address'],
                'city' => $row['city'],
                'province' => $row['province'],
                'amount' => $row['quantity'] * $price,
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
            'payment_date' => 'required',
            'order_id' => 'required',
            'recipient_name' => 'max:255',
            'recipient_phone' => 'max:255',
            'product_name' => 'max:255',
            'quantity' => 'required|numeric|integer',
            'tracking_number' => 'required',
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
