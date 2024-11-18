<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validación de datos
            $validator = $this->validateRequest($request);

            // Si hay errores de validación, lanzamos una excepción
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Crear el usuario
            $user = $this->createUser($request);

            // Retornar respuesta de éxito
            return response()->json([
                'message' => 'Usuario registrado con éxito.',
                'user' => $user,
            ], 201);

        } catch (ValidationException $e) {
            // Manejo de errores de validación
            return response()->json([
                'message' => 'Errores de validación.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            // Manejo de otros errores generales
            return response()->json([
                'message' => 'Hubo un error al registrar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function validateRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8', // al menos 8 caracteres
                'regex:/[a-z]/', // al menos una minúscula
                'regex:/[A-Z]/', // al menos una mayúscula
                'regex:/[0-9]/', // al menos un número
                'regex:/[@$!%*?&]/', // al menos un carácter especial
            ],
        ], $this->customMessages());
    }

    protected function customMessages()
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Por favor, ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una letra minúscula, un número y un carácter especial.',
        ];
    }

    protected function createUser(Request $request)
    {
        // $now = Carbon::now();

        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'created_at' => $now,
            // 'updated_at' => $now,
        ]);
    }
}
