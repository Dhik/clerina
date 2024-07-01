<?php

namespace App\Domain\Order\Exports;

use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLL;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrderTemplateExport implements ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    public function headings(): array
    {
        return [
            trans('labels.id_order') . ' *',
            trans('labels.receipt_number') . ' *',
            trans('labels.shipment'),
            trans('labels.date') . ' *',
            trans('labels.payment_method'),
            trans('labels.product') . ' *',
            trans('labels.sku') . ' *',
            trans('labels.variant'),
            trans('labels.price') . ' *',
            trans('labels.qty') . ' *',
            trans('labels.username') . ' *',
            trans('labels.customer_name'),
            trans('labels.phone_number') . ' *',
            trans('labels.shipping_address') . ' *',
            trans('labels.city'),
            trans('labels.province'),
        ];
    }

    public function title(): string
    {
        return 'Order';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lengthValidationRow = 10000;

                $salesBLL = app(SalesChannelBLL::class);

                // Get the active sheet
                $spreadsheet = $sheet->getDelegate();

                // Define quantity validation
                $qtyValidation = $spreadsheet->getCell('J2')->getDataValidation();
                $qtyValidation->setType(DataValidation::TYPE_WHOLE)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Input Error')
                    ->setError('Hanya bisa diisi angka')
                    ->setPromptTitle('Validasi Jumlah')
                    ->setPrompt('Hanya bisa diisi angka');

                // Define date validation
                $dateValidation = $spreadsheet->getCell('D2')->getDataValidation();
                $dateValidation->setType(DataValidation::TYPE_DATE)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Input Error')
                    ->setError('Hanya bisa diisi dengan tanggal yang valid')
                    ->setPromptTitle('Validasi Tanggal')
                    ->setPrompt('Hanya bisa diisi dengan tanggal yang valid');

                for ($row = 2; $row <= $lengthValidationRow; $row++) {
                    $spreadsheet->getCell('D'.$row)->setDataValidation(clone $dateValidation);
                    $spreadsheet->getCell('J'.$row)->setDataValidation(clone $qtyValidation);
                }
            },
        ];
    }
}
