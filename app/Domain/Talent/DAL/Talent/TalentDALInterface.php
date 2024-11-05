<?php

namespace App\Domain\Talent\DAL\Talent;

use App\DomainUtils\BaseDAL\BaseDALInterface;

interface TalentDALInterface extends BaseDALInterface
{
    public function getAllWithContent();
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function find($id);
}
