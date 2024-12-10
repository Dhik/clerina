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
    public function __construct(protected OfferBLLInterface $offerBLL) {}

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
        return response()->json(['budget' => $budget]);
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


    public function showCampaigns($id)
    {
        // Fetch the budget with related campaigns and their total expenses
        $budget = Budget::with(['campaigns' => function ($query) {
            $query->select('id', 'id_budget', 'title', 'start_date', 'end_date', 'description', 'total_expense');
        }])->findOrFail($id);

        // Calculate the sum of total expenses for the campaigns under this budget
        $totalExpenseSum = $budget->campaigns->sum('total_expense');

        return response()->json([
            'budget' => $budget,
            'campaigns' => $budget->campaigns,
            'totalExpenseSum' => number_format($totalExpenseSum, 0, ',', '.')
        ]);
    }



    public function show()
    {
        $budgets = Budget::all();

        return DataTables::of($budgets)
            ->addColumn('action', function ($budget) {
                return '
                <button class="btn btn-sm btn-primary viewButton" 
                    data-id="' . $budget->id . '" 
                    data-toggle="modal" 
                    data-target="#viewBudgetModal">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-success editButton" 
                    data-id="' . $budget->id . '" 
                    data-nama_budget="' . htmlspecialchars($budget->nama_budget, ENT_QUOTES, 'UTF-8') . '" 
                    data-budget="' . $budget->budget . '" 
                    data-toggle="modal" 
                    data-target="#budgetModal">
                    <i class="fas fa-pencil-alt"></i>
                </button>
                <button class="btn btn-sm btn-danger deleteButton" data-id="' . $budget->id . '"><i class="fas fa-trash-alt"></i></button>
            ';
            })
            ->make(true);
    }
}
