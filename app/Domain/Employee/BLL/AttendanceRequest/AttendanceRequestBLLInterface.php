<?php

namespace App\Domain\Employee\BLL\AttendanceRequest;

interface AttendanceRequestBLLInterface
{
    public function getPendingRequests();
    public function getApprovedRequests();
    public function getRejectedRequests();
    public function updateRequestStatus($id, $status);
}
