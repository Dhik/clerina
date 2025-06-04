<?php

namespace App\Domain\Campaign\BLL\KOL;

use App\Domain\Campaign\DAL\KOL\KeyOpinionLeaderDALInterface;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Requests\KeyOpinionLeaderRequest;
use DragonCode\Support\Helpers\Boolean;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Utilities\Request;

class KeyOpinionLeaderBLL implements KeyOpinionLeaderBLLInterface
{
    public function __construct(protected KeyOpinionLeaderDALInterface $kolDAL)
    {}

    /**
     * Return KOL datatable
     */
    public function getKOLDatatable(Request $request): Builder
    {
        $query = $this->kolDAL->getKOLDatatable();
        $query->where('type', 'affiliate');

        if (!is_null($request->channel)) {
            $query->where('channel', $request->channel);
        }

        if (!is_null($request->niche)) {
            $query->where('niche', $request->niche);
        }

        if (!is_null($request->skinType)) {
            $query->where('skin_type', $request->skinType);
        }

        if (!is_null($request->skinConcern)) {
            $query->where('skin_concern', $request->skinConcern);
        }

        if (!is_null($request->contentType)) {
            $query->where('content_type', $request->contentType);
        }

        if (!is_null($request->pic)) {
            $query->where('pic_contact', $request->pic);
        }

        // Status affiliate filter
        if (!is_null($request->statusAffiliate)) {
            if ($request->statusAffiliate === 'null') {
                $query->whereNull('status_affiliate');
            } else {
                $query->where('status_affiliate', $request->statusAffiliate);
            }
        }

        // Followers range filter
        if (!is_null($request->followersMin) && $request->followersMin !== '') {
            $query->where('followers', '>=', (int)$request->followersMin);
        }

        if (!is_null($request->followersMax) && $request->followersMax !== '') {
            $query->where('followers', '<=', (int)$request->followersMax);
        }

        return $query;
    }

    /**
     * Select kol by username
     */
    public function selectKOL(?string $username): Collection
    {
        return $this->kolDAL->selectKOL($username);
    }

    /**
     * Create a new Key Opinion Leader
     */
    public function storeKOL(KeyOpinionLeaderRequest $request): KeyOpinionLeader
    {
        $rate = $request->input('rate');
        $averageView = $request->input('average_view');

        $data = [
            'channel' => $request->input('channel'),
            'username' => $request->input('username'),
            'niche' => $request->input('niche'),
            'average_view' => $averageView,
            'skin_type' => $request->input('skin_type'),
            'skin_concern' => $request->input('skin_concern'),
            'content_type' => $request->input('content_type'),
            'rate' => $rate,
            'pic_contact' => $request->input('pic_contact'),
            'created_by' => Auth::user()->id,
            'cpm' => ceil(($rate/$averageView) * 1000),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone_number' => $request->input('phone_number'),
            'bank_name' => $request->input('bank_name'),
            'bank_account' => $request->input('bank_account'),
            'bank_account_name' => $request->input('bank_account_name'),
            'npwp' => (bool) $request->input('npwp'),
            'npwp_number' => $request->input('npwp_number'),
            'nik' => $request->input('nik'),
            'notes' => $request->input('notes'),
            'product_delivery' => (bool) $request->input('product_delivery'),
            'product' => $request->input('product'),
        ];

        return $this->kolDAL->storeKOL($data);
    }

    /**
     * Create a new Key Opinion Leader via excel input
     */
    public function storeExcel(array $arrayData): bool
    {
        try {
            DB::beginTransaction();

            foreach ($arrayData as $data) {
                $rate = $data[7];
                $averageView = $data[3];

                $preparedData = [
                    'channel' => $data[0],
                    'username' => $data[1],
                    'niche' => $data[2],
                    'average_view' => $averageView,
                    'skin_type' => $data[4],
                    'skin_concern' => $data[5],
                    'content_type' => $data[6],
                    'rate' => $rate,
                    'pic_contact' => $data[8],
                    'created_by' => Auth::user()->id,
                    'cpm' => ceil(($rate/$averageView) * 1000),
                    'name' => $data[9],
                    'address' => $data[10],
                    'phone_number' => $data[11],
                    'bank_name' => $data[12],
                    'bank_account' => $data[13],
                    'bank_account_name' => $data[14],
                    'npwp' => $data[15] === 'true' ? 1 : 0,
                    'npwp_number' => $data[16],
                    'nik' => $data[17],
                    'notes' => $data[18],
                    'product_delivery' => $data[19] === 'true' ? 1 : 0,
                    'product' => $data[20],
                ];

                $this->kolDAL->storeKOL($preparedData);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Error Input Excel: ' . $e);

            return false;
        }

        return true;
    }

    /**
     * Update Key Opinion Leader
     */
    public function updateKOL(KeyOpinionLeader $keyOpinionLeader, KeyOpinionLeaderRequest $request): KeyOpinionLeader
    {
        // Only include fields that are actually provided in the request
        $data = [];
        
        // Always update these fields if provided
        if ($request->has('username')) {
            $data['username'] = $request->input('username');
        }
        if ($request->has('phone_number')) {
            $data['phone_number'] = $request->input('phone_number');
        }
        if ($request->has('views_last_9_post')) {
            $data['views_last_9_post'] = $request->input('views_last_9_post');
        }
        if ($request->has('activity_posting')) {
            $data['activity_posting'] = $request->input('activity_posting');
        }
        
        // Only update other fields if they're provided (for full updates)
        if ($request->filled('channel')) {
            $data['channel'] = $request->input('channel');
        }
        if ($request->filled('niche')) {
            $data['niche'] = $request->input('niche');
        }
        if ($request->filled('average_view')) {
            $rate = $request->input('rate');
            $averageView = $request->input('average_view');
            $data['average_view'] = $averageView;
            if ($rate) {
                $data['cpm'] = ceil(($rate/$averageView) * 1000);
            }
        }
        if ($request->filled('skin_type')) {
            $data['skin_type'] = $request->input('skin_type');
        }
        if ($request->filled('skin_concern')) {
            $data['skin_concern'] = $request->input('skin_concern');
        }
        if ($request->filled('content_type')) {
            $data['content_type'] = $request->input('content_type');
        }
        if ($request->filled('rate')) {
            $data['rate'] = $request->input('rate');
        }
        if ($request->filled('pic_contact')) {
            $data['pic_contact'] = $request->input('pic_contact');
        }
        if ($request->filled('name')) {
            $data['name'] = $request->input('name');
        }
        if ($request->filled('address')) {
            $data['address'] = $request->input('address');
        }
        if ($request->filled('bank_name')) {
            $data['bank_name'] = $request->input('bank_name');
        }
        if ($request->filled('bank_account')) {
            $data['bank_account'] = $request->input('bank_account');
        }
        if ($request->filled('bank_account_name')) {
            $data['bank_account_name'] = $request->input('bank_account_name');
        }
        if ($request->has('npwp')) {
            $data['npwp'] = (bool) $request->input('npwp');
        }
        if ($request->filled('npwp_number')) {
            $data['npwp_number'] = $request->input('npwp_number');
        }
        if ($request->filled('nik')) {
            $data['nik'] = $request->input('nik');
        }
        if ($request->filled('notes')) {
            $data['notes'] = $request->input('notes');
        }
        if ($request->has('product_delivery')) {
            $data['product_delivery'] = (bool) $request->input('product_delivery');
        }
        if ($request->filled('product')) {
            $data['product'] = $request->input('product');
        }
        if ($request->filled('followers')) {
            $data['followers'] = $request->input('followers');
        }

        // Update the KOL first
        $updatedKol = $this->kolDAL->updateKOL($keyOpinionLeader, $data);
        
        // Check if we need to update status_affiliate based on the new criteria
        $this->updateAffiliateStatus($updatedKol);
        
        return $updatedKol;
    }

    private function updateAffiliateStatus(KeyOpinionLeader $kol): void
    {
        // Check if this KOL meets the qualification criteria
        $meetsQualification = $kol->views_last_9_post == 1 && 
                            $kol->activity_posting == 1 && 
                            $kol->followers > 500;
        
        if ($meetsQualification) {
            // Count current qualified KOLs
            $qualifiedCount = KeyOpinionLeader::where('status_affiliate', 'Qualified')->count();
            
            if ($qualifiedCount < 1000) {
                // Still room for more qualified KOLs
                $kol->update(['status_affiliate' => 'Qualified']);
            } else {
                // Check if this KOL has more followers than the lowest qualified KOL
                $lowestQualifiedKol = KeyOpinionLeader::where('status_affiliate', 'Qualified')
                    ->orderBy('followers', 'asc')
                    ->first();
                
                if ($kol->followers > $lowestQualifiedKol->followers) {
                    // Promote this KOL to qualified and demote the lowest one
                    $lowestQualifiedKol->update(['status_affiliate' => 'Waiting List']);
                    $kol->update(['status_affiliate' => 'Qualified']);
                } else {
                    // This KOL goes to waiting list
                    $kol->update(['status_affiliate' => 'Waiting List']);
                }
            }
        } else {
            // KOL doesn't meet qualification criteria
            if ($kol->status_affiliate === 'Qualified' || $kol->status_affiliate === 'Waiting List') {
                $kol->update(['status_affiliate' => 'Not Qualified']);
                
                // If this was a qualified KOL, promote the next best from waiting list
                if ($kol->status_affiliate === 'Qualified') {
                    $nextBestWaitingKol = KeyOpinionLeader::where('status_affiliate', 'Waiting List')
                        ->where('views_last_9_post', 1)
                        ->where('activity_posting', 1)
                        ->where('followers', '>', 500)
                        ->orderBy('followers', 'desc')
                        ->first();
                        
                    if ($nextBestWaitingKol) {
                        $nextBestWaitingKol->update(['status_affiliate' => 'Qualified']);
                    }
                }
            }
        }
    }
}
