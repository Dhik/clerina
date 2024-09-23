<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\BriefContent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
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
        $briefContents = BriefContent::where('id_brief', $id_brief)->get();

        return DataTables::of($briefContents)
            ->addColumn('actions', function ($briefContent) {
                return '
                    <form action="'.route('brief_contents.destroy', $briefContent->id).'" method="POST" style="display:inline-block;">
                        '.csrf_field().'
                        '.method_field('DELETE').'
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
    
}
