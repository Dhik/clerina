<?php

namespace App\Domain\KeywordMonitoring\BLL\KeywordMonitoring;

use App\DomainUtils\BaseBLL\BaseBLLInterface;

interface KeywordMonitoringBLLInterface extends BaseBLLInterface
{
    /**
     * Retrieve all keyword monitorings.
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all();
    public function create(array $data, $fileFields = null);
    public function update($model, array $data, $fileFields = null);
    public function delete($model, $fileFields = null);
}
