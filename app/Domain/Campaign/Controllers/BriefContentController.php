<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\BriefContent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BriefContentController extends Controller
{

    /**
     * Return offer datatable
     * @throws Exception
     */
    /**
     * Get offer by campaign id for datatable
     * @throws Exception
     */


    /**
     * Return index page for offer
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_brief' => 'required|exists:briefs,id',
            'link' => 'required|url|max:255',
        ]);

        BriefContent::create($request->all());

        return redirect()->back()->with('success', 'Link added successfully.');
    }
    public function data($id_brief)
    {
        // Get the latest statistics for each campaign content
        $briefContents = BriefContent::select(
            'brief_contents.id',
            'campaigns.title AS campaign_title',
            'campaign_contents.username',
            'campaign_contents.task_name',
            'brief_contents.link AS brief_link',
            'latest_stats.view',
            'latest_stats.like',
            'latest_stats.comment',
            'latest_stats.cpm'
        )
            ->join('campaign_contents', 'brief_contents.link', '=', 'campaign_contents.link')
            ->join('campaigns', 'campaign_contents.campaign_id', '=', 'campaigns.id')
            ->join('statistics as latest_stats', function ($join) {
                $join->on('campaign_contents.id', '=', 'latest_stats.campaign_content_id')
                    ->where('latest_stats.date', '=', function ($query) {
                        $query->select(DB::raw('MAX(date)'))
                            ->from('statistics')
                            ->whereColumn('campaign_content_id', 'campaign_contents.id');
                    });
            })
            ->where('brief_contents.id_brief', $id_brief)
            ->get();

        return DataTables::of($briefContents)
            ->addColumn('actions', function ($briefContent) {
                return '
                    <form action="' . route('brief_contents.destroy', $briefContent->id) . '" method="POST" style="display:inline-block;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                    </form>
                ';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }



    public function destroy($id)
    {
        $briefContent = BriefContent::findOrFail($id);
        $briefContent->delete();

        return redirect()->back()->with('success', 'Link deleted successfully.');
    }

    public function getKPI($id_brief)
    {
        $kpiData = BriefContent::select(
            DB::raw('SUM(statistics.like) as total_likes'),
            DB::raw('SUM(statistics.comment) as total_comments'),
            DB::raw('SUM(statistics.view) as total_views'),
            DB::raw('AVG(statistics.cpm) as cpm')
        )
            ->join('campaign_contents', 'brief_contents.link', '=', 'campaign_contents.link')
            ->join('statistics', 'campaign_contents.id', '=', 'statistics.campaign_content_id')
            ->where('brief_contents.id_brief', $id_brief)
            ->first();

        return response()->json($kpiData);
    }

    public function chartData($id_brief, Request $request): JsonResponse
    {
        // Authorize the request, similar to how it's done in StatisticController
        $this->authorize('viewCampaignContent', CampaignContent::class);

        // Fetch the statistics for the given brief
        $query = BriefContent::select(
            'statistics.date',
            DB::raw('SUM(statistics.view) as total_view'),
            DB::raw('SUM(statistics.like) as total_like'),
            DB::raw('SUM(statistics.comment) as total_comment'),
            DB::raw('SUM(CASE WHEN statistics.like > 0 THEN statistics.like ELSE 0 END) as positive_like')
        )
            ->join('campaign_contents', 'brief_contents.link', '=', 'campaign_contents.link')
            ->join('statistics', 'campaign_contents.id', '=', 'statistics.campaign_content_id')
            ->where('brief_contents.id_brief', $id_brief)
            ->groupBy('statistics.date');

        // Apply date filtering if provided
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->whereBetween('statistics.date', [$startDate, $endDate]);
        }

        // Get the results
        $chartData = $query->get();

        // Return the data as JSON
        return response()->json($chartData);
    }
}
