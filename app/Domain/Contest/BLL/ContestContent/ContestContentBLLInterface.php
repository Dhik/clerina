<?php

namespace App\Domain\Contest\BLL\ContestContent;

use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Models\ContestContent;
use App\Domain\Contest\Requests\ContestContentRequest;
use App\Domain\Contest\Requests\ContestRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Utilities\Request;

interface ContestContentBLLInterface extends BaseBLLInterface
{
    /**
     * Get contest content list datatable
     */
    public function getContestContentDataTable(Contest $contest): Builder;

    /**
     * Create new contest content
     */
    public function storeContestContent(ContestContentRequest $request): ContestContent;

    /**
     * Update contest content
     */
    public function updateContestContent(ContestContent $content, ContestContentRequest $request): ContestContent;

    /**
     * Delete contest
     */
    public function deleteContestContent(ContestContent $content): bool;

    public function scrapData(ContestContent $content);
    public function getContestContentRecap(Contest $contest, $filterDates = null): Builder;
}
