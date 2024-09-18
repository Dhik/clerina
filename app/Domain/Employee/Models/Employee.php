<?php

namespace App\Domain\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_id', 'full_name', 'email', 'barcode', 'organization',
        'job_position', 'job_level', 'join_date', 'resign_date', 'status_employee',
        'end_date', 'sign_date', 'birth_date', 'age', 'birth_place', 'citizen_id_address',
        'residential_address', 'npwp', 'ptkp_status', 'employee_tax_status', 'tax_config',
        'bank_name', 'bank_account', 'bank_account_holder', 'bpjs_ketenagakerjaan',
        'bpjs_kesehatan', 'nik_npwp_16_digit', 'mobile_phone', 'phone', 'branch_name',
        'parent_branch_name', 'religion', 'gender', 'marital_status', 'blood_type',
        'nationality_code', 'currency', 'length_of_service', 'payment_schedule',
        'approval_line', 'manager', 'grade', 'class', 'profile_picture', 'cost_center',
        'cost_center_category', 'sbu', 'npwp_16_digit', 'passport', 'passport_expiration_date', 'shift_id',
        'kk', 'ktp', 'cv', 'ijazah', 'place_id'
    ];
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($employee) {
            $employee->attendances()->delete();
        });
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
