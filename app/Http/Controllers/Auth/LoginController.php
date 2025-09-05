<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;



class LoginController extends Controller
{
    public function login(Request $request)
    {

        //validacija podataka
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        //izvlacimo email i pasword 
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // dd($user);
            //provera da li je korisnik aktivirao nalog
            if ($user->is_active == false) {

                Auth::logout();
                return response()->json(['message' => 'Niste aktivirali nalog. Aktivacioni link Vam je poslat na email adresu.'], 403);
            }

            $request->session()->regenerate();

            return redirect('/taskAdministration');
        }

        return response()->json(['message' => 'Uneli ste pogrešnu email adresu ili lozinku.'], 401);
        
    }

    public  function resendActivationLink(Request $request)
    {
        //validacija podataka
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Korisnik sa ovom email adresom ne postoji. Unesite validnu email adresu.'], 404);
        }

        if ($user->is_active == true) {
            return response()->json(['message' => 'Vaš nalog je već aktiviran.'], 400);
        }

        //generisanje novog tokena
        $token = Str::random(64);
        $user->activation_token = $token;
        $user->activation_expires_at = null;
        $user->save();

        Mail::to($user->email)->send(new \App\Mail\ActivationMail($user));

        return response()->json(['message' => 'Link za aktivaciju naloga Vam je ponovo poslat na email adresu.']);
        
    }

    public function sendResetLink(Request $request)
    {
        // dd($request->all());
        //validacija email adrese
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        //provera da li korisnik postoji
        //ako postoji nastavljamo, ako ne postoji samo ignorišemo
        if ($user) {
           
            //generisanje tokena za resetovanje lozinke
            $token = Str::random(64);
            $user->reset_token = $token;
            $user->reset_expires_at = now()->addMinutes(30);
            $user->save();

            Mail::to($user->email)->send(new \App\Mail\ResetPasswordLinkMail($user));
        }

        return response()->json(['message' => 'Link za resetovanje lozinke Vam je poslat na email adresu.']);

    }
}
