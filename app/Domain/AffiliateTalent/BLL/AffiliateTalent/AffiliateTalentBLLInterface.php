<?php

namespace App\Domain\AffiliateTalent\BLL\AffiliateTalent;
use App\DomainUtils\BaseBLL\BaseBLLInterface;

interface AffiliateTalentBLLInterface
{
    public function getAll();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
    public function find(int $id);
}