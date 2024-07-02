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

        return $this->kolDAL->updateKOL($keyOpinionLeader, $data);
    }
}
