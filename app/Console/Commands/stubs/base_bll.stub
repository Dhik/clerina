<?php

namespace App\DomainUtils\BaseBLL;

/**
 * @method BaseBLLFileUtils prepareFileFields(array $data, $fileFields, $model = null)
 * @method BaseBLLFileUtils deleteFilesIfExist($model, $fileFields)
 */
abstract class BaseBLL
{
    public $DAL;

    public function find($id)
    {
        return $this->DAL->find($id);
    }

    public function get()
    {
        return $this->DAL->get();
    }

    public function query()
    {
        return $this->DAL->query();
    }

    public function getByColumns($columns)
    {
        return $this->DAL->getByColumns($columns);
    }

    public function getByColumnsQuery($columns)
    {
        return $this->DAL->getByColumnsQuery($columns);
    }

    public function create(array $data, $fileFields = null)
    {
        if ($fileFields && function_exists('prepareFileFields')) {
            $this->prepareFileFields($data, $fileFields);
        }

        return $this->DAL->create($data);
    }

    public function insert(array $data)
    {
        return $this->DAL->insert($data);
    }

    public function update($model, array $data, $fileFields = null)
    {
        if ($fileFields && function_exists('prepareFileFields')) {
            $this->prepareFileFields($data, $fileFields, $model);
        }

        return $this->DAL->update($model, $data);
    }

    public function updateBulk(array $ids, array $data)
    {
        return $this->DAL->updateBulk($ids, $data);
    }

    public function updateBulkValues(array $values, $column)
    {
        return $this->DAL->updateBulkValues($values, $column);
    }

    public function delete($model, $fileFields = null)
    {
        if ($fileFields && function_exists('deleteFilesIfExist')) {
            $this->deleteFilesIfExist($model, $fileFields);
        }

        return $this->DAL->delete($model);
    }
}
