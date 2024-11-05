<?php

namespace App\Domain\Talent\BLL\Talent;

use App\DomainUtils\BaseBLL\BaseBLLInterface;
use App\Domain\Talent\Models\Talent;
use Illuminate\Http\Request;

interface TalentBLLInterface extends BaseBLLInterface
{
    public function getAllTalentsWithContent();
    public function createTalent(array $data);
    public function updateTalent(Talent $talent, array $data);
    public function deleteTalent(int $id);
    public function calculateFinancials(array $data): array;
    public function getTalentById(int $id);
    public function handleTalentImport($file);
}
