<?php

namespace App\Domain\Employee\BLL\TimeOff;

interface TimeOffBLLInterface
{
    public function getPendingTimeOffs();
    public function getApprovedTimeOffs();
    public function getRejectedTimeOffs();
    public function updateTimeOffStatus($id, $status);
}
