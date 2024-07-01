<?php

namespace App\Domain\Campaign\Exports;

use App\Domain\Campaign\Models\Offer;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class OfferExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
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
        return Offer::query()
            ->with('campaign', 'createdBy', 'financedBy', 'approvedBy', 'keyOpinionLeader')
            ->when(!empty($this->campaignId), function ($q) {
                $q->where('campaign_id', $this->campaignId);
            });
    }

    public function map($row): array
    {
        return [
            ucfirst($row->status), // A
            $row->createdBy->name ?? '', // B
            $row->keyOpinionLeader->username ?? '', // C
            $row->rate_per_slot, // D
            $row->benefit, // E
            $row->negotiate, // F
            $row->acc_slot, // G
            $row->approvedBy->name ?? '', // H
            $row->rate_total_slot, // I
            $row->rate_final_slot, // J
            $row->discount, // K
            $row->npwp ? trans('labels.yes') : trans('labels.no'), // L
            $row->pph, // M
            $row->final_amount, // N
            $row->sign_url, // O
            $row->bank_account, // P
            $row->bank_name, // Q
            $row->bank_account_name, // R
            $row->nik, // S
            $row->transfer_status, // T
            $row->transfer_date, // U
        ];
    }

    public function title(): string
    {
        return trans('labels.offering');
    }

    public function headings(): array
    {
        return [
            trans('labels.status'), // A
            trans('labels.created_by'), // B
            trans('labels.username'), // C
            trans('labels.slot_rate'), // D
            trans('labels.benefit'), // E
            trans('labels.negotiate'), // F
            trans('labels.acc_slot'), // G
            trans('labels.updated_by'), // H
            trans('labels.rate_total_slot'), // I
            trans('labels.rate_final_slot'), // J
            trans('labels.discount'), // K
            trans('labels.npwp'), // L
            trans('labels.pph'), // M
            trans('labels.final_amount'), // N
            trans('labels.kol_sign'), // O
            trans('labels.bank_account'), // P
            trans('labels.bank_name'), // Q
            trans('labels.bank_account_name'), // R
            trans('labels.nik'), // S
            trans('labels.transfer_status'), // T
            trans('labels.transfer_date'), // U
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => self::CUSTOM_NUMBER,
            'G' => self::CUSTOM_NUMBER,
            'I' => self::CUSTOM_NUMBER,
            'J' => self::CUSTOM_NUMBER,
            'K' => self::CUSTOM_NUMBER,
            'M' => self::CUSTOM_NUMBER,
            'N' => self::CUSTOM_NUMBER
        ];
    }
}
