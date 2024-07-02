<?php

namespace App\DomainUtils\BaseDAL;

interface BaseDALInterface
{
    public function find($id);

    public function get();

    public function query();

    public function getByColumns($columns);

    public function getByColumnsQuery($columns);

    public function create(array $data);

    public function insert(array $data);

    public function update($model, array $data);

    public function updateBulk(array $ids, array $data);

    public function updateBulkValues(array $values, $column);

    public function save($model);

    public function delete($model);
}
