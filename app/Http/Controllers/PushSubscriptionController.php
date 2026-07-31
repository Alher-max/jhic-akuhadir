<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
            'keys.p256dh' => 'required',
            'keys.auth' => 'required',
        ]);

        $endpoint = $request->endpoint;
        $token = $request->keys['auth'];
        $key = $request->keys['p256dh'];
        $user = Auth::user();

        PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'user_id' => $user->id,
                'public_key' => $key,
                'auth_token' => $token,
                'content_encoding' => $request->content_encoding ?? 'aesgcm',
            ]
        );

        return response()->json(['success' => true], 200);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'endpoint' => 'required',
        ]);

        PushSubscription::where('endpoint', $request->endpoint)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true], 200);
    }
}
