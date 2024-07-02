<?php

namespace App\Domain\Customer\Controllers;

use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Customer\Requests\CustomerNoteRequest;
use App\Domain\Order\Models\Order;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

class CustomerNoteController extends Controller
{
    public function __construct(protected CustomerBLLInterface $customerBLL)
    {
    }

    /**
     * @throws Exception
     */
    public function getCustomerNote(Request $request): JsonResponse
    {
        $this->authorize('viewCustomer', Customer::class);

        $query = $this->customerBLL->getCustomerNoteDatatable($request);

        $currentUser = Auth::user();

        return DataTables::of($query)
            ->addColumn('actions', function ($row) use ($currentUser) {
                $actionsButton = '<button class="btn btn-success btn-sm updateButton">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="btn btn-danger btn-sm deleteButton">
                                    <i class="fas fa-trash-alt"></i>
                                </button>';

                if ($currentUser->hasRole(RoleEnum::SuperAdmin) || $currentUser->hasRole(RoleEnum::BrandManager)) {
                    return $actionsButton;
                } else if ($currentUser->can(PermissionEnum::UpdateCustomerNote) && $currentUser->id === $row->user_id) {
                    return $actionsButton;
                }

                return '';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Create new customer note
     */
    public function store(CustomerNoteRequest $request): JsonResponse
    {
        $this->authorize('createCustomerNote',  CustomerNote::class);

        $this->customerBLL->storeCustomerNote($request, Auth::user()->id);

        return response()->json($request->all());
    }

    /**
     * Update social media
     */
    public function update(CustomerNote $customerNote, CustomerNoteRequest $request): JsonResponse
    {
        $this->authorize('updateCustomerNote', [CustomerNote::class, $customerNote]);

        $this->customerBLL->updateCustomerNote($customerNote, $request);

        return response()->json($request->all());
    }

    /**
     * Delete social media
     */
    public function delete(CustomerNote $customerNote): JsonResponse
    {
        $this->authorize('deleteCustomerNote', [CustomerNote::class, $customerNote]);

        $this->customerBLL->deleteCustomerNote($customerNote);

        return response()->json(['message' => trans('messages.success_delete')]);
    }
}
