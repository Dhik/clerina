<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\BLL\Offer\OfferBLLInterface;
use App\Domain\Campaign\Enums\OfferEnum;
use App\Domain\Campaign\Exports\KeyOpinionLeaderExport;
use App\Domain\Campaign\Exports\OfferExport;
use App\Domain\Campaign\Models\Campaign;
use App\Domain\Campaign\Models\KeyOpinionLeader;
use App\Domain\Campaign\Models\Budget;
use App\Domain\Campaign\Requests\ChatProofRequest;
use App\Domain\Campaign\Requests\FinanceOfferRequest;
use App\Domain\Campaign\Requests\OfferRequest;
use App\Domain\Campaign\Requests\OfferStatusRequest;
use App\Domain\Campaign\Requests\OfferUpdateRequest;
use App\Domain\Campaign\Requests\ReviewOfferRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class BudgetController extends Controller
{
    public function __construct(protected OfferBLLInterface $offerBLL)
    {
    }

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
        $this->authorize('viewOffer', Offer::class);
        return view('admin.budget.index');
    }
    public function create()
    {
        return view('admin.budget.create');
    }
    public function store(Request $request)
    {
        Budget::create($request->all());
        return redirect()->route('budgets.index');
    }

    public function edit($id)
    {
        $budget = Budget::findOrFail($id);
        return view('admin.budget.edit', compact('budget'));
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);
        $budget->update($request->all());
        return redirect()->route('budgets.index')->with('success', 'Budget updated successfully.');
    }

    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);
        $budget->delete();
        return response()->json(['success' => true]);
    }

    public function show()
{
    $budgets = Budget::all();

    return DataTables::of($budgets)
        ->addColumn('action', function ($budget) {
            return '
                <button class="btn btn-sm btn-primary editButton" 
                    data-id="' . $budget->id . '" 
                    data-nama_budget="' . htmlspecialchars($budget->nama_budget, ENT_QUOTES, 'UTF-8') . '" 
                    data-budget="' . $budget->budget . '" 
                    data-toggle="modal" 
                    data-target="#budgetModal">
                    Edit
                </button>
                <button class="btn btn-sm btn-danger deleteButton" data-id="' . $budget->id . '">Delete</button>
            ';
        })
        ->make(true);
}

    
}
