<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Services\UI\Support\UIDebug;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $fullName = trim($request->first_name . ' ' . $request->last_name);

        $user = User::create([
            'name' => $fullName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol por defecto
        $user->assignRole('user');

        // Intentar enviar email de verificación (sin bloquear el registro si falla)
        $this->trySendEmail(
            fn() => event(new Registered($user)),
            'registro de usuario'
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        // Obtener permisos del usuario
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $permissions,
                ],
                'token' => $token,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            UIDebug::error('Credenciales inválidas para el email: ' . $request->email);
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciales inválidas',
                'errors' => ['email' => ['The provided credentials are incorrect.']]
            ], 401);
        }

        // Determinar el nombre del token según "remember"
        $tokenName = $request->remember ? 'auth_token_remember' : 'auth_token';

        // Crear token con expiración según "remember"
        if ($request->remember) {
            // Token con duración extendida (30 días)
            $token = $user->createToken($tokenName, ['*'], now()->addDays(30))->plainTextToken;
        } else {
            // Token con duración estándar (24 horas)
            $token = $user->createToken($tokenName, ['*'], now()->addDay())->plainTextToken;
        }

        // Obtener permisos del usuario usando Spatie
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Autenticación exitosa',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ],
                'token' => $token,
                'remember' => $request->remember ?? false,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'status' => 'success',
            'data' => null,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
    public function verifyEmail(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
                'errors' => null
            ], 404);
        }

        // Verificar que el hash coincida con el email del usuario
        $expectedHash = sha1($user->email);
        $providedHash = $request->route('hash');

        if ($expectedHash !== $providedHash) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link',
                'errors' => null
            ], 400);
        }

        // El middleware 'signed' ya validó la firma y expiración
        // Si llegamos aquí, la URL es válida

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Email already verified'
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            if ($user instanceof MustVerifyEmail) {
                event(new Verified($user));
            }
        }

        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'Email verified successfully'
        ], 200);
    }
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Email already verified'
            ], 200);
        }

        $sent = $this->trySendEmail(
            fn() => $request->user()->sendEmailVerificationNotification(),
            'reenvío de verificación'
        );

        if ($sent) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'Verification email sent'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'data' => null,
            'message' => 'No se pudo enviar el email de verificación. El servidor de correo no está disponible.'
        ], 503);
    }

    /**
     * Obtener usuario autenticado
     */
    public function user(Request $request)
    {
        $user = $request->user();

        // Obtener permisos usando Spatie
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();
        $roles = $user->getRoleNames()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ],
            ]
        ]);
    }

    /**
     * Enviar enlace de reset de contraseña
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar que el usuario existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'We can\'t find a user with that email address.',
                'errors' => ['email' => ['We can\'t find a user with that email address.']]
            ], 404);
        }

        // Verificar disponibilidad del servidor de email antes de intentar
        if (!$this->isMailServerAvailable()) {
            Log::warning('Intento de recuperación de contraseña sin servidor de email disponible');
            return response()->json([
                'status' => 'error',
                'message' => 'El servidor de correo no está disponible. Por favor, intenta más tarde.',
                'errors' => ['email' => ['Servidor de correo no disponible']]
            ], 503);
        }

        $sent = $this->trySendEmail(
            function() use ($request, &$status) {
                $status = Password::sendResetLink($request->only('email'));
            },
            'recuperación de contraseña'
        );

        if ($sent && isset($status) && $status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'Password reset link sent to your email address'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'No se pudo enviar el email de recuperación.',
            'errors' => ['email' => ['No se pudo enviar el email de recuperación']]
        ], 500);
    }

    /**
     * Resetear la contraseña
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'status' => 'success',
                'data' => null,
                'message' => 'Password has been reset successfully'
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unable to reset password',
            'errors' => ['email' => [__($status)]]
        ], 400);
    }

      /**
     * Verificar si el servidor de email está disponible
     */
    private function isMailServerAvailable(): bool
    {
        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port');

        if (!$mailHost || !$mailPort) {
            return false;
        }

        // Intentar conectar con timeout de 2 segundos
        $connection = @fsockopen($mailHost, $mailPort, $errno, $errstr, 2);

        if ($connection) {
            fclose($connection);
            return true;
        }

        Log::info("Servidor de email no disponible: {$mailHost}:{$mailPort} - {$errstr}");
        return false;
    }

    /**
     * Intentar enviar email de forma segura
     */
    private function trySendEmail(callable $callback, string $errorContext): bool
    {
        if (!$this->isMailServerAvailable()) {
            Log::warning("Email no enviado ({$errorContext}): Servidor de email no disponible");
            return false;
        }

        try {
            $callback();
            return true;
        } catch (\Exception $e) {
            Log::warning("Error al enviar email ({$errorContext}): " . $e->getMessage());
            return false;
        }
    }
}
