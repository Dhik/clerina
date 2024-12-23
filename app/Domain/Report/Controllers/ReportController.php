<?php

namespace App\Domain\Report\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Report\BLL\Report\ReportBLLInterface;
use App\Domain\Report\Models\Report;
use App\Domain\Report\Requests\ReportRequest;

/**
 * @property ReportBLLInterface reportBLL
 */
class ReportController extends Controller
{
    public function __construct(ReportBLLInterface $reportBLL)
    {
        $this->reportBLL = $reportBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.report.index_');
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
     * @param ReportRequest $request
     */
    public function store(ReportRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Report $report
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Report  $report
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ReportRequest $request
     * @param  Report  $report
     */
    public function update(ReportRequest $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Report $report
     */
    public function destroy(Report $report)
    {
        //
    }
}
