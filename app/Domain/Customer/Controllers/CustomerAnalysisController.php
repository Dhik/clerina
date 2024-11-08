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

use App\Domain\Customer\Exports\CustomersExport;

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
        // Start with a base query
        $query = CustomersAnalysis::query();

        // Apply month filter if provided
        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
        }

        // After filtering by month, group by 'nama_penerima' and 'nomor_telepon' and calculate sum of 'qty'
        $query = $query->selectRaw('nama_penerima, nomor_telepon, SUM(qty) as total_qty')
            ->groupBy('nama_penerima', 'nomor_telepon');

        return DataTables::of($query)
            ->make(true);
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

        $uniqueCount = $query->select('nama_penerima', 'nomor_telepon')
                            ->distinct()
                            ->count();

        return response()->json(['unique_customer_count' => $uniqueCount]);
    }
    public function getProductCounts(Request $request)
    {
        $query = CustomersAnalysis::query();

        if ($request->has('month') && $request->month) {
            $month = $request->month;
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$month]);
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

        $dailyCounts = $query->selectRaw('DATE(tanggal_pesanan_dibuat) as date, COUNT(DISTINCT CONCAT(nama_penerima, nomor_telepon)) as unique_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($dailyCounts);
    }


}
