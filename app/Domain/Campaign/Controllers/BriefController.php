<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\Brief;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Carbon\Carbon;

class BriefController extends Controller
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
    public function index()
    {
        return view('admin.brief.index');
    }
    public function create()
    {
        return view('admin.brief.create');
    }
    public function data()
    {
        $briefs = Brief::all();
        return DataTables::of($briefs)
            ->addColumn('actions', function ($brief) {
                return '
                    <a href="' . route('brief.show', $brief->id) . '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i></a>
                    <a href="' . route('brief.edit', $brief->id) . '" class="btn btn-sm btn-success"><i class="fas fa-pencil-alt"></i></a>
                    <form action="' . route('brief.destroy', $brief->id) . '" method="POST" style="display:inline-block;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                    </form>
                ';
            })
            ->editColumn('acc_date', function ($brief) {
                return Carbon::parse($brief->acc_date)->format('d-m-Y');
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    public function store(Request $request)
    {
        $request->validate([
            'acc_date' => 'required|date',
            'title' => 'required|max:255',
            'brief' => 'required',
        ]);

        Brief::create($request->all());
        return redirect()->route('brief.index')->with('success', 'Brief created successfully.');
    }

    public function show(Brief $brief)
    {
        return view('admin.brief.show', compact('brief'));
    }

    public function edit(Brief $brief)
    {
        return view('admin.brief.edit', compact('brief'));
    }

    public function update(Request $request, Brief $brief)
    {
        $request->validate([
            'acc_date' => 'required|date',
            'title' => 'required|max:255',
            'brief' => 'required',
        ]);

        $brief->update($request->all());
        return redirect()->route('brief.index')->with('success', 'Brief updated successfully.');
    }

    public function destroy(Brief $brief)
    {
        $brief->delete();
        return redirect()->route('brief.index')->with('success', 'Brief deleted successfully.');
    }
}
