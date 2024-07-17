<?php

namespace App\Domain\Employee\BLL\Employee;

use App\Domain\Employee\Models\Employee;
use App\DomainUtils\BaseBLL\BaseBLLInterface;

interface EmployeeBLLInterface extends BaseBLLInterface
{
    // public function updateEmployee(Employee $employee, EmployeeUpdateRequest $request): Employee;
    public function getAllEmployees();
    public function getEmployeeById($id);
    public function updateEmployee($id, $data);
    public function deleteEmployee($id);
    public function getOverview();
    public function createEmployee(array $data): Employee;
}
