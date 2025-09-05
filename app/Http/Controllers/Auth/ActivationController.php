<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;

class ActivationController extends Controller
{
    public function activate($token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Nevažeći aktivacioni token.'], 404);
        }

        if($user->activation_expires_at != null){

            if (Carbon::now()->gt($user->activation_expires_at)) {
                return response()->json(['message' => 'Token je istekao. Kliknite na "pošalji ponovo".'], 410);
            }

        }
        

        $user->is_active = true;
        $user->activation_token = null;
        $user->activation_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Nalog je uspešno aktiviran.']);
    }
}
