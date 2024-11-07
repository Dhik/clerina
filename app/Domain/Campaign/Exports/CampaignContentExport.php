<?php

namespace App\Domain\Campaign\Exports;

use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\Offer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CampaignContentExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
{
    use Exportable;
    private ?string $campaignId;

    const CUSTOM_NUMBER = '#,##0';

    public function forCampaign(?string $campaignId): static
    {
        $this->campaignId = $campaignId;
        return $this;
    }

    public function query()
    {
        return CampaignContent::query()
            ->with('latestStatistic', 'keyOpinionLeader')
            ->when(!empty($this->campaignId), function ($q) {
                $q->where('campaign_id', $this->campaignId);
            });
    }

    public function map($row): array
    {
        $viewCount = $row->latestStatistic->view ?? 0;
        $likeCount = $row->latestStatistic->like ?? 0;
        $commentCount = $row->latestStatistic->comment ?? 0;
        return [
            $row->username ?? '', // A
            $row->channel, // B
            $row->link, // C
            $row->product, // D
            $row->upload_date, // E
            $row->latestStatistic->view ?? 0, // F
            $row->latestStatistic->like ?? 0, // G
            $row->latestStatistic->comment ?? 0, // H
            $this->countCPM($row->rate_card, $row->latestStatistic->view ?? 0) , // I
            $this->calculateER($likeCount, $commentCount, $viewCount),
            $row->rate_card, // J
            $row->is_paid ? trans('labels.paid') : trans('labels.unpaid'), // K
            $row->keyOpinionLeader->bank_name, // L
            $row->kode_ads,
        ];
    }

    protected function countCPM($rateCard, $view): float|int
    {
        if ($view === 0) {
            return 0;
        }

        return ($rateCard / $view) * 1000;
    }
    protected function calculateER($likeCount, $commentCount, $viewCount): float|int
    {
        if ($viewCount === 0) {
            return 0;
        }

        return (($likeCount + $commentCount) / $viewCount) * 100; // ER in percentage
    }

    public function title(): string
    {
        return trans('labels.offering');
    }

    public function headings(): array
    {
        return [
            trans('labels.username'), // A
            trans('labels.platform'), // B
            trans('labels.link'), // C
            trans('labels.product'), // D
            trans('labels.post_date'), // E
            trans('labels.view'), // F
            trans('labels.like'), // G
            trans('labels.comment'), // H
            trans('labels.cpm'), // I
            trans('labels.engagement_rate'), // J
            trans('labels.rate_card'), // K
            trans('labels.payment'), // L
            trans('labels.bank_name'), // M
            trans('labels.kode_ads'), // N
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => self::CUSTOM_NUMBER,
            'G' => self::CUSTOM_NUMBER,
            'H' => self::CUSTOM_NUMBER,
            'I' => self::CUSTOM_NUMBER,
            'K' => self::CUSTOM_NUMBER,
            'J' => '0.00',
        ];
    }
}
