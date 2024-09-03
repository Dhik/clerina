<?php

namespace App\Domain\User\Controllers;

use App\Domain\Tenant\BLL\Tenant\TenantBLLInterface;
use App\Domain\User\BLL\User\UserBLLInterface;
use App\Domain\Employee\BLL\Employee\EmployeeBLLInterface;
use App\Domain\User\Models\User;
use App\Domain\User\Requests\ResetPasswordRequest;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Requests\UserRequest;
use App\Domain\User\Requests\UserUpdateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use App\Domain\Employee\Models\Employee;
use App\Domain\Employee\Models\Shift;

class UserController extends Controller
{
    public function __construct(
        public UserBLLInterface $userBLL,
        public TenantBLLInterface $tenantBLL,
        public EmployeeBLLInterface $employeeBLL
    ) {
    }

    /**
     * Get user list for datatables
     */
    public function get(): JsonResponse
    {
        $userQuery = $this->userBLL->getUserDataTable();

        return DataTables::of($userQuery)
            ->filter(function ($query) {
                $searchParam = request()->search['value'];

                if (! empty($searchParam)) {
                    $query->orWhereHas('roles', function ($query) use ($searchParam) {
                        $query->where('name', 'like', '%'.$searchParam.'%');
                    });
                }
            }, true)
            ->addColumn('roles', function ($row) {
                return $row->roles->pluck('name')->map(function ($value, $key) {
                    return '<span class="badge bg-primary">'.$value.'</span>';
                })->implode(' ');
            })
            ->addColumn('tenants', function ($row) {
                return $row->tenants->pluck('name')->map(function ($value, $key) {
                    return '<span class="badge bg-success">'.$value.'</span>';
                })->implode(' ');
            })
            ->addColumn('actions',
                '<a href="{{ URL::route( \'users.show\', array( $id )) }}" class="btn btn-primary btn-xs" >
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ URL::route( \'users.edit\', array( $id )) }}" class="btn btn-success btn-xs" >
                            <i class="fas fa-pencil-alt"></i>
                        </a> '
            )
            ->rawColumns(['actions', 'roles', 'tenants'])
            ->toJson();
    }

    /**
     * Display a listing of the users.
     */
    public function index(): View|ApplicationAlias|Factory|Application
    {
        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize(PermissionEnum::CreateUser, User::class);

        $roles = $this->userBLL->getAllRoles();
        $tenants = $this->tenantBLL->getAllTenants();
        $shifts = Shift::all(); // Fetch all shifts

        return view('admin.user.create', compact('roles', 'tenants', 'shifts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request): RedirectResponse
{
    $this->authorize(PermissionEnum::CreateUser, User::class);

    $user = $this->userBLL->createUser($request);

    // Create employee
    $employeeData = [
        'employee_id' => $request->employee_id,
        'full_name' => $request->name,
        'job_position' => $request->position,
        'job_level' => $request->job_level,
        'organization' => $request->organization,
        'join_date' => $request->join_date,
        'status_employee' => $request->status_employee,
        'birth_date' => $request->birth_date,
        'birth_place' => $request->birth_place,
        'age' => $request->age,
        'citizen_id_address' => $request->citizen_id_address,
        'residential_address' => $request->residential_address,
        'email' => $request->email,
        'mobile_phone' => $request->phone_number,
        'shift_id' => $request->shift_id,
    ];

    $this->employeeBLL->createEmployee($employeeData);

    return redirect()
        ->route('users.show', $user->id)
        ->with([
            'alert' => 'success',
            'message' => trans('messages.success_save', ['model' => trans('labels.user')]),
        ]);
}


    /**
     * Display the specified resource.
     */
    public function show(User $user): View|ApplicationAlias|Factory|Application
    {
        $this->authorize(PermissionEnum::ViewUser, [User::class, $user]);

        $roles = $this->userBLL->getRoleUser($user);
        $tenants = $user->tenants()->get()->pluck('name');

        return view('admin.user.show', compact('user', 'roles', 'tenants'));
    }

    public function show_detail(User $user): View|ApplicationAlias|Factory|Application
    {
        $this->authorize(PermissionEnum::ViewUser, [User::class, $user]);

        $roles = $this->userBLL->getRoleUser($user);
        $tenants = $user->tenants()->get()->pluck('name');

        return view('admin.user.show_detail', compact('user', 'roles', 'tenants'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View|ApplicationAlias|Factory|Application
    {
        $this->authorize(PermissionEnum::UpdateUser, [User::class, $user]);

        $roles = $this->userBLL->getAllRoles();
        $tenants = $this->tenantBLL->getAllTenants();

        return view('admin.user.edit', compact('user', 'roles', 'tenants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(User $user, UserUpdateRequest $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::UpdateUser, [User::class, $user]);

        $this->userBLL->updateUser($user, $request);

        return redirect()
            ->route('users.show', $user->id)
            ->with([
                'alert' => 'success',
                'message' => trans('messages.success_update', ['model' => trans('labels.user')]),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        $this->authorize(PermissionEnum::DeleteUser, [User::class, $user]);

        $this->userBLL->deleteUser($user);

        return response()->json(['message' => trans('messages.success_delete')]);
    }

    /**
     * Reset password by admin
     */
    public function resetPassword(User $user): View|ApplicationAlias|Factory|Application
    {
        $this->authorize(PermissionEnum::UpdateUser, [User::class, $user]);

        return view('admin.user.reset-password', compact('user'));
    }

    /**
     * Process Reset password by admin
     */
    public function updateResetPassword(User $user, ResetPasswordRequest $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::UpdateUser, [User::class, $user]);

        $this->userBLL->resetPassword($user, $request);

        return redirect()->route('users.show', $user->id)->with([
            'alert' => 'success',
            'message' => trans('messages.success_update', ['model' => trans('labels.password')]),
        ]);
    }

    /**
     * View profile
     */
    public function viewProfile(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewProfile', User::class);

        $user = Auth::user();
        $roles = $this->userBLL->getRoleUser($user);
        $tenants = $user->tenants()->get()->pluck('name');

        return view('admin.user.show', compact('user', 'roles', 'tenants'));
    }

    /**
     * View page reset password
     */
    public function changePassword(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('changeOwnPassword', User::class);

        $user = Auth::user();

        return view('admin.user.change-own-password', compact('user'));
    }

    /**
     * Post change own password
     */
    public function postChangePassword(ResetPasswordRequest $request): RedirectResponse
    {
        $this->authorize('changeOwnPassword', User::class);

        $this->userBLL->resetPassword(Auth::user(), $request);

        return redirect()->route('user.profile')->with([
            'alert' => 'success',
            'message' => trans('messages.success_update', ['model' => trans('labels.password')]),
        ]);
    }
}
