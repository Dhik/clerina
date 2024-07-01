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
        $grouped = $collection->groupBy('username');
        $importedUsername = $grouped->keys()->toArray();

        $kol = $this->getKol($campaign->id);

        $this->validateKolExistence($campaign->id, $importedUsername, $kol);
        $this->validateKolSlots($campaign->id, $data);

        $this->save($collection, $campaign, $kol);

        return 'OK';
    }

    protected function save(Collection $collections, Campaign $campaign, Collection $kol): void
    {
        try {
            DB::beginTransaction();
            foreach ($collections as $data) {
                CampaignContent::create([
                    'campaign_id' => $campaign->id,
                    'key_opinion_leader_id' => $kol->where('username', $data['username'])->first()->id,
                    'channel' => $data['channel'],
                    'task_name' => $data['task_name'],
                    'link' => $data['link'],
                    'rate_card' => $data['rate_card'],
                    'product' => $data['product'],
                    'created_by' => Auth::user()->id
                ]);
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
