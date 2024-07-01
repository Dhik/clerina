<?php

namespace App\Domain\Campaign\BLL\KOL;

use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Requests\KeyOpinionLeaderRequest;
use DragonCode\Support\Helpers\Boolean;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Yajra\DataTables\Utilities\Request;

interface KeyOpinionLeaderBLLInterface
{
    /**
     * Return KOL datatable
     */
    public function getKOLDatatable(Request $request): Builder;

    /**
     * Select kol by username
     */
    public function selectKOL(?string $username): Collection;

    /**
     * Create a new Key Opinion Leader
     */
    public function storeKOL(KeyOpinionLeaderRequest $request): KeyOpinionLeader;

    /**
     * Create a new Key Opinion Leader via excel input
     */
    public function storeExcel(array $arrayData): bool;

    /**
     * Update Key Opinion Leader
     */
    public function updateKOL(KeyOpinionLeader $keyOpinionLeader, KeyOpinionLeaderRequest $request): KeyOpinionLeader;
}
