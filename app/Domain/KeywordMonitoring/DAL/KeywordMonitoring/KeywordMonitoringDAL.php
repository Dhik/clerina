<?php

namespace App\Domain\KeywordMonitoring\DAL\KeywordMonitoring;

use App\DomainUtils\BaseDAL\BaseDAL;
use App\Domain\KeywordMonitoring\Models\KeywordMonitoring;

/**
 * @property KeywordMonitoring model
 */
class KeywordMonitoringDAL extends BaseDAL implements KeywordMonitoringDALInterface
{
    public function __construct(KeywordMonitoring $keywordMonitoring)
    {
        $this->model = $keywordMonitoring;
    }
    public function all()
    {
        return KeywordMonitoring::all();
    }

    public function create(array $data)
    {
        return KeywordMonitoring::create($data);
    }

    public function update($model, array $data)
    {
        return $keywordMonitoring->update($data);
    }

    public function delete($model)
    {
        $model->postings()->delete(); // Delete related postings
        return $model->delete();
    }
}
