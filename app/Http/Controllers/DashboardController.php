<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Send role-bearing users to their shell; otherwise show the generic dashboard.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        /** @var User $user */
        $user = $request->user();

        $route = $user->dashboardRoute();

        if ($route !== 'dashboard') {
            return redirect()->route($route);
        }

        return view('dashboard');
    }
}
