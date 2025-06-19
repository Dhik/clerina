<?php

namespace App\Domain\Affiliate\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Affiliate\BLL\Affiliate\AffiliateBLLInterface;
use App\Domain\Affiliate\Models\Affiliate;
use App\Domain\Affiliate\Requests\AffiliateRequest;

/**
 * @property AffiliateBLLInterface affiliateBLL
 */
class AffiliateController extends Controller
{
    public function __construct(AffiliateBLLInterface $affiliateBLL)
    {
        $this->affiliateBLL = $affiliateBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param AffiliateRequest $request
     */
    public function store(AffiliateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Affiliate $affiliate
     */
    public function show(Affiliate $affiliate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Affiliate  $affiliate
     */
    public function edit(Affiliate $affiliate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param AffiliateRequest $request
     * @param  Affiliate  $affiliate
     */
    public function update(AffiliateRequest $request, Affiliate $affiliate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Affiliate $affiliate
     */
    public function destroy(Affiliate $affiliate)
    {
        //
    }
}
