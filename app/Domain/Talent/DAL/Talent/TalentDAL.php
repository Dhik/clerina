<?php

namespace App\Domain\Talent\DAL\Talent;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Talent\Models\Talent;
use Illuminate\Support\Facades\Auth;

/**
 * @property Talent model
 */
class TalentDAL extends BaseDAL implements TalentDALInterface
{
    public $model;
    public function __construct(Talent $talent)
    {
        $this->model = $talent;
    }
    public function getAllWithContent()
    {
        return $this->model
            ->select([
                'talents.id',
                'talents.username',
                'talents.talent_name',
                'talents.pengajuan_transfer_date',
                'talents.rate_final',
                'talents.slot_final',
                'talents.dp_amount'
            ])
            ->leftJoin('talent_content', 'talents.id', '=', 'talent_content.talent_id')
            // ->whereNotNull('talents.price_rate') 
            ->groupBy(
                'talents.id',
                'talents.username',
                'talents.talent_name',
                'talents.pengajuan_transfer_date',
                'talents.rate_final',
                'talents.slot_final',
                'talents.dp_amount'
            )
            ->selectRaw('COUNT(talent_content.id) as content_count')
            ->selectRaw('CONCAT(COUNT(talent_content.id), " / ", IFNULL(talents.slot_final, 0)) AS remaining')
            ->get();
    }

    public function create(array $data)
    {
        $data['tenant_id'] = Auth::user()->current_tenant_id;
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $talent = $this->model->findOrFail($id);
        return $talent->update($data);
    }

    public function delete($id)
    {
        return $this->model->findOrFail($id)->delete();
    }

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }
}
