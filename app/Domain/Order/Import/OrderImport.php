<?php
namespace App\Domain\Order\Import;

use App\Domain\Order\Job\CreateCustomerJob;
use App\Domain\Customer\BLL\Customer\CustomerBLL;
use App\Domain\Order\Models\Order;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class OrderImport implements SkipsEmptyRows, ToModel, WithMapping, WithStartRow, WithUpserts, WithValidation
{
    use Importable;

    protected array $importedData = [];

    public function __construct(protected int $salesChannelId, protected int $tenantId)
    {
        $this->customerBLL = App::make(CustomerBLL::class);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy(): string
    {
        return 'id_order';
    }

    public function map($row): array
    {
        return [
            'id_order' => $row[0],
            'receipt_number' => $row[1],
            'shipment' => $row[2],
            'date' => $row[3],
            'payment_method' => $row[4],
            'product' => $row[5],
            'sku' => $row[6],
            'variant' => $row[7],
            'price' => $row[8],
            'qty' => $row[9],
            'username' => $row[10],
            'customer_name' => $row[11],
            'customer_phone_number' => $row[12],
            'shipping_address' => $row[13],
            'city' => $row[14],
            'province' => $row[15],
            'sales_channel_id' => $this->salesChannelId,
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row): void
    {
        $price = $this->convertCurrencyStringToNumber($row['price']);

        $existingOrder = Order::where('id_order', $row['id_order'])->first();

        if ($existingOrder) {
            $existingOrder->update([
                'receipt_number' => $row['receipt_number'],
                'date' => $this->formatDateForDatabase($row['date']),
                'shipment' => $row['shipment'],
                'payment_method' => $row['payment_method'],
                'product' => $row['product'],
                'variant' => $row['variant'],
                'username' => $row['username'],
                'customer_name' => $row['customer_name'],
                'customer_phone_number' => $row['customer_phone_number'],
                'shipping_address' => $row['shipping_address'],
                'city' => $row['city'],
                'province' => $row['province'],
            ]);
        } else {
            $order = Order::create([
                'id_order' => $row['id_order'],
                'receipt_number' => $row['receipt_number'],
                'date' => $this->formatDateForDatabase($row['date']),
                'sku' => $row['sku'],
                'sales_channel_id' => $this->salesChannelId,
                'tenant_id' => $this->tenantId,
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
                'amount' => $row['qty'] * $price,
            ]);

            // Additional actions after order creation
            if ($order->wasRecentlyCreated) {
                $data = [
                    'customer_name' => $order->customer_name,
                    'customer_phone_number' => $order->customer_phone_number,
                    'tenant_id' => $order->tenant_id
                ];

                CreateCustomerJob::dispatch($data);
            }

            $this->importedData[] = $order;
        }
    }

    public function getImportedData(): array
    {
        return $this->importedData;
    }

    public function rules(): array
    {
        return [
            'date' => 'required',
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
        $formats = [
            'd/m/Y H:i:s',
            'd/m/Y H:i',
            'Y-m-d H:i',
            'd M Y H:i',
            'd/m/Y',
            'Y-m-d',
            'd-m-Y H:i:s'  // Added this format
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString)->format('Y-m-d');
            } catch (Exception $e) {
                // Continue to the next format if this one fails
            }
        }

        try {
            $formattedDate = Date::excelToDateTimeObject($dateString);
            return $formattedDate->format('Y-m-d');
        } catch (Exception $e) {
            // Continue to the next format if this one fails
        }

        // Optionally, handle the case where no formats match
        throw new Exception("Date format not recognized: $dateString");
    }
}
