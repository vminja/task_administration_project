<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class RegisterController extends Controller
{

    public function register(Request $request)
    {
        //validacija podataka
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|confirmed|min:6',
            'full_name' => 'required',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'birth_date' => 'nullable|date'
        ], 
        [
            'username.required' => 'Korisničko ime je obavezno.',
            'username.unique' => 'Korisničko ime već postoji.',
            'password.required' => 'Lozinka je obavezna.',
            'password.confirmed' => 'Lozinke se ne poklapaju.',
            'password.min' => 'Lozinka mora imati najmanje :min karaktera.',
            'full_name.required' => 'Ime i prezime su obavezni.',
            'email.required' => 'Email je obavezan.',
            'email.email' => 'Email mora biti validan.',
            'email.unique' => 'Email već postoji.',
        ]);

        //generisanje random tokena za aktivaciju
        $token = Str::random(64);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password), // hashovanje lozinke
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'type' => 'admin',
            'activation_token' => $token,
            'activation_expires_at' => null,
        ]);

        // // Pošalji aktivacioni mejl
        Mail::to($user->email)->send(new \App\Mail\ActivationMail($user));

        return response()->json(['message' => 'Link za aktivaciju Vam je poslat na email adresu.']);
    }
}
