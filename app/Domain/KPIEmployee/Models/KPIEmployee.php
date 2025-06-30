<?php

namespace App\Domain\KPIEmployee\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class KPIEmployee extends Model
{
    protected $table = 'kpi_employee';
    
    protected $fillable = [
        'kpi',
        'employee_id',
        'department',
        'position',
        'method_calculation',
        'perspective',
        'data_source',
        'target',
        'actual',
        'bobot'
    ];

    protected $casts = [
        'target' => 'decimal:2',
        'actual' => 'decimal:2',
        'bobot' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
    /**
     * Get the KPI employees for this employee.
     */
    public function kpiEmployees()
    {
        return $this->hasMany(KPIEmployee::class, 'employee_id', 'employee_id');
    }
}