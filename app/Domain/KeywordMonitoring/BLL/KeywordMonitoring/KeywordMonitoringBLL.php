<?php

namespace App\Domain\KeywordMonitoring\BLL\KeywordMonitoring;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\KeywordMonitoring\DAL\KeywordMonitoring\KeywordMonitoringDALInterface;

/**
 * @property KeywordMonitoringDALInterface DAL
 */
class KeywordMonitoringBLL extends BaseBLL implements KeywordMonitoringBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(KeywordMonitoringDALInterface $keywordMonitoringDAL)
    {
        $this->DAL = $keywordMonitoringDAL;
    }
    public function all()
    {
        return $this->DAL->all();
    }

    /**
     * Create a new keyword monitoring.
     *
     * @param array $data
     * @return KeywordMonitoring
     */
    public function create(array $data, $fileFields = null)
    {
        return $this->DAL->create($data);
    }

    /**
     * Update the specified keyword monitoring.
     *
     * @param KeywordMonitoring $keywordMonitoring
     * @param array $data
     * @return bool
     */
    public function update($model, array $data, $fileFields = null)
    {
        return $this->DAL->update($keywordMonitoring, $data);
    }

    /**
     * Delete the specified keyword monitoring.
     *
     * @param KeywordMonitoring $keywordMonitoring
     * @return bool|null
     * @throws \Exception
     */
    public function delete($model, $fileFields = null)
    {
        return $this->DAL->delete($model);
    }
}
