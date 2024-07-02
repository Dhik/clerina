<?php

namespace App\Domain\Employee\BLL\Employee;

use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use App\Domain\Employee\DAL\Employee\EmployeeDALInterface;

/**
 * @property EmployeeDALInterface DAL
 */
class EmployeeBLL extends BaseBLL implements EmployeeBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(EmployeeDALInterface $employeeDAL)
    {
        $this->employeeDAL = $employeeDAL;
    }
    public function getAllEmployees()
    {
        return Employee::all();
    }

    public function getEmployeeById($id)
    {
        return Employee::findOrFail($id);
    }

    public function updateEmployee($id, $data)
    {
        $employee = Employee::findOrFail($id);

        if (isset($data['profile_picture'])) {
            $data['profile_picture'] = $data['profile_picture']->store('profile_pictures', 'public');
        }

        $employee->update($data);
        return $employee;
    }

    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
    }

    public function getOverview()
    {
        $totalEmployees = Employee::count();
        $newHires = Employee::whereMonth('join_date', now()->month)->count();
        $leavings = Employee::whereMonth('resign_date', now()->month)->count();

        return [
            'totalEmployees' => $totalEmployees,
            'newHires' => $newHires,
            'leavings' => $leavings,
        ];
    }
}

