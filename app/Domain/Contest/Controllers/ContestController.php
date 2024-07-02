<?php

namespace App\Domain\Contest\Controllers;

use App\Domain\Campaign\Models\Campaign;
use App\Http\Controllers\Controller;
use App\Domain\Contest\BLL\Contest\ContestBLLInterface;
use App\Domain\Contest\Models\Contest;
use App\Domain\Contest\Requests\ContestRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;

/**
 * @property ContestBLLInterface contestBLL
 */
class ContestController extends Controller
{
    public function __construct(protected ContestBLLInterface $contestBLL)
    {
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function get(): JsonResponse
    {
        $this->authorize('viewContest', Contest::class);

        $query = $this->contestBLL->getContestDataTable();

        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
                $actions = '<a href=' . route('contest.show', $row->id) . ' class="btn btn-success btn-xs">
                        <i class="fas fa-eye"></i>
                    </a>';

                $actions .= ' <a href=' . route('contest.edit', $row->id) . ' class="btn btn-primary btn-xs">
                        <i class="fas fa-pencil-alt"></i>
                    </a>';

                $actions .= ' <button class="btn btn-danger btn-xs deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>';

                return $actions;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        $this->authorize('viewContest', Contest::class);

        return view('admin.contest.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        $this->authorize('createContest', Contest::class);

        return view('admin.contest.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ContestRequest $request
     */
    public function store(ContestRequest $request)
    {
        $this->authorize('createContest', Contest::class);

        $contest = $this->contestBLL->storeContest($request);

        return redirect()
            ->route('contest.show', $contest->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_save', ['model' => trans('labels.contest')]),
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Contest $contest
     */
    public function show(Contest $contest)
    {
        return view('admin.contest.show', compact('contest'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Contest  $contest
     */
    public function edit(Contest $contest)
    {
        $this->authorize('updateContest', Contest::class);

        return view('admin.contest.edit', compact('contest'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ContestRequest $request
     * @param  Contest  $contest
     */
    public function update(ContestRequest $request, Contest $contest)
    {
        $this->authorize('updateContest', Contest::class);

        $contest = $this->contestBLL->updateContest($contest, $request);

        return redirect()
            ->route('contest.show', $contest->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_update', ['model' => trans('labels.contest')]),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Contest $contest
     */
    public function destroy(Contest $contest)
    {
        $this->authorize('deleteContest', Contest::class);

        $result = $this->contestBLL->deleteContest($contest);

        if (! $result) {
            return response()->json(['message' => trans('messages.error_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }
}
