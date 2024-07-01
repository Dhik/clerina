<?php

namespace App\DomainUtils\BaseBLL;

/**
 * @method BaseBLLFileUtils prepareFileFields(array $data, $fileFields, $model = null)
 * @method BaseBLLFileUtils deleteFilesIfExist($model, $fileFields)
 */
abstract class BaseBLL
{
    public $dal;

    public function find($id)
    {
        return $this->dal->find($id);
    }

    public function get()
    {
        return $this->dal->get();
    }

    public function query()
    {
        return $this->dal->query();
    }

    public function getByColumns($columns)
    {
        return $this->dal->getByColumns($columns);
    }

    public function getByColumnsQuery($columns)
    {
        return $this->dal->getByColumnsQuery($columns);
    }

    public function create(array $data, $fileFields = null)
    {
        if ($fileFields && method_exists($this, 'prepareFileFields')) {
            $this->prepareFileFields($data, $fileFields);
        }

        return $this->dal->create($data);
    }

    public function insert(array $data)
    {
        return $this->dal->insert($data);
    }

    public function update($model, array $data, $fileFields = null)
    {
        if ($fileFields && method_exists($this, 'prepareFileFields')) {
            $this->prepareFileFields($data, $fileFields, $model);
        }

        return $this->dal->update($model, $data);
    }

    public function updateBulk(array $ids, array $data)
    {
        return $this->dal->updateBulk($ids, $data);
    }

    public function updateBulkValues(array $values, $column)
    {
        return $this->dal->updateBulkValues($values, $column);
    }

    public function delete($model, $fileFields = null)
    {
        if ($fileFields && method_exists($this, 'deleteFilesIfExist')) {
            $this->deleteFilesIfExist($model, $fileFields);
        }

        return $this->dal->delete($model);
    }
}
