<?php

namespace App\Domain\Campaign\DAL\KOL;

use App\Domain\Campaign\Models\KeyOpinionLeader;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class KeyOpinionLeaderDAL implements KeyOpinionLeaderDALInterface
{
    public function __construct(protected KeyOpinionLeader $kol)
    {}

    /**
     * Get datatable
     */
    public function getKOLDatatable(): Builder
    {
        return $this->kol->query()->with('picContact');
    }

    /**
     * Select kol by username
     */
    public function selectKOL(?string $username): Collection
    {
        return $this->kol
            ->select('id', 'channel', 'username')
            ->where('username', 'like', '%' . $username . '%')
            ->limit(50)
            ->get();
    }

    /**
     * Create a new Key Opinion Leader
     */
    public function storeKOL(array $data): KeyOpinionLeader
    {
        return $this->kol->create($data);
    }

    /**
     * Update Key Opinion Leader
     */
    public function updateKOL(KeyOpinionLeader $keyOpinionLeader, array $data): KeyOpinionLeader
    {
        $keyOpinionLeader->update($data);
        return $keyOpinionLeader;
    }
}
