<?php

namespace App\Domain\KeywordMonitoring\DAL\KeywordMonitoring;

use App\DomainUtils\BaseDAL\BaseDALInterface;

interface KeywordMonitoringDALInterface extends BaseDALInterface
{
    public function all();

    public function create(array $data);

    public function update($model, array $data);

    public function delete($model);
}
