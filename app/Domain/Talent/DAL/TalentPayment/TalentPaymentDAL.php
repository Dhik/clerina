<?php

namespace App\Domain\Talent\DAL\TalentPayment;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\Talent\Models\TalentPayment;

/**
 * @property TalentPayment model
 */
class TalentPaymentDAL extends BaseDAL implements TalentPaymentDALInterface
{
    public function __construct(TalentPayment $talentPayments)
    {
        $this->model = $talentPayments;
    }

    // You can add specific data access methods related to talent payments here
}
