<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['headmaster', 'kepala_sekolah', 'owner']) || $user->onboarding_completed) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.onboarding');
    }

    public function store(Request $request)
    {
        // Pastikan onboarding belum selesai
        $user = Auth::user();
        if ($user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'onboarding_option' => 'required|in:self,delegate',
            'delegate_email' => 'required_if:onboarding_option,delegate|nullable|email|max:255',
        ]);

        if ($request->onboarding_option === 'delegate') {
            Invitation::create([
                'tenant_id' => $user->tenant_id,
                'email' => $request->delegate_email,
                'token' => Str::random(32),
                'status' => 'pending',
            ]);
        }

        // Tandai onboarding selesai
        $user->update(['onboarding_completed' => true]);

        return redirect()->route('dashboard');
    }
}
