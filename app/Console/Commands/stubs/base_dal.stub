<?php

namespace App\DomainUtils\BaseDAL;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

abstract class BaseDAL
{
    public $model;

    public function find($id)
    {
        return $this->model->findOrFail($id);
    }

    public function get()
    {
        return $this->model->all();
    }

    public function query()
    {
        return $this->model->query();
    }

    public function getByColumns($columns)
    {
        return $this->getByColumnsQuery($columns)->get();
    }

    public function getByColumnsQuery($columns)
    {
        $query = $this->model;

        foreach ($columns as $key => $val) {
            if (is_array($val)) {
                $query = $query->whereIn($key, $val);

                unset($columns[$key]);
            }
        }

        return $query->where($columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function insert(array $data)
    {
        return $this->model->insert($data);
    }

    public function update($model, array $data)
    {
        return $model->update($data);
    }

    public function updateBulk(array $ids, array $data)
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function updateBulkValues(array $values, $column)
    {
        $table = $this->model->getTable();

        $cases = [];
        $ids = [];
        $params = [];

        foreach ($values as $id => $value) {
            $id = (int) $id;
            $cases[] = "WHEN {$id} then ?";
            $params[] = $value;
            $ids[] = $id;
        }

        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);
        $params[] = Carbon::now();

        return DB::update("UPDATE `{$table}` SET `{$column}` = CASE `id` {$cases} END, `updated_at` = ? " .
            " WHERE `id` in ({$ids})", $params);
    }

    public function save($model)
    {
        return $model->save();
    }

    public function delete($model)
    {
        return $model->delete();
    }
}
