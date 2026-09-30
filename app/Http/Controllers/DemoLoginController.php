<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DemoLoginController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $accounts = config('demo.accounts');
        $validated = $request->validate([
            'email' => ['required', 'email', Rule::in(array_keys($accounts))],
        ]);

        $account = $accounts[$validated['email']];
        $user = User::query()
            ->where('email', $validated['email'])
            ->where('role', $account['role'])
            ->where('is_active', true)
            ->first();

        abort_unless($user, 404, 'Akun demo tidak tersedia atau tidak aktif.');

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route($account['dashboard']);
    }
}
