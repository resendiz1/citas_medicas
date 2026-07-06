<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request)
    {
        $role = $request->query('role', 'paciente');

        if (!in_array($role, ['medico', 'paciente'])) {
            $role = 'paciente';
        }

        session(['google_role' => $role]);

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['email' => 'Error al autenticar con Google.']);
        }

        $role = session('google_role', 'paciente');
        session()->forget('google_role');

        $user = User::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                $user->update(['google_id' => $googleUser->getId(), 'avatar' => $googleUser->getAvatar()]);
            } else {
                $user = User::create([
                    'name'      => $googleUser->getName() ?? $googleUser->getNickname() ?? 'Usuario',
                    'email'     => $googleUser->getEmail(),
                    'password'  => Hash::make(Str::random(32)),
                    'role'      => $role,
                    'google_id' => $googleUser->getId(),
                    'avatar'    => $googleUser->getAvatar(),
                ]);
            }
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}
