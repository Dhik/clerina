<?php

namespace App\Domain\KeywordMonitoring\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\KeywordMonitoring\BLL\KeywordMonitoring\KeywordMonitoringBLLInterface;
use App\Domain\KeywordMonitoring\Models\KeywordMonitoring;
use App\Domain\KeywordMonitoring\Models\Posting;
use App\Domain\KeywordMonitoring\Requests\KeywordMonitoringRequest;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


/**
 * @property KeywordMonitoringBLLInterface keywordMonitoringBLL
 */
class KeywordMonitoringController extends Controller
{
    public function __construct(KeywordMonitoringBLLInterface $keywordMonitoringBLL)
    {
        $this->keywordMonitoringBLL = $keywordMonitoringBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.keywordMonitoring.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        return view('admin.keywordMonitoring.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param KeywordMonitoringRequest $request
     */
    public function store(KeywordMonitoringRequest $request)
    {
        $request->validate([
            'keyword' => 'required|max:255',
        ]);

        $this->keywordMonitoringBLL->create($request->all());
        return redirect()->route('keywordMonitoring.index')->with('success', 'Keyword created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param KeywordMonitoring $keywordMonitoring
     */
    public function show(KeywordMonitoring $keywordMonitoring)
    {
        return view('admin.keywordMonitoring.show', compact('keywordMonitoring'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  KeywordMonitoring  $keywordMonitoring
     */
    public function edit(KeywordMonitoring $keywordMonitoring)
    {
        return view('admin.keywordMonitoring.edit', compact('keywordMonitoring'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param KeywordMonitoringRequest $request
     * @param  KeywordMonitoring  $keywordMonitoring
     */
    public function update(KeywordMonitoringRequest $request, KeywordMonitoring $keywordMonitoring)
    {
        $request->validate([
            'keyword' => 'required|max:255',
        ]);

        $this->keywordMonitoringBLL->update($keywordMonitoring, $request->all());
        return redirect()->route('keywordMonitoring.index')->with('success', 'Keyword updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param KeywordMonitoring $keywordMonitoring
     */
    public function destroy(KeywordMonitoring $keywordMonitoring)
    {
        $this->keywordMonitoringBLL->delete($keywordMonitoring);
        return redirect()->route('keywordMonitoring.index')->with('success', 'Keyword deleted successfully.');
    }

    public function data()
{
    $keywordMonitorings = $this->keywordMonitoringBLL->all();

    return DataTables::of($keywordMonitorings)
        ->addColumn('actions', function ($keywordMonitoring) {
            return '
                <a href="'.route('keywordMonitoring.show', $keywordMonitoring->id).'" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                <a href="'.route('keywordMonitoring.edit', $keywordMonitoring->id).'" class="btn btn-sm btn-success"><i class="fas fa-pencil-alt"></i></a>
                <form action="'.route('keywordMonitoring.destroy', $keywordMonitoring->id).'" method="POST" style="display:inline-block;">
                    '.csrf_field().'
                    '.method_field('DELETE').'
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                </form>
            ';
        })
        ->editColumn('created_at', function ($keywordMonitoring) {
            return \Carbon\Carbon::parse($keywordMonitoring->created_at)->format('d-m-Y');
        })
        ->editColumn('updated_at', function ($keywordMonitoring) {
            return \Carbon\Carbon::parse($keywordMonitoring->updated_at)->format('d-m-Y');
        })
        ->rawColumns(['actions'])
        ->make(true);
}

public function fetchTiktokData($id)
{
    $keywordMonitoring = KeywordMonitoring::findOrFail($id);
    $keyword = $keywordMonitoring->keyword;

    $response = Http::withHeaders([
        'x-rapidapi-key' => '2bc060ac02msh3d873c6c4d26f04p103ac5jsn00306dda9986',
        'x-rapidapi-host' => 'tokapi-mobile-version.p.rapidapi.com'
    ])->get('https://tokapi-mobile-version.p.rapidapi.com/v1/search/post', [
        'keyword' => $keyword,
        'count' => 30,
        'offset' => 30,
        'region' => 'ID',
    ]);

    if ($response->successful()) {
        $data = $response->json();

        foreach ($data['search_item_list'] as $post) {
            $createTime = $post['aweme_info']['create_time'];
            $uploadDate = date('Y-m-d H:i:s', $createTime);
            $awemeId = $post['aweme_info']['statistics']['aweme_id'];

            $existingPost = Posting::where('aweme_id', $awemeId)->first();
            if ($existingPost) {
                // Update existing record
                $existingPost->update([
                    'play_count' => $post['aweme_info']['statistics']['play_count'],
                    'comment_count' => $post['aweme_info']['statistics']['comment_count'],
                    'digg_count' => $post['aweme_info']['statistics']['digg_count'],
                    'share_count' => $post['aweme_info']['statistics']['share_count'],
                    'collect_count' => $post['aweme_info']['statistics']['collect_count'],
                    'download_count' => $post['aweme_info']['statistics']['download_count'],
                    'username' => $post['aweme_info']['author']['unique_id'],
                    'keyword_id' => $id,
                    'upload_date' => $uploadDate,
                ]);
            } else {
                // Create new record
                Posting::create([
                    'play_count' => $post['aweme_info']['statistics']['play_count'],
                    'comment_count' => $post['aweme_info']['statistics']['comment_count'],
                    'digg_count' => $post['aweme_info']['statistics']['digg_count'],
                    'share_count' => $post['aweme_info']['statistics']['share_count'],
                    'collect_count' => $post['aweme_info']['statistics']['collect_count'],
                    'download_count' => $post['aweme_info']['statistics']['download_count'],
                    'aweme_id' => $awemeId,
                    'username' => $post['aweme_info']['author']['unique_id'],
                    'keyword_id' => $id,
                    'upload_date' => $uploadDate,
                ]);
            }
        }

        return response()->json($data);
    } else {
        return response()->json(['error' => 'Failed to fetch data'], 500);
    }
}

    public function getPostingsData($id)
    {
        $query = Posting::where('keyword_id', $id);

        return DataTables::of($query)
            ->addColumn('post_link', function ($posting) {
                return '<a href="https://www.tiktok.com/@' . $posting->username . '/video/' . $posting->aweme_id . '" target="_blank">View Post</a>';
            })
            ->rawColumns(['post_link'])
            ->make(true);
    }

    public function getAllPostings()
    {
        $data = Posting::select(
                'keyword_monitorings.keyword',
                DB::raw('SUM(postings.play_count) as total_play_count'),
                DB::raw('SUM(postings.comment_count) as total_comment_count'),
                DB::raw('SUM(postings.digg_count) as total_digg_count'),
                DB::raw('SUM(postings.share_count) as total_share_count'),
                DB::raw('SUM(postings.collect_count) as total_collect_count'),
                DB::raw('SUM(postings.download_count) as total_download_count'),
                DB::raw('COUNT(postings.id) as total_posts'),
                DB::raw('SUM(postings.play_count) / COUNT(postings.id) as avg_play_count')
            )
            ->join('keyword_monitorings', 'keyword_monitorings.id', '=', 'postings.keyword_id')
            ->groupBy('keyword_monitorings.keyword')
            ->orderBy('avg_play_count', 'desc')
            ->get();

        $data->transform(function ($item) {
            $item->total_play_count = number_format($item->total_play_count);
            $item->total_comment_count = number_format($item->total_comment_count);
            $item->total_digg_count = number_format($item->total_digg_count);
            $item->total_share_count = number_format($item->total_share_count);
            $item->total_collect_count = number_format($item->total_collect_count);
            $item->total_download_count = number_format($item->total_download_count);
            $item->avg_play_count = number_format($item->avg_play_count, 2);
            return $item;
        });

        return response()->json($data);
    }


}
