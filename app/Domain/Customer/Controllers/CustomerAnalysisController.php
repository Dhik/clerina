<?php

namespace App\Domain\Customer\Controllers;

use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomersAnalysis;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Customer\Requests\CustomerRequest;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationAlias;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon; 
use Yajra\DataTables\Utilities\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Domain\Sales\Services\GoogleSheetService;
use Illuminate\Support\Facades\DB;
use App\Domain\Customer\Exports\CustomersExport;
use App\Domain\Customer\Exports\CustomersAnalysisExport;

class CustomerAnalysisController extends Controller
{
    protected $googleSheetService;
    public function __construct(protected CustomerBLLInterface $customerBLL, GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * @throws Exception
     */
    public function index()
    {
        return view('admin.customers_analysis.index');
    }
    public function data(Request $request)
    {
        $query = CustomersAnalysis::query();

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }

        if ($request->has('produk') && $request->produk) {
            $produk = $request->produk;
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$produk]);
        }

        $query = $query->selectRaw('
            MIN(id) as id,
            nama_penerima,
            nomor_telepon,
            COUNT(id) as total_orders,
            MIN(is_joined) as is_joined
        ')
        ->groupBy('nama_penerima', 'nomor_telepon');

        $dataTable = DataTables::of($query);

        $dataTable->filter(function ($query) use ($request) {
            if ($request->has('search') && $request->search['value']) {
                $search = strtolower($request->search['value']);
                $query->havingRaw('LOWER(nama_penerima) LIKE ? OR LOWER(nomor_telepon) LIKE ? OR LOWER(total_orders) LIKE ?', ["%$search%", "%$search%", "%$search%"]);
            }
        });
                
        $dataTable->addColumn('is_joined', function ($row) {
            if ($row->is_joined == 0) {
                return '
                    <button class="btn btn-sm bg-maroon joinButton" 
                        data-id="' . $row->id . '">
                        <i class="fas fa-redo"></i> Join
                    </button>
                    ';
                } else {
                    return '
                        <button class="btn btn-sm bg-info unJoinButton" 
                            data-id="' . $row->id . '">
                            <i class="fas fa-undo"></i> Joined
                        </button>
                    ';
                }
            });
            $dataTable->addColumn('details', function ($row) {
                return '
                    <button class="btn btn-light viewButton" 
                        data-id="' . $row->id . '" 
                        data-toggle="modal" 
                        data-target="#viewCustomerModal" 
                        data-placement="top" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                ';
            });
            // <button class="btn btn-light editButton" 
            //             data-id="' . $row->id . '" 
            //             data-toggle="modal" 
            //             data-target="#editCustomerModal" 
            //             data-placement="top" title="Edit">
            //             <i class="fas fa-pencil-alt"></i>
            //         </button>
                
        return $dataTable->rawColumns(['is_joined', 'details'])->make(true);
    }

    public function edit($id)
    {
        $customer = CustomersAnalysis::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }
        return response()->json(['customer' => $customer]);
    }



    public function importCustomers()
    {
        $range = 'Import Customers!A2:H'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            $tanggalPesananDibuat = Carbon::createFromFormat('Y-m-d H:i', $row[0])->format('Y-m-d H:i:s');
            // if (Carbon::parse($tanggalPesananDibuat)->format('Y-m') !== $currentMonth) {
            //     continue;
            // }

            $customerData = [
                'tanggal_pesanan_dibuat' => $tanggalPesananDibuat,
                'nama_penerima'          => $row[1] ?? null,
                'produk'                 => $row[2] ?? null,
                'qty'                    => (int) $row[3] ?? 0,
                'alamat'                 => $row[4] ?? null,
                'kota_kabupaten'         => $row[5] ?? null,
                'provinsi'               => $row[6] ?? null,
                'nomor_telepon'          => $row[7] ?? null,
                'tenant_id'              => $tenant_id,
                'sales_channel_id'       => 1, 
                'social_media_id'        => null, 
            ];
            CustomersAnalysis::updateOrCreate(
                [
                    'tanggal_pesanan_dibuat' => $tanggalPesananDibuat,
                    'tenant_id'              => $tenant_id,
                    'nama_penerima'          => $row[1] ?? null,
                ],
                $customerData
            );
        }

        return response()->json(['message' => 'Data imported successfully']);
    }
    public function countUniqueCustomers(Request $request)
    {
        $query = CustomersAnalysis::query();

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }
        if ($request->has('produk') && $request->produk) {
            $produk = $request->produk;
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$produk]);
        }

        $uniqueCount = $query->select('nama_penerima', 'nomor_telepon')
                            ->distinct()
                            ->count();
        
        $joinedCount = $query->where('is_joined', 1)
                          ->select('nama_penerima', 'nomor_telepon')
                          ->distinct()
                          ->count();

        return response()->json(['unique_customer_count' => $uniqueCount, 'joined_count' => $joinedCount]);
    }
    public function getProductCounts(Request $request)
    {
        $query = CustomersAnalysis::query();

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }
        if ($request->has('produk') && $request->produk) {
            $produk = $request->produk;
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$produk]);
        }

        $data = $query->selectRaw('SUBSTRING_INDEX(produk, " -", 1) as short_name, COUNT(*) as total_count')
            ->groupBy('short_name')
            ->get();

        return response()->json($data);
    }
    public function getDailyUniqueCustomers(Request $request)
    {
        $query = CustomersAnalysis::query();
        
        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }
        if ($request->has('produk') && $request->produk) {
            $produk = $request->produk;
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$produk]);
        }

        $dailyCounts = $query->selectRaw('DATE(tanggal_pesanan_dibuat) as date, COUNT(DISTINCT CONCAT(nama_penerima, nomor_telepon)) as unique_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($dailyCounts);
    }
    public function join($id)
    {
        try {
            $customerAnalysis = CustomersAnalysis::findOrFail($id);
            
            $namaPenerima = $customerAnalysis->nama_penerima;
            $nomorTelepon = $customerAnalysis->nomor_telepon;
    
            CustomersAnalysis::where('nama_penerima', $namaPenerima)
                ->where('nomor_telepon', $nomorTelepon)
                ->update(['is_joined' => 1]);
    
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to unjoin customers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to unjoin customers.'], 500);
        }
    }
    public function unjoin($id)
    {
        try {
            // First, get the customer analysis record by ID
            $customerAnalysis = CustomersAnalysis::findOrFail($id);
            
            // Retrieve the nama_penerima and nomor_telepon from the found record
            $namaPenerima = $customerAnalysis->nama_penerima;
            $nomorTelepon = $customerAnalysis->nomor_telepon;
    
            // Update all records matching nama_penerima and nomor_telepon
            CustomersAnalysis::where('nama_penerima', $namaPenerima)
                ->where('nomor_telepon', $nomorTelepon)
                ->update(['is_joined' => 0]); // Set is_joined to 0
    
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to unjoin customers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to unjoin customers.'], 500);
        }
    }

    public function show($id)
    {
        $customer = CustomersAnalysis::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        // Retrieve and sort orders by date
        $customerOrders = CustomersAnalysis::where('nama_penerima', $customer->nama_penerima)
            ->where('nomor_telepon', $customer->nomor_telepon)
            ->orderBy('tanggal_pesanan_dibuat', 'asc') // Sort by date
            ->get(['produk', 'tanggal_pesanan_dibuat', 'qty']);

        $totalQty = CustomersAnalysis::where('nama_penerima', $customer->nama_penerima)
            ->where('nomor_telepon', $customer->nomor_telepon)
            ->sum('qty');

        return response()->json([
            'nama_penerima' => $customer->nama_penerima,
            'nomor_telepon' => $customer->nomor_telepon,
            'alamat' => $customer->alamat,
            'kota_kabupaten' => $customer->kota_kabupaten,
            'provinsi' => $customer->provinsi,
            'quantity' => $totalQty,
            'orders' => $customerOrders->map(function($order) {
                return [
                    'produk' => $order->produk,
                    'tanggal_pesanan_dibuat' => $order->tanggal_pesanan_dibuat,
                    'qty' => $order->qty,
                ];
            })
        ]);
    }

    public function productDistribution($id)
    {
        $customer = CustomersAnalysis::find($id);

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        $productDistribution = CustomersAnalysis::where('nama_penerima', $customer->nama_penerima)
            ->where('nomor_telepon', $customer->nomor_telepon)
            ->select('produk', DB::raw('COUNT(*) as count'))
            ->groupBy('produk')
            ->get();

        return response()->json($productDistribution);
    }

    public function getProducts()
    {
        $products = CustomersAnalysis::selectRaw('DISTINCT SUBSTRING_INDEX(produk, " -", 1) as short_name')
            ->orderBy('short_name')
            ->get();

        return response()->json($products);
    }

    public function export(Request $request)
    {
        $month = $request->input('month');
        $produk = $request->input('produk');

        return Excel::download(new CustomersAnalysisExport($month, $produk), 'customer_analysis.xlsx');
    }

    
    public function getCityCounts(Request $request)
    {
        $query = CustomersAnalysis::query();

        // Optional filter for month
        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }

        // Optional filter for specific city (kota_kabupaten)
        if ($request->has('kota_kabupaten') && $request->kota_kabupaten) {
            $kotaKabupaten = $request->kota_kabupaten;
            $query->where('kota_kabupaten', $kotaKabupaten);
        }

        // Fetching count of orders per kota_kabupaten
        $data = $query->selectRaw('kota_kabupaten, COUNT(*) as total_count')
            ->groupBy('kota_kabupaten')
            ->get();

        return response()->json($data);
    }


}
