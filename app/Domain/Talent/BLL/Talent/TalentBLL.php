<?php

namespace App\Domain\Talent\BLL\Talent;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Talent\DAL\Talent\TalentDALInterface;
use App\Domain\Talent\Models\Talent;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Talent\Import\TalentImport;

/**
 * @property TalentDALInterface DAL
 */
class TalentBLL extends BaseBLL implements TalentBLLInterface
{
    use BaseBLLFileUtils;
    protected $DAL;

    public function __construct(TalentDALInterface $talentDAL)
    {
        $this->DAL = $talentDAL;
    }
    public function getAllTalentsWithContent()
    {
        return $this->DAL->getAllWithContent();
    }

    public function createTalent(array $data)
    {
        $financials = $this->calculateFinancials($data);
        $data = array_merge($data, $financials);
        return $this->DAL->create($data);
    }

    public function updateTalent(Talent $talent, array $data)
    {
        $financials = $this->calculateFinancialsUpdate($data);
        $data = array_merge($data, $financials);
        return $this->DAL->update($talent->id, $data);
    }

    public function deleteTalent(int $id)
    {
        return $this->DAL->delete($id);
    }

    public function calculateFinancials(array $data): array
    {
        $priceRate = (int) str_replace(['Rp', '.', ' '], '', $data['price_rate'] ?? 0);
        $rateFinal = (int) str_replace(['Rp', '.', ' '], '', $data['rate_final'] ?? 0);
        $slotFinal = $data['slot_final'] ?? 1;

        $ratePerSlot = $slotFinal > 0 ? $rateFinal / $slotFinal : 0;
        $discount = ($priceRate * $slotFinal) - $rateFinal;

        $isPTorCV = Str::startsWith($data['talent_name'], ['PT', 'CV']);
        $taxRate = $isPTorCV ? 0.02 : 0.025;
        $taxDeduction = (int) ($rateFinal * $taxRate);
        $finalTransfer = $rateFinal - $taxDeduction;
        return [
            'rate_per_slot' => $ratePerSlot,
            'discount' => $discount,
            'tax_deduction' => $taxDeduction,
            'final_transfer' => $finalTransfer,
        ];
    }

    public function calculateFinancialsUpdate(array $data): array
    {
        $priceRate = (int) str_replace(['Rp', '.', ' '], '', $data['price_rate'] ?? 0);
        $rateFinal = (int) str_replace(['Rp', '.', ' '], '', $data['rate_final'] ?? 0);
        $slotFinal = $data['slot_final'] ?? 0;

        $discount = ($priceRate * $slotFinal) - $rateFinal;

        $isPTorCV = Str::startsWith($data['talent_name'], ['PT', 'CV']);
        $taxRate = $isPTorCV ? 0.02 : 0.025;
        $taxDeduction = (int) ($rateFinal * $taxRate);
        $finalTransfer = $rateFinal - $taxDeduction;
        return [
            'discount' => $discount,
            'tax_deduction' => $taxDeduction,
            'final_transfer' => $finalTransfer,
        ];
    }

    public function getTalentById(int $id)
    {
        return $this->DAL->find($id);
    }

    public function handleTalentImport($file)
    {
        return Excel::import(new TalentImport, $file);
    }
}
