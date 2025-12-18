<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Mostrar el perfil del usuario
     */
    public function show()
    {
        $user = Auth::user();

        // Últimos 20 logs de login del usuario
        $loginLogs = LoginLog::where('user_id', $user->id)
            ->orderBy('logged_at', 'desc')
            ->limit(20)
            ->get();

        return view('profile.show', compact('user', 'loginLogs'));
    }

    /**
     * Actualizar información del perfil
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Perfil actualizado correctamente');
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Contraseña actualizada correctamente');
    }

    /**
     * Ver historial de login
     */
    public function loginHistory()
    {
        $user = Auth::user();

        $loginLogs = LoginLog::where('user_id', $user->id)
            ->orderBy('logged_at', 'desc')
            ->paginate(50);

        return view('profile.login-history', compact('loginLogs'));
    }
}
