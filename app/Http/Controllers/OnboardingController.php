<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService
    ) {}

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
            'timezone' => 'nullable|string|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
        ]);

        if ($user->tenant && $request->filled('timezone')) {
            $user->tenant->update(['timezone' => $request->timezone]);
        }

        if ($request->onboarding_option === 'delegate') {
            $this->invitationService->inviteOperator($request->delegate_email, $user->tenant_id);
        }

        // Tandai onboarding selesai
        $user->update(['onboarding_completed' => true]);

        return redirect()->route('dashboard');
    }
}
