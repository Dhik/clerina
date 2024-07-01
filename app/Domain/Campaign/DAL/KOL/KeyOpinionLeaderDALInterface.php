<?php

namespace App\Domain\Campaign\DAL\KOL;

use App\Domain\Campaign\Models\KeyOpinionLeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Yajra\DataTables\Utilities\Request;

interface KeyOpinionLeaderDALInterface
{
    /**
     * Get datatable
     */
    public function getKOLDatatable(): Builder;

    /**
     * Select kol by username
     */
    public function selectKOL(?string $username): Collection;

    /**
     * Create a new Key Opinion Leader
     */
    public function storeKOL(array $data): KeyOpinionLeader;

    /**
     * Update Key Opinion Leader
     */
    public function updateKOL(KeyOpinionLeader $keyOpinionLeader, array $data): KeyOpinionLeader;
}
