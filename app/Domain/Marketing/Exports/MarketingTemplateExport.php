<?php

namespace App\Domain\Marketing\Exports;

use App\Domain\Marketing\BLL\MarketingCategory\MarketingCategoryBLL;
use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingTemplateExport implements ShouldAutoSize, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function headings(): array
    {
        return [
            trans('labels.date'),
            trans('labels.type'),
            trans('labels.category'),
            trans('labels.sub_category'),
            trans('labels.amount'),
        ];
    }

    public function title(): string
    {
        return 'Marketing';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lengthValidationRow = 1000;

                $marketingCategoryBLL = app(MarketingCategoryBLL::class);

                $typeOptions = MarketingCategoryTypeEnum::Category;
                $categoryOptions = $marketingCategoryBLL->getAllMarketingCategories()->pluck('name')->toArray();
                $subCategoryOptions = $marketingCategoryBLL->getAllMarketingSubCategories()->pluck('name')
                    ->toArray();

                // Get the active sheet
                $spreadsheet = $sheet->getDelegate();

                $dateValidation = $spreadsheet->getCell('A2')->getDataValidation();
                $dateValidation->setType(DataValidation::TYPE_DATE)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Input Error')
                    ->setError('Tanggal salah, Gunakan format dd/mm/yyyy, contoh: 12/12/2024')
                    ->setPromptTitle('Validasi Tanggal')
                    ->setPrompt('Gunakan format dd/mm/yyyy, contoh: 12/12/2024');

                $typeValidation = $spreadsheet->getCell('B2')->getDataValidation();
                $typeValidation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Input error')
                    ->setError('data tidak ada dalam list')
                    ->setPromptTitle('Pilih tipe')
                    ->setPrompt('Pilih tipe dari list')
                    ->setFormula1('"'.implode(',', $typeOptions).'"');

                $categoryValidation = $spreadsheet->getCell('C2')->getDataValidation();
                $categoryValidation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Input error')
                    ->setError('data tidak ada dalam list')
                    ->setPromptTitle('Pilih kategori')
                    ->setPrompt('Pilih kategori dari list')
                    ->setFormula1('"'.implode(',', $categoryOptions).'"');

                $subCategoryValidation = $spreadsheet->getCell('D2')->getDataValidation();
                $subCategoryValidation->setType(DataValidation::TYPE_LIST)
                    ->setErrorStyle(DataValidation::STYLE_INFORMATION)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setShowDropDown(true)
                    ->setErrorTitle('Input error')
                    ->setError('data tidak ada dalam list')
                    ->setPromptTitle('Pilih kategori')
                    ->setPrompt('Pilih kategori dari list')
                    ->setFormula1('"'.implode(',', $subCategoryOptions).'"');

                $amountValidation = $spreadsheet->getCell('E2')->getDataValidation();
                $amountValidation->setType(DataValidation::TYPE_WHOLE)
                    ->setErrorStyle(DataValidation::STYLE_STOP)
                    ->setAllowBlank(false)
                    ->setShowInputMessage(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Input Error')
                    ->setError('Hanya bisa diisi angka')
                    ->setPromptTitle('Validasi Sales')
                    ->setPrompt('Hanya bisa diisi angka');

                for ($row = 2; $row <= $lengthValidationRow; $row++) {
                    $spreadsheet->getCell('A'.$row)->setDataValidation(clone $dateValidation);
                    $spreadsheet->getCell('B'.$row)->setDataValidation(clone $typeValidation);
                    $spreadsheet->getCell('C'.$row)->setDataValidation(clone $categoryValidation);
                    $spreadsheet->getCell('D'.$row)->setDataValidation(clone $subCategoryValidation);
                    $spreadsheet->getCell('E'.$row)->setDataValidation(clone $amountValidation);
                }
            },
        ];
    }
}
