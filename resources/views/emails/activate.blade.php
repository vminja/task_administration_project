<h2>Zdravo, {{ $user->full_name }}</h2>

<p>Hvala što ste se registrovali. Da biste aktivirali nalog, kliknite na link ispod:</p>

<p><a href="{{ $activationUrl }}">{{ $activationUrl }}</a></p>
