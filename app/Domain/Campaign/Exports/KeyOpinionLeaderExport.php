<?php

namespace App\Domain\Campaign\Exports;

use App\Domain\Campaign\Models\KeyOpinionLeader;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class KeyOpinionLeaderExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    private ?string $channel;
    private ?string $niche;
    private ?string $skinType;
    private ?string $skinConcern;
    private ?string $contentType;
    private ?int $pic;

    const CUSTOM_NUMBER = '#,##0';

    public function __construct()
    {
    }

    public function forChannel(?string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    public function forNiche(?string $niche): static
    {
        $this->niche = $niche;
        return $this;
    }

    public function forSkinType(?string $skinType): static
    {
        $this->skinType = $skinType;
        return $this;
    }

    public function forSkinConcern(?string $skinConcern): static
    {
        $this->skinConcern = $skinConcern;
        return $this;
    }

    public function forContentType(?string $contentType): static
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function forPic(?string $pic): static
    {
        $this->pic = $pic;
        return $this;
    }

    public function query()
    {
        return KeyOpinionLeader::query()
            ->with('picContact', 'createdBy')
            ->when(!empty($this->channel), function ($q) {
                $q->where('channel', $this->channel);
            })
            ->when(!empty($this->niche), function ($q) {
                $q->where('niche', $this->niche);
            })
            ->when(!empty($this->skinType), function ($q) {
                $q->where('skin_type', $this->skinType);
            })
            ->when(!empty($this->skinConcern), function ($q) {
                $q->where('skin_concern', $this->skinConcern);
            })
            ->when(!empty($this->contentType), function ($q) {
                $q->where('content_type', $this->contentType);
            })
            ->when(!empty($this->pic), function ($q) {
                $q->where('pic_contact', $this->pic);
            });
    }

    public function map($row): array
    {
        return [
            $row->channel,
            $row->username,
            $row->niche,
            $row->average_view,
            $row->skin_type,
            $row->skin_concern,
            $row->content_type,
            $row->rate,
            $row->picContact->name ?? '',
            $row->createdBy->name ?? '',
            $row->cpm,
            $row->name,
            $row->address,
            $row->phone_number,
            $row->bank_name,
            $row->bank_account,
            $row->bank_account_name,
            $row->npwp ? trans('labels.yes') : trans('labels.no'),
            $row->npwp_number,
            $row->nik,
            $row->notes,
            $row->product_delivery ? trans('labels.yes') : trans('labels.no'),
            $row->product
        ];
    }

    public function title(): string
    {
        return trans('labels.key_opinion_leader');
    }

    public function headings(): array
    {
        return [
            trans('labels.channel'), // A
            trans('labels.username'), // B
            trans('labels.niche'), // C
            trans('labels.average_view'), // D
            trans('labels.skin_type'), // E
            trans('labels.skin_concern'), // F
            trans('labels.content_type'), // G
            trans('labels.slot_rate'), // H
            trans('labels.pic_contact'), // I
            trans('labels.created_by'), // J
            trans('labels.cpm_short'), // K
            trans('labels.name'), // L
            trans('labels.address'), // M
            trans('labels.phone_number'), // N
            trans('labels.bank_name'), //O
            trans('labels.bank_account'), // P
            trans('labels.bank_account_name'), // Q
            trans('labels.npwp'), // R
            trans('labels.npwp_number'), // S
            trans('labels.nik'), // T
            trans('labels.notes'), // U
            trans('labels.product_delivery'), // V
            trans('labels.product'), // W
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => self::CUSTOM_NUMBER,
            'H' => self::CUSTOM_NUMBER,
            'K' => self::CUSTOM_NUMBER,
            'N' => '#0',
            'P' => '#0',
            'S' => '#0',
        ];
    }
}
