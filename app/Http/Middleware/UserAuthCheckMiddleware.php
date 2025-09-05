<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserAuthCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(123);

         if (
            $request->is('login') ||
            $request->is('submitLogin') ||
            $request->is('register') ||
            $request->is('subminRegister') ||
            $request->is('activate/*') ||
            $request->is('resetLink/*') ||
            $request->is('sendResetLink') ||
            $request->is('submitNewPassword') ||
            $request->is('resendActivationLink')
        ) {
            return $next($request);
        }

        // Proveri da li je korisnik uogovan
        if (Auth::check()) {
            return $next($request);
        }

        // Ako korisnik nije autentifikovan preusmeri na stranicu za prijavu
        return redirect('/login');
    }

}
