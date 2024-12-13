<?php

namespace App\Domain\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Product\BLL\Product\ProductBLLInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Order\Models\Order;
use App\Domain\Product\Requests\ProductRequest;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use App\Domain\Product\Import\ProductImport;
use Auth;

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

    public function data()
    {
        $products = Product::where('tenant_id', Auth::user()->current_tenant_id)->get();

        $orderCounts = Order::selectRaw('sku, COUNT(*) as count')
            ->groupBy('sku')
            ->pluck('count', 'sku');

        return DataTables::of($products)
            ->addColumn('action', function ($product) {
                return '
                    <button class="btn btn-sm btn-primary viewButton" 
                        data-id="' . $product->id . '" 
                        data-toggle="modal" 
                        data-target="#viewProductModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $product->id . '" 
                        data-product="' . htmlspecialchars($product->product, ENT_QUOTES, 'UTF-8') . '" 
                        data-stock="' . $product->stock . '" 
                        data-sku="' . $product->sku . '" 
                        data-harga_jual="' . $product->harga_jual . '" 
                        data-harga_markup="' . $product->harga_markup . '" 
                        data-harga_cogs="' . $product->harga_cogs . '" 
                        data-harga_batas_bawah="' . $product->harga_batas_bawah . '" 
                        data-tenant_id="' . $product->tenant_id . '" 
                        data-toggle="modal" 
                        data-target="#productModal">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" data-id="' . $product->id . '"><i class="fas fa-trash-alt"></i></button>
                ';
            })
            ->addColumn('order_count', function ($product) use ($orderCounts) {
                return $orderCounts->filter(function($count, $sku) use ($product) {
                    return strpos($sku, $product->sku) !== false;
                })->sum() ?? 0;
            })
            ->rawColumns(['action'])
            ->make(true);
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
        try {
            $product = new Product();
            $product->product = $request->product;
            $product->stock = $request->stock;
            $product->sku = $request->sku;
            $product->harga_jual = $request->harga_jual;
            $product->harga_markup = $request->harga_markup;
            $product->harga_cogs = $request->harga_cogs;
            $product->harga_batas_bawah = $request->harga_batas_bawah;
            $product->tenant_id = Auth::user()->current_tenant_id;
            $product->save();

            return response()->json(['message' => 'Product added successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add product'], 500);
        }
    }

    public function getOrders(Product $product)
    {
        $orders = Order::where('sku', 'LIKE', '%'.$product->sku.'%')
                    ->orderBy('date', 'desc'); 

        return datatables()->of($orders)
            ->addColumn('total_price', function($order) {
                return number_format($order->amount, 0, ',', '.');
            })
            ->addColumn('date', function($order) {
                // Parse the date to a Carbon instance and format it
                return \Carbon\Carbon::parse($order->date)->format('Y-m-d');
            })
            ->rawColumns(['total_price']) // Optional: if you need to render raw HTML in a column
            ->make(true);
    }

    public function getOrderCountPerDay(Product $product)
    {
        $orderCounts = Order::where('sku', 'LIKE', '%'.$product->sku.'%')
            ->selectRaw('DATE(date) as order_date, COUNT(id_order) as order_count')
            ->groupBy('order_date')
            ->orderBy('order_date', 'asc')
            ->get();

        $labels = $orderCounts->pluck('order_date');
        $data = $orderCounts->pluck('order_count');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    /**
     * Show the details of the specified product.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('admin.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        // Return product data in JSON format for the AJAX call
        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product in storage.
     *
     * @param ProductRequest $request
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            // Validate and update the product data
            $validatedData = $request->validated();
            $product->update($validatedData);
    
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        try {
            // Delete the product from the database
            $product->delete();

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }
}
