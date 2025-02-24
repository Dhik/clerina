<?php

namespace App\Domain\Customer\Controllers;

use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomersAnalysis;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Customer\Models\CustomerNote;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Domain\Customer\Jobs\ProcessLargeExport;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use App\Domain\Customer\Models\CustomerMonitor;
use App\Domain\Customer\Exports\ChunkedExportJob;
use Illuminate\Support\Facades\Storage;
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
use App\Domain\Customer\Exports\CustomersAnalysisExportMonth;

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
        $customer = CustomersAnalysis::select('status_customer')
            ->distinct()
            ->whereNotNull('status_customer')
            ->get();
            
        $whichHp = CustomersAnalysis::select('which_hp')
            ->distinct()
            ->whereNotNull('which_hp')
            ->get();

        $cities = CustomersAnalysis::select('kota_kabupaten')
            ->distinct()
            ->whereNotNull('kota_kabupaten')
            ->get();
            
        return view('admin.customers_analysis.index', compact('customer', 'whichHp', 'cities'));
    }
    public function data(Request $request)
    {
        $query = CustomersAnalysis::query();

        if (!$request->month && !$request->produk) {
            $lastMonth = now()->subMonth()->format('Y-m');
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$lastMonth]);
        }

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }
        // if ($request->has('filterDormant')) {
        //     $filterDormant = $request->filterDormant;
        //     if ($filterDormant === '1') {
        //         $query->where('is_dormant', 1);
        //     } else if ($filterDormant === '0') {
        //         $query->where('is_dormant', 0);
        //     }
        //     // If filterDormant is null, don't apply any filter
        // }

        if ($request->has('produk') && $request->produk) {
            $produk = $request->produk;
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$produk]);
        }
        if ($request->has('status') && $request->status) {
            $query->where('status_customer', $request->status);
        }

        $query = $query->selectRaw('
            MIN(id) as id,
            nama_penerima,
            nomor_telepon,
            COUNT(id) as total_orders,
            MIN(is_joined) as is_joined,
            MIN(is_dormant) as is_dormant,
            MIN(status_customer) as status_customer,
            MIN(which_hp) as which_hp
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

        $dataTable->addColumn('is_dormant', function ($row) {
            if ($row->is_dormant == 1) {
                return '
                    <button class="btn btn-sm bg-danger" 
                        data-id="' . $row->id . '">
                        <i class="fas fa-user-clock"></i> Dormant
                    </button>
                ';
            } else {
                return '
                    <button class="btn btn-sm bg-success" 
                        data-id="' . $row->id . '">
                        <i class="fas fa-user-check"></i> Active
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
                
        return $dataTable->rawColumns(['is_joined', 'is_dormant', 'details'])->make(true);
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

        if (!$request->month && !$request->produk) {
            $lastMonth = now()->subMonth()->format('Y-m');
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$lastMonth]);
        }

        if ($request->has('month') && $request->month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$request->month]);
        }

        if ($request->has('produk') && $request->produk) {
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$request->produk]);
        }

        $uniqueCount = $query->distinct('nomor_telepon')
                        ->count('nomor_telepon');

        $loyalisCount = (clone $query)->where('status_customer', 'Loyalis')
                            ->distinct('nomor_telepon')
                            ->count('nomor_telepon');

        $prioritasCount = (clone $query)->where('status_customer', 'Prioritas')
                            ->distinct('nomor_telepon')
                            ->count('nomor_telepon');

        $newCount = (clone $query)->where('status_customer', 'New Customer')
                            ->distinct('nomor_telepon')
                            ->count('nomor_telepon');

        return response()->json([
            'unique_customer_count' => $uniqueCount,
            'loyalis_count' => $loyalisCount,
            'prioritas_count' => $prioritasCount,
            'new_count' => $newCount
        ]);
    }

    public function getProductCounts(Request $request)
    {
        $query = CustomersAnalysis::query();

        if (!$request->month && !$request->produk) {
            $lastMonth = now()->subMonth()->format('Y-m');
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$lastMonth]);
        }

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }

        if ($request->has('produk') && $request->produk) {
            $produk = $request->produk;
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$produk]);
        }

        $data = $query->selectRaw('sku as short_name, COUNT(*) as total_count')
            ->whereNotNull('sku')
            ->groupBy('short_name')
            ->get();

        return response()->json($data);
    }

    public function getDailyUniqueCustomers(Request $request)
    {
        $query = CustomersAnalysis::query();

        if (!$request->month && !$request->produk) {
            $lastMonth = now()->subMonth()->format('Y-m');
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$lastMonth]);
        }
        
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
    public function importWhichHp()
    {
        try {
            $range = 'Import Which HP!A2:B';
            $sheetData = $this->googleSheetService->getSheetData($range);

            foreach ($sheetData as $row) {
                if (!empty($row[0])) {
                    CustomersAnalysis::where('nomor_telepon', $row[0])
                        ->update(['which_hp' => $row[1] ?? null]);
                }
            }

            return response()->json([
                'success' => true, 
                'message' => 'Which HP updated successfully.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to update which_hp: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Failed to update which_hp.'
            ], 500);
        }
    }
    public function importJoin()
    {
        try {
            $range = 'Import Status Customers!A2:C'; 
            $sheetData = $this->googleSheetService->getSheetData($range);

            foreach ($sheetData as $row) {
                $isJoined = strtolower($row[2]) === 'join' ? 1 : 0;

                $customerData = [
                    'nama_penerima'          => $row[0] ?? null,
                    'nomor_telepon'          => $row[1] ?? null,
                    'is_joined'              => $isJoined, 
                ];
                CustomersAnalysis::where('nama_penerima', $customerData['nama_penerima'])
                                ->where('nomor_telepon', $customerData['nomor_telepon'])
                                ->update(['is_joined' => $isJoined]);
            }

            return response()->json(['success' => true, 'message' => 'Customers updated successfully.'], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to update customers: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update customers.'], 500);
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
            ->get(['produk', 'tanggal_pesanan_dibuat', 'qty', 'status_customer']);

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
                    'status_customer' => $order->status_customer,
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

    // public function export(Request $request)
    // {
    //     $month = $request->input('month');
    //     $status = $request->input('status');
    //     $whichHp = $request->input('which_hp');
    //     return Excel::download(new CustomersAnalysisExport($month, $status, $whichHp), 'customer_analysis.xlsx');
    // }
    /**
     * Export customer data - smart handling for both small and large exports
     */
    public function export(Request $request)
    {
        $month = $request->input('month');
        $status = $request->input('status');
        $whichHp = $request->input('which_hp');
        $cities = $request->input('cities') ? explode(',', $request->input('cities')) : [];
        
        // Store export parameters
        $params = compact('month', 'status', 'whichHp', 'cities');
        
        // Count the records
        $count = $this->getApproximateExportCount($month, $status, $whichHp, $cities);
        
        // If small dataset, do regular export with formatted filename
        if ($count <= 1500) {
            $filename = 'customer_analysis';
            
            $formatParams = [];
            if (!empty($month)) $formatParams[] = date('M_Y', strtotime($month . '-01'));
            if (!empty($status)) $formatParams[] = str_replace(' ', '_', $status);
            if (!empty($whichHp)) $formatParams[] = str_replace(' ', '_', $whichHp);
            if (!empty($cities)) $formatParams[] = 'Cities_' . count($cities); // Add number of selected cities
            
            if (!empty($formatParams)) {
                $filename .= '_' . implode('_', $formatParams);
            }
            $filename .= '_' . date('Y-m-d') . '.xlsx';
            
            return Excel::download(
                new CustomersAnalysisExport($month, $status, $whichHp, $cities), 
                $filename
            );
        }
        
        // For large dataset, queue the export
        $exportId = md5(time() . rand(1000, 9999));
        
        // Store the export parameters for later use
        session(['export_params_' . $exportId => $params]);
        
        ProcessLargeExport::dispatch(
            $month,
            $status, 
            $whichHp,
            $cities, // Now passing array of cities
            $exportId,
            auth()->id()
        );
        
        return redirect()->route('customer_analysis.export_status', ['id' => $exportId]);
    }

    /**
     * Show export status page
     */
    public function showStatus($id)
    {
        // Check if export exists yet
        $exists = Storage::exists("exports/{$id}.zip");
        
        return view('admin.customers_analysis.export-status', [
            'exportId' => $id,
            'completed' => $exists,
            'downloadUrl' => route('customer_analysis.export_download', ['id' => $id])
        ]);
    }

    /**
     * Download a completed export
     */
    public function download($id)
    {
        $path = storage_path("app/exports/{$id}.zip");
        
        if (file_exists($path)) {
            // Format export filename based on parameters
            $formatParams = [];
            
            // Get export parameters from session if available
            if (session()->has('export_params_' . $id)) {
                $params = session('export_params_' . $id);
                if (!empty($params['month'])) $formatParams[] = date('M_Y', strtotime($params['month'] . '-01'));
                if (!empty($params['status'])) $formatParams[] = str_replace(' ', '_', $params['status']);
                if (!empty($params['which_hp'])) $formatParams[] = str_replace(' ', '_', $params['which_hp']);
            }
            
            // Create formatted filename
            $filename = 'customer_analysis';
            if (!empty($formatParams)) {
                $filename .= '_' . implode('_', $formatParams);
            }
            $filename .= '_' . date('Y-m-d') . '.zip';
            
            // Return file and delete after sending
            return response()->download($path, $filename)->deleteFileAfterSend(true);
        }
        
        return redirect()->route('customer_analysis.export_status', ['id' => $id])
                        ->with('error', 'Export is still processing.');
    }

    /**
     * Get approximate count of records to be exported
     */
    private function getApproximateExportCount($month, $status, $whichHp, $cities)
    {
        $query = CustomersAnalysis::query();
        
        if ($month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }
        
        if ($status) {
            $query->where('status_customer', $status);
        }
        
        if ($whichHp) {
            $query->where('which_hp', $whichHp);
        }
        
        if (!empty($cities)) {
            $query->whereIn('kota_kabupaten', $cities);
        }
        
        return $query->distinct('nomor_telepon')->count('nomor_telepon');
    }
    public function getCityCounts(Request $request)
    {
        $query = CustomersAnalysis::query();

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }
        if ($request->has('kota_kabupaten') && $request->kota_kabupaten) {
            $kotaKabupaten = $request->kota_kabupaten;
            $query->where('kota_kabupaten', $kotaKabupaten);
        }
        $data = $query->selectRaw('kota_kabupaten, COUNT(*) as total_count')
            ->groupBy('kota_kabupaten')
            ->get();

        return response()->json($data);
    }
    public function getTrendData(Request $request)
    {
        $query = CustomerMonitor::query()
            ->where('tenant_id', Auth::user()->current_tenant_id);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        $data = $query->orderBy('date')->get();

        $labels = CustomerMonitor::where('tenant_id', Auth::user()->current_tenant_id)
            ->orderBy('date')
            ->pluck('date')
            ->unique()
            ->map(function($date) {
                return Carbon::parse($date)->format('F Y');
            })->values();

        $allDates = CustomerMonitor::where('tenant_id', Auth::user()->current_tenant_id)
            ->orderBy('date')
            ->pluck('date')
            ->unique()
            ->values();

        $datasets = [];
        $colors = [
            'Loyalis' => ['#0D6EFD', '#0D6EFD20'],
            'Prioritas' => ['#198754', '#19875420'],
            'New Customer' => ['#17A2B8', '#17A2B820']
        ];

        foreach($data->groupBy('status') as $status => $values) {
            $countsByDate = collect($allDates)->mapWithKeys(function($date) {
                return [$date => 0];
            });

            foreach($values as $value) {
                $countsByDate[$value->date] = $value->count_customer;
            }

            $datasets[] = [
                'label' => $status,
                'data' => $countsByDate->values(),
                'fill' => false,
                'borderColor' => $colors[$status][0],
                'backgroundColor' => $colors[$status][1],
                'tension' => 0.4
            ];
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => $datasets
        ]);
    }
}
