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
    private ?string $statusAffiliate;
    private ?int $followersMin;
    private ?int $followersMax;

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

    public function forStatusAffiliate(?string $statusAffiliate): static
    {
        $this->statusAffiliate = $statusAffiliate;
        return $this;
    }

    public function forFollowersRange(?int $followersMin, ?int $followersMax): static
    {
        $this->followersMin = $followersMin;
        $this->followersMax = $followersMax;
        return $this;
    }

    public function query()
    {
        return KeyOpinionLeader::query()
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
            })
            ->when(!empty($this->statusAffiliate), function ($q) {
                if ($this->statusAffiliate === 'null') {
                    $q->whereNull('status_affiliate');
                } else {
                    $q->where('status_affiliate', $this->statusAffiliate);
                }
            })
            ->when(!empty($this->followersMin), function ($q) {
                $q->where('followers', '>=', $this->followersMin);
            })
            ->when(!empty($this->followersMax), function ($q) {
                $q->where('followers', '<=', $this->followersMax);
            });
    }

    public function map($row): array
    {
        return [
            $row->channel,
            $row->username,
            $row->phone_number,
            $row->followers,
            $row->following,
            $row->total_likes,
            $row->video_count,
            $this->formatEngagementRate($row->engagement_rate),
            $this->formatBooleanField($row->views_last_9_post),
            $this->formatBooleanField($row->activity_posting),
            $row->status_affiliate
        ];
    }

    private function formatBooleanField($value): string
    {
        if ($value === null) {
            return 'Not Set';
        }
        return $value ? trans('labels.yes') : trans('labels.no');
    }

    private function formatEngagementRate($value): string
    {
        if ($value === null) {
            return 'N/A';
        }
        // If the value is already a percentage (e.g., 15.71), format it properly
        // If it's a decimal (e.g., 0.1571), convert it to percentage
        if ($value > 1) {
            // Already a percentage, just add % symbol
            return number_format($value, 2) . '%';
        } else {
            // Convert decimal to percentage
            return number_format($value * 100, 2) . '%';
        }
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
            trans('labels.phone_number'), // C
            'Followers', // D
            'Following', // E
            'Total Likes', // F
            'Video Count', // G
            'Engagement Rate', // H
            'Recent Views', // I
            'Activity Status', // J
            'Affiliate Status', // K
        ];
    }

    public function columnFormats(): array
    {
        return [
            'C' => '#0', // Phone number
            'D' => self::CUSTOM_NUMBER, // Followers
            'E' => self::CUSTOM_NUMBER, // Following
            'F' => self::CUSTOM_NUMBER, // Total likes
            'G' => self::CUSTOM_NUMBER, // Video count
            'H' => '@', // Engagement rate as text (since we're formatting it manually)
        ];
    }
}