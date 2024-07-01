<?php

namespace App\DomainUtils\BaseBLL;

interface BaseBLLInterface
{
    public function find($id);

    public function get();

    public function query();

    public function getByColumns($columns);

    public function getByColumnsQuery($columns);

    public function create(array $data, $fileFields = null);

    public function insert(array $data);

    public function update($model, array $data, $fileFields = null);

    public function updateBulk(array $ids, array $data);

    public function updateBulkValues(array $values, $column);

    public function delete($model, $fileFields = null);
}
