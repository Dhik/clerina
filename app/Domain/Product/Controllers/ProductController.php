<?php

namespace App\Domain\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Product\BLL\Product\ProductBLLInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Requests\ProductRequest;

/**
 * @property ProductBLLInterface productBLL
 */
class ProductController extends Controller
{
    public function __construct(ProductBLLInterface $productBLL)
    {
        $this->productBLL = $productBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.product.index');
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
     * @param ProductRequest $request
     */
    public function store(ProductRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Product  $product
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductRequest $request
     * @param  Product  $product
     */
    public function update(ProductRequest $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     */
    public function destroy(Product $product)
    {
        //
    }
}
