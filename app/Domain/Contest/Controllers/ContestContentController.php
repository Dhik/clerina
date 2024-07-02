<?php

namespace App\Domain\Contest\Controllers;

use App\Domain\Contest\BLL\Contest\ContestBLLInterface;
use App\Domain\Contest\BLL\ContestContent\ContestContentBLLInterface;
use App\Domain\Contest\Job\ScrapJob;
use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Models\ContestContent;
use App\Domain\Contest\Requests\ContestContentRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Domain\Contest\Exports\ContestContentExport;


/**
 * @property ContestBLLInterface contestBLL
 */
class ContestContentController extends Controller
{
    public function __construct(
        protected ContestContentBLLInterface $contentBLL,
        protected ContestContent $content
    )
    {
    }

    /**
     * @param  Contest  $contest
     * @return JsonResponse
     * @throws \Exception
     */
    public function get(Contest $contest): JsonResponse
    {
        $query = $this->contentBLL->getContestContentDataTable($contest);

        return DataTables::of($query)
            ->addColumn('username_link', function ($row) {
                return '<a href='. $row->social_media_link.' target="_blank">'. $row->username .'</a>';
            })
            ->addColumn('actions', function ($row) {
                $actions = '<button class="btn btn-primary btn-xs btnDetail">
                            <i class="fas fa-eye"></i>
                        </button>';

                if (auth()->check()) {
                    $actions .= ' <a href='.route('contestContent.edit', $row->id).' class="btn btn-success btn-xs">
                        <i class="fas fa-pencil-alt"></i>
                    </a>';

                    $actions .= ' <button class="btn btn-info btn-xs btnRefresh">
                             <i class="fas fa-sync-alt"></i>
                        </button>';

                    $actions .= ' <button class="btn btn-danger btn-xs deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>';
                }

                return $actions;
            })
            ->rawColumns(['actions', 'username_link'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create(Contest $contest)
    {
        $this->authorize('createContest', Contest::class);

        return view('admin.contest.content.create', compact('contest'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  ContestContentRequest  $request
     * @return RedirectResponse
     */
    public function store(ContestContentRequest $request)
    {
        $this->authorize('createContest', Contest::class);

        $content = $this->contentBLL->storeContestContent($request);

        return redirect()
            ->route('contest.show', $content->contest_id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_save', ['model' => trans('labels.content')]),
            ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ContestContent  $contestContent
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function edit(ContestContent $contestContent)
    {
        $this->authorize('updateContest', Contest::class);

        return view('admin.contest.content.edit', compact('contestContent'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  ContestContentRequest  $request
     * @param  ContestContent  $content
     * @return RedirectResponse
     */
    public function update(ContestContentRequest $request, ContestContent $contestContent)
    {
        $this->authorize('updateContest', Contest::class);

        $content = $this->contentBLL->updateContestContent($contestContent, $request);

        return redirect()
            ->route('contest.show', $content->contest_id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_update', ['model' => trans('labels.content')]),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ContestContent  $contestContent
     * @return JsonResponse
     */
    public function destroy(ContestContent $contestContent)
    {
        $this->authorize('deleteContest', Contest::class);

        $result = $this->contentBLL->deleteContestContent($contestContent);

        if (! $result) {
            return response()->json(['message' => trans('messages.error_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }

    public function refresh(ContestContent $contestContent)
    {
        $this->authorize('updateContest', Contest::class);

        $result = $this->contentBLL->scrapData($contestContent);

        if (!$result) {
            return response()->json('failed')->setStatusCode(500);
        }

        return response()->json($result);
    }

    public function bulkRefresh(Contest $contest): RedirectResponse
    {
        $this->authorize('updateContest', Contest::class);

        $contest = $contest->load('contestContent');

        foreach ($contest->contestContent as $content) {
            ScrapJob::dispatch($content);
        }

        return redirect()->back()->with([
            'alert' => 'success',
            'message' => trans('messages.process_ongoing'),
        ]);
    }
    public function getContestContentRecap(Request $request, Contest $contest): JsonResponse
    {
        $filterDates = $request->input('filterDates');
        $query = $this->contentBLL->getContestContentRecap($contest, $filterDates);

        return DataTables::of($query)
            ->addColumn('username_link', function ($row) {
                return '<a href=' . $row->social_media_link . ' target="_blank">' . $row->username . '</a>';
            })
            ->addColumn('actions', function ($row) {
                $actions = '<button class="btn btn-primary btn-xs btnDetail">
                                <i class="fas fa-eye"></i>
                            </button>';

                if (auth()->check()) {
                    $actions .= ' <a href=' . route('contestContent.edit', $row->id) . ' class="btn btn-success btn-xs">
                            <i class="fas fa-pencil-alt"></i>
                        </a>';

                    $actions .= ' <button class="btn btn-info btn-xs btnRefresh">
                                 <i class="fas fa-sync-alt"></i>
                            </button>';

                    $actions .= ' <button class="btn btn-danger btn-xs deleteButton">
                                <i class="fas fa-trash-alt"></i>
                            </button>';
                }

                return $actions;
            })
            ->rawColumns(['actions', 'username_link'])
            ->toJson();
    }
    public function export(Contest $contest)
    {
        return Excel::download(new ContestContentExport($contest->id), 'contest_content_' . $contest->id . '.xlsx');
    }
}
