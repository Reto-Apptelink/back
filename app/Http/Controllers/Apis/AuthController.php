<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'Debe ingresar un correo electrónico válido.',
                'password.required' => 'La contraseña es obligatoria.'
            ]);

            $user = User::where('email', $request->email)->first();

            // Verificar contraseña y devolver token
            if ($user && Hash::check($request->password, $user->password)) {

                $user->tokens()->delete();

                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status' => 'success',
                    'token' => $token,
                    'data' => $user,
                    // 'redirect_url' => route('dashboard') // URL protegida
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciales incorrectas. Verifique su correo y contraseña.'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error en el servidor. Intente de nuevo más tarde.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        // Revocar el token actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada con éxito'
        ], 200);
    }

    /* public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'message' => 'El enlace de recuperación de contraseña se ha enviado a su correo.'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo enviar el enlace de recuperación. Verifique su correo e intente nuevamente.'
            ], 400);
        }
    } */

    /* public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ], [
            'token.required' => 'El token de recuperación es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Debe ingresar un correo electrónico válido.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'message' => 'La contraseña se ha restablecido correctamente.'
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'El token de recuperación no es válido o ha expirado.'
            ], 400);
        }
    } */
}
