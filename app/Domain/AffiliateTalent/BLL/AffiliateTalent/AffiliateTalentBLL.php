<?php

namespace App\Domain\AffiliateTalent\BLL\AffiliateTalent;

use App\Domain\AffiliateTalent\Models\AffiliateTalent;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\AffiliateTalent\DAL\AffiliateTalent\AffiliateTalentDALInterface;

/**
 * @property AffiliateTalentDALInterface DAL
 */
class AffiliateTalentBLL implements AffiliateTalentBLLInterface
{
    public function getAll()
    {
        return AffiliateTalent::with(['salesChannel', 'tenant'])->paginate(10);
    }

    public function create(array $data)
    {
        return AffiliateTalent::create($data);
    }

    public function update(int $id, array $data)
    {
        $affiliateTalent = $this->find($id);
        $affiliateTalent->update($data);
        return $affiliateTalent;
    }

    public function delete(int $id)
    {
        return AffiliateTalent::destroy($id);
    }

    public function find(int $id)
    {
        return AffiliateTalent::findOrFail($id);
    }
}