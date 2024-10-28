<?php

namespace App\Domain\Campaign\Service;

use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Import\ContentImport;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\CampaignContent;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\Offer;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Utilities\Request;

class CampaignImportService
{
    /**
     * Import content
     *
     * @throws Exception
     */
    public function importContent(Request $request, int $tenantId, Campaign $campaign): string
    {
        $import = new ContentImport();
        Excel::import($import, $request->file('fileContentImport'));

        $data = $import->getImportedData();
        $collection = collect($data);

        $this->save($collection, $campaign);

        return 'OK';
    }

    public function storeContent(Request $request, int $tenantId, Campaign $campaign): string
    {
        $collection = collect($data);
        $this->save($collection, $campaign);

        return 'OK';
    }

    protected function save(Collection $collections, Campaign $campaign): void
    {
        try {
            DB::beginTransaction();
            foreach ($collections as $data) {
                // Create or update the KeyOpinionLeader based on the username
                // Check if the record exists
                $existingKOL = KeyOpinionLeader::where('username', $data['username'])->first();

                if ($existingKOL) {
                    // If the record exists, update it without modifying average_view and cpm
                    $existingKOL->update([
                        'channel' => $data['channel'],
                        'rate' => $data['rate_card'],
                        'created_by' => Auth::user()->id,
                        'pic_contact' => Auth::user()->id,
                    ]);
                    $kol = $existingKOL;
                } else {
                    // If the record does not exist, create a new one with average_view and cpm set to 0
                    $kol = KeyOpinionLeader::create([
                        'username' => $data['username'],
                        'channel' => $data['channel'],
                        'niche' => '-',
                        'average_view' => 0,
                        'skin_type' => '-',
                        'skin_concern' => '-',
                        'content_type' => '-',
                        'rate' => $data['rate_card'],
                        'cpm' => 0,
                        'created_by' => Auth::user()->id,
                        'pic_contact' => Auth::user()->id,
                    ]);
                }


                // Create or update the campaign content
                CampaignContent::updateOrCreate(
                    [
                        'link' => $data['link'],
                        'campaign_id' => $campaign->id,
                    ],
                    [
                        'channel' => $data['channel'],
                        'username' => $data['username'],
                        'key_opinion_leader_id' => $kol->id,
                        'task_name' => $data['task_name'],
                        'rate_card' => $data['rate_card'],
                        'product' => $data['product'],
                        'kode_ads' => $data['kode_ads'],
                        'created_by' => Auth::user()->id,
                    ]
                );

            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error Import: ' . $e);
            throw $e;
        }
    }

    protected function getKol(int $campaignId)
    {
        return KeyOpinionLeader::whereHas('offers', function ($query) use ($campaignId) {
            $query->where('campaign_id', $campaignId)
                ->where('status', OfferEnum::Approved);
        })->get();
    }

    protected function validateKolExistence(int $campaignId, array $importedUsernames, Collection $kol): void
    {
        $registeredUsernames = $kol->pluck('username')->toArray();

        $nonexistentUsernames = array_diff($importedUsernames, $registeredUsernames);

        if (empty($nonexistentUsernames)) {
            return;
        }

        $errorMessage = trans('messages.kol_not_exist_import') . $this->formatAsList($nonexistentUsernames);
        $this->throwValidationException('nonexistent_usernames', $errorMessage);
    }

    protected function validateKolSlots(int $campaignId, $importedData)
    {
        $totalSlots = $this->getTotalSlots($campaignId);
        $usedSlots = $this->getUsedSlots($campaignId);

        $remainingSlots = $this->calculateRemainingSlots($importedData, $totalSlots, $usedSlots);

        if (empty($remainingSlots)) {
            return;
        }

        $errorMessage = trans('messages.kol_doesnt_have_enough_slot') . $this->formatRemainingSlots($remainingSlots);
        $this->throwValidationException('kol_doesnt_have_enough_slot', $errorMessage);
    }

    protected function formatAsList(array $items): string
    {
        $list = '<ul>';
        foreach ($items as $item) {
            $list .= '<li>' . $item . '</li>';
        }
        $list .= '</ul>';
        return $list;
    }

    protected function getTotalSlots(int $campaignId)
    {
        return Offer::where('campaign_id', $campaignId)
            ->select('key_opinion_leader_id')
            ->selectRaw('SUM(acc_slot) as total_acc_slot')
            ->with('keyOpinionLeader:id,username')
            ->groupBy('key_opinion_leader_id')
            ->get()
            ->keyBy('keyOpinionLeader.username')
            ->map->total_acc_slot;
    }

    protected function getUsedSlots(int $campaignId)
    {
        return CampaignContent::where('campaign_id', $campaignId)
            ->select('key_opinion_leader_id')
            ->selectRaw('COUNT(id) as used_slot')
            ->with('keyOpinionLeader:id,username')
            ->groupBy('key_opinion_leader_id')
            ->get()
            ->keyBy('keyOpinionLeader.username')
            ->map->used_slot;
    }

    protected function calculateRemainingSlots($importedData, $totalSlots, $usedSlots)
    {
        $usernameCounts = collect($importedData)->pluck('username')->countBy();

        return $totalSlots->map(function ($total, $username) use ($usedSlots, $usernameCounts) {
            $used = $usedSlots->get($username, 0);
            $remaining = $total - $used;

            if ($usernameCounts->has($username) && $usernameCounts[$username] > $remaining) {
                return [
                    'username' => $username,
                    'acc_slot' => $total,
                    'remaining_slot' => $remaining,
                    'requested_slot' => $usernameCounts[$username]
                ];
            }
        })->filter()->values()->all();
    }

    protected function formatRemainingSlots(array $remainingSlots): string
    {
        $list = '<ul>';
        foreach ($remainingSlots as $kol) {
            $list .= '<li>' . $kol['username'] . ': Request Import ' . $kol['requested_slot'] . ' - Sisa Slot ' . $kol['remaining_slot'] . '</li>';
        }
        $list .= '</ul>';
        return $list;
    }

    protected function throwValidationException($field, $message)
    {
        $validator = Validator::make([], []);
        $validator->errors()->add($field, $message);
        throw new ValidationException($validator);
    }
}
