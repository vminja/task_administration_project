<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetLinkController extends Controller
{
    public function resetPasswordLink($token)
    {
        $user = User::where('reset_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Nevažeći token za promenu lozinke.'], 404);
        }

        if($user->reset_expires_at != null){
            // Proveravamo da li je token istekao
            if (Carbon::now()->gt($user->reset_expires_at)) {
                return response()->json(['message' => 'Token je istekao.'], 410);
            }

        }

        //Ako je sve u redu idemo na stranicu za resetovanje lozinke
        return view('resetPasswordForm', ['email' => $user->email, 'token' => $token]);
    }

    public function submitNewPassword(Request $request)
    {
        // dd($request->all());
        // Validacija podataka
        $request->validate([
            'newPassword.password' => 'required|min:6|confirmed',
            'token' => 'required|string'
        ], 
        [
            'newPassword.password.required' => 'Lozinka je obavezna.',
            'newPassword.password.min' => 'Lozinka mora imati najmanje :min karaktera.',
            'newPassword.password.confirmed' => 'Lozinke se ne poklapaju.',
            'token.required' => 'Token za resetovanje je obavezan.'
        ]);

        $user = User::where('reset_token', $request->token)->first();

        if (!$user) {
            return response()->json(['message' => 'Nevažeći token za promenu lozinke.'], 404);
        }

        // Promena lozinke
        $user->password = Hash::make($request->newPassword['password']);
        $user->reset_token = null; // Resetovanje tokena
        $user->reset_expires_at = null; // Resetovanje vremena isteka
        $user->save();

        return response()->json(['message' => 'Lozinka je uspešno promenjena.']);
    }
}
