<?php

namespace App\Domain\AffiliateTalent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\AffiliateTalent\BLL\AffiliateTalent\AffiliateTalentBLLInterface;
use App\Domain\AffiliateTalent\Models\AffiliateTalent;
use App\Domain\AffiliateTalent\Requests\AffiliateTalentRequest;
use Illuminate\Http\Request;

class AffiliateTalentController extends Controller
{
    protected $affiliateTalentBLL;

    public function __construct(AffiliateTalentBLLInterface $affiliateTalentBLL)
    {
        $this->affiliateTalentBLL = $affiliateTalentBLL;
    }

    public function index()
    {
        $affiliateTalents = AffiliateTalent::with(['salesChannel', 'tenant'])->paginate(10);
        return view('admin.affiliate.index', compact('affiliateTalents'));
    }

    public function create()
    {
        return view('admin.affiliate.create');
    }

    public function store(AffiliateTalentRequest $request)
    {
        $affiliateTalent = AffiliateTalent::create($request->validated());
        return redirect()->route('affiliate.index')
            ->with('success', 'Affiliate talent created successfully.');
    }

    public function show(AffiliateTalent $affiliate)
    {
        return view('admin.affiliate.show', compact('affiliate'));
    }

    public function edit(AffiliateTalent $affiliate)
    {
        return view('admin.affiliate.edit', compact('affiliate'));
    }

    public function update(AffiliateTalentRequest $request, AffiliateTalent $affiliate)
    {
        $affiliate->update($request->validated());
        return redirect()->route('affiliate.index')
            ->with('success', 'Affiliate talent updated successfully.');
    }

    public function destroy(AffiliateTalent $affiliate)
    {
        $affiliate->delete();
        return redirect()->route('affiliate.index')
            ->with('success', 'Affiliate talent deleted successfully.');
    }

    public function data(Request $request)
    {
        $query = AffiliateTalent::query()->with(['salesChannel', 'tenant']);
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('username', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
        }

        return response()->json($query->paginate(10));
    }
}