<?php

namespace App\Domain\Campaign\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CampaignContentTemplateExport implements ShouldAutoSize, WithHeadings, WithTitle, WithStyles
{
    const REQUIRED_COLOR = '3CB371';

    public function headings(): array
    {
        return [
            trans('labels.username'),
            trans('labels.task'),
            trans('labels.platform'). ' (instagram_feed/tiktok_video/instagram_story/tiktok_live/youtube_video/twitter_post/shopee_video) CASE SENSITIVE',
            trans('labels.link'),
            trans('labels.rate_card'),
            trans('labels.product'),
            trans('labels.kode_ads')
        ];
    }

    public function styles(Worksheet $sheet): void
    {
        $arrayColumns = [
            'A', 'B', 'C', 'E', 'F'
        ];

        foreach ($arrayColumns as $column) {
            $this->applyStyles($sheet, $column);
        }
    }

    protected function applyStyles(Worksheet $sheet, string $column): void
    {
        $sheet->getStyle($column.':'.$column)->applyFromArray([
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['rgb' => self::REQUIRED_COLOR],
            ],
        ]);
    }

    public function title(): string
    {
        return 'Campaign';
    }
}
