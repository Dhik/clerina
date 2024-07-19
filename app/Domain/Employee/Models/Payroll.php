<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'full_name',
        'gaji_pokok',
        'tunjangan_jabatan',
        'insentif_live',
        'insentif',
        'function',
        'BPJS'
    ];
}