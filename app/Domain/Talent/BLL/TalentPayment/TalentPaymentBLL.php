<?php

namespace App\Domain\Talent\BLL\TalentPayment;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\Domain\Talent\DAL\TalentPayments\TalentPaymentDALInterface;

/**
 * @property TalentPaymentDALInterface DAL
 */
class TalentPaymentsBLL extends BaseBLL implements TalentPaymentBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(TalentPaymentDALInterface $talentPaymentsDAL)
    {
        $this->DAL = $talentPaymentsDAL;
    }

    // You can add specific methods for handling business logic related to talent payments here
}
