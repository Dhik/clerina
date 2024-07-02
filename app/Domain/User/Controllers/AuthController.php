<?php

namespace App\Domain\User\Controllers;

use App\Domain\Tenant\BLL\Tenant\TenantBLLInterface;
use App\Domain\User\BLL\User\UserBLLInterface;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        protected UserBLLInterface $userBLL,
        protected TenantBLLInterface $tenantBLL
    ) {
    }

    /**
     * View login page
     */
    public function login(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('adminlte::auth.login');
    }
    public function absensi(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        return view('adminlte::auth.absensi');
    }

    /**
     * Verify login
     */
    public function loginVerify(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Authentication passed
            $this->tenantBLL->setDefaultTenant();

            return redirect()->intended(route('dashboard'));
        }

        // Authentication failed
        return back()->withErrors(['email' => trans('auth.password')]);
    }

    /**
     * Logout
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('auth.absensi');
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): View|\Illuminate\Foundation\Application|Factory|Application|RedirectResponse
    {
        $user = Auth::user();

        if ($user->can(PermissionEnum::ViewSales)) {
            return redirect()->route('sales.index');
        }

        return view('admin.dashboard');
    }
    public function absensiLoginVerify(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            // Authentication passed
            $this->tenantBLL->setDefaultTenant();

            return redirect()->route('attendance.absensi');
        }

        // Authentication failed
        return back()->withErrors(['email' => trans('auth.password')]);
    }
}
