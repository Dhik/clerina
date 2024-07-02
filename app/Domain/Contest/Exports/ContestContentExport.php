<?php

namespace App\Domain\Contest\Exports;

use App\Domain\Contest\Models\ContestContent;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContestContentExport implements FromCollection, WithHeadings, WithMapping
{
    protected $contestId;

    public function __construct($contestId)
    {
        $this->contestId = $contestId;
    }

    public function collection()
    {
        // Fetch all records and group them by username
        $collection = ContestContent::where('contest_id', $this->contestId)->get();
        $grouped = $collection->groupBy('username');
        $result = collect();

        foreach ($grouped as $username => $items) {
            foreach ($items as $item) {
                $result->push($item);
            }

            // Calculate totals for each username
            $totals = [
                'username' => 'Total for ' . $username,
                'link' => '',
                'view' => $items->sum('view'),
                'like' => $items->sum('like'),
                'comment' => $items->sum('comment'),
                'share' => $items->sum('share'),
                'interaction' => number_format($items->avg('interaction'), 2),
                'value_in_rupiahs' => 'Rp. ' . number_format($items->sum('view') * 10, 0, ',', '.')
            ];
            $result->push((object) $totals); // Convert totals array to object
        }

        // Calculate overall totals
        $overallTotals = [
            'username' => 'Overall Total',
            'link' => '',
            'view' => $collection->sum('view'),
            'like' => $collection->sum('like'),
            'comment' => $collection->sum('comment'),
            'share' => $collection->sum('share'),
            'interaction' => number_format($collection->avg('interaction'), 2),
            'value_in_rupiahs' => 'Rp. ' . number_format($collection->sum('view') * 10, 0, ',', '.')
        ];
        $result->push((object) $overallTotals); // Add overall totals to the collection

        return $result;
    }

    public function headings(): array
    {
        return [
            'Username',
            'Link',
            'Views',
            'Likes',
            'Comments',
            'Shares',
            'Interaction',
            'Value in Rupiahs'
        ];
    }

    public function map($row): array
    {
        // Check if row is an instance of ContestContent or an object (for totals)
        if ($row instanceof ContestContent) {
            return [
                $row->username,
                $row->link,
                $row->view,
                $row->like,
                $row->comment,
                $row->share,
                $row->interaction,
                'Rp. ' . number_format($row->view * 10, 0, ',', '.')
            ];
        } else {
            return [
                $row->username,
                $row->link,
                $row->view,
                $row->like,
                $row->comment,
                $row->share,
                $row->interaction,
                $row->value_in_rupiahs
            ];
        }
    }
}
