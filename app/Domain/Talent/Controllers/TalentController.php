<?php

namespace App\Domain\Talent\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Talent\BLL\Talent\TalentBLLInterface;
use App\Domain\Talent\Models\Talent;
use App\Domain\Talent\Requests\TalentRequest;

/**
 * @property TalentBLLInterface talentBLL
 */
class TalentController extends Controller
{
    public function __construct(TalentBLLInterface $talentBLL)
    {
        $this->talentBLL = $talentBLL;
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
     * @param TalentRequest $request
     */
    public function store(TalentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Talent $talent
     */
    public function show(Talent $talent)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Talent  $talent
     */
    public function edit(Talent $talent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TalentRequest $request
     * @param  Talent  $talent
     */
    public function update(TalentRequest $request, Talent $talent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Talent $talent
     */
    public function destroy(Talent $talent)
    {
        //
    }
}
