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

        // Skip rows where any mandatory field is null
        // $mandatoryFields = [
        //     'orderNumber',
        //     'trackingCode',
        //     'shippingProvider',
        //     'updateTime',
        //     'itemName',
        //     'sellerSku',
        //     'qty',
        //     'paidPrice',
        //     'customerName',
        //     'shippingAddress',
        //     'billingPhone',
        //     'shippingCity',
        //     'billingAddr4'
        // ];

        // foreach ($mandatoryFields as $field) {
        //     if (!isset($row[$field]) || is_null($row[$field]) || trim($row[$field]) === '') {
        //         return [];
        //     }
        // }

        try {
            $cleanedRow = [
                'id_order' => $this->cleanData($row['orderNumber']),
                'receipt_number' => $this->cleanData($row['trackingCode']),
                'shipment' => $this->cleanData($row['shippingProvider']),
                'date' => $this->formatDateForDatabase($row['updateTime']),
                'payment_method' => $this->cleanData($row['payMethod']),
                'product' => $this->cleanData($row['itemName']),
                'sku' => $this->cleanData($row['sellerSku']),
                'variant' => $this->cleanData($row['variation']),
                'price' => $this->convertCurrencyStringToNumber($this->cleanData($row['paidPrice'])),
                'qty' => $this->cleanData($row['qty']),
                'username' => $this->cleanData($row['customerName']),
                'customer_name' => $this->cleanData($row['customerName']),
                'shipping_address' => $this->cleanData($row['shippingAddress']),
                'customer_phone_number' => $this->cleanData($row['billingPhone']),
                'city' => $this->cleanData($row['shippingCity']),
                'province' => $this->cleanData($row['billingAddr4']),
                'sales_channel_id' => $this->salesChannelId,
                'tenant_id' => $this->tenantId,
                'amount' => $this->cleanData($row['qty']) * $this->convertCurrencyStringToNumber($this->cleanData($row['paidPrice']))
            ];

            $this->cleanedData[] = $cleanedRow;

            return $cleanedRow;
        } catch (Exception $e) {
            Log::error("Error mapping row: " . json_encode($row) . " - Exception: " . $e->getMessage());
            return [];
        }
    }

    public function model(array $row): ?Model
    {
        // Skip rows where all critical fields are null
        if (empty($row)) {
            return null;
        }

        try {
            $price = $row['price'];

            $order = Order::updateOrCreate([
                'id_order' => $row['id_order'],
                'receipt_number' => $row['receipt_number'],
                'date' => $row['date'],
                'sku' => $row['sku'],
                'sales_channel_id' => $this->salesChannelId,
                'tenant_id' => $this->tenantId,
            ], [
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
            Log::error("Error processing row: " . json_encode($row) . " - Exception: " . $e->getMessage());
            return null;
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
            '*.id_order' => 'nullable',
            '*.receipt_number' => 'nullable',
            '*.shipment' => 'nullable',
            '*.date' => 'nullable',
            '*.payment_method' => 'max:255',
            '*.product' => 'nullable',
            '*.sku' => 'nullable',
            '*.variant' => 'max:255',
            '*.price' => 'nullable',
            '*.qty' => 'nullable|numeric|integer',
            '*.username' => 'max:255',
            '*.customer_name' => 'nullable',
            '*.customer_phone_number' => 'max:255',
            '*.shipping_address' => 'nullable',
            '*.city' => 'max:255',
            '*.province' => 'max:255',
            '*.amount' => 'nullable',
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
        if (is_null($dateString) || $dateString === '') {
            return null;
        }

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
