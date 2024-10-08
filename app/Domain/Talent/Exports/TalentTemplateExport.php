<?php

namespace App\Domain\Talent\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TalentTemplateExport implements ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    /**
     * Return the Excel sheet headings.
     */
    public function headings(): array
    {
        return [
            'Username *',
            'Talent Name *',
            'Video Slot',
            'Content Type',
            'Produk',
            'Rate Final',
            'PIC',
            'Bulan Running',
            'Niche',
            'Followers',
            'Address',
            'Phone Number *',
            'Bank',
            'No Rekening',
            'Nama Rekening',
            'No NPWP',
            'Pengajuan Transfer Date *',
            'GDrive TTD Kol Accepting',
            'NIK',
            'Price Rate',
            'First Rate Card',
            'Discount',
            'Slot Final',
            'Tax Deduction',
        ];
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

                // Define quantity validation for numeric fields (Followers, Rate Final, etc.)
                $numericValidation = $spreadsheet->getCell('K2')->getDataValidation();
                $numericValidation->setType(DataValidation::TYPE_WHOLE)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(true)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Input Error')
                    ->setError('This field can only contain numbers')
                    ->setPromptTitle('Number Validation')
                    ->setPrompt('Please enter a valid number');

                // Define date validation for Pengajuan Transfer Date
                $dateValidation = $spreadsheet->getCell('R2')->getDataValidation();
                $dateValidation->setType(DataValidation::TYPE_DATE)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Input Error')
                    ->setError('Please enter a valid date')
                    ->setPromptTitle('Date Validation')
                    ->setPrompt('Please enter a valid date');

                // Apply validation for all relevant columns up to the specified row limit
                for ($row = 2; $row <= $lengthValidationRow; $row++) {
                    $spreadsheet->getCell('R' . $row)->setDataValidation(clone $dateValidation); // Date validation
                    $spreadsheet->getCell('K' . $row)->setDataValidation(clone $numericValidation); // Followers
                    $spreadsheet->getCell('F' . $row)->setDataValidation(clone $numericValidation); // Rate Final
                    $spreadsheet->getCell('T' . $row)->setDataValidation(clone $numericValidation); // Price Rate
                    $spreadsheet->getCell('U' . $row)->setDataValidation(clone $numericValidation); // First Rate Card
                    $spreadsheet->getCell('V' . $row)->setDataValidation(clone $numericValidation); // Discount
                    $spreadsheet->getCell('W' . $row)->setDataValidation(clone $numericValidation); // Slot Final
                    $spreadsheet->getCell('X' . $row)->setDataValidation(clone $numericValidation); // Tax Deduction
                }
            },
        ];
    }
}
