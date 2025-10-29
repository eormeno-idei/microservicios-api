<?php

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CustomVerifyEmailNotification;

describe('Email Verification', function () {

    // RefreshDatabase trait handles database setup automatically

    it('envía notificación de verificación cuando se registra un usuario', function () {
        Notification::fake();

        $userData = [
            'name' => 'Test User',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);

        $user = User::where('email', 'test@example.com')->first();

        // Verificar que el usuario fue creado sin verificación de email
        expect($user)->not->toBeNull()
            ->and($user->email_verified_at)->toBeNull();

        // Verificar que se envió la notificación de verificación
        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    });

    it('verifica el email con un enlace válido', function () {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email_verified_at' => null,
        ]);

        // Generar URL de verificación firmada
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // Extraer componentes de la URL
        $parsedUrl = parse_url($verificationUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        $response = $this->get($path . '?' . $query);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Email verified successfully']);

        // Verificar que el email fue marcado como verificado
        $user->refresh();
        expect($user->email_verified_at)->not->toBeNull();
    });

    it('rechaza enlaces de verificación inválidos', function () {
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email_verified_at' => null,
        ]);

        // Crear URL firmada válida pero con hash incorrecto
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => 'wrong-hash',
            ]
        );

        // Extraer componentes de la URL
        $parsedUrl = parse_url($verificationUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        $response = $this->get($path . '?' . $query);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Invalid verification link']);

        // Verificar que el email no fue verificado
        $user->refresh();
        expect($user->email_verified_at)->toBeNull();
    });

    it('maneja intentos de verificación con usuario inexistente', function () {
        $nonExistentUserId = 99999;

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $nonExistentUserId,
                'hash' => sha1('test@example.com'),
            ]
        );

        $parsedUrl = parse_url($verificationUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        $response = $this->get($path . '?' . $query);

        $response->assertStatus(404)
            ->assertJson(['message' => 'User not found']);
    });

    it('maneja usuarios con email ya verificado', function () {
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email_verified_at' => now(),
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $parsedUrl = parse_url($verificationUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        $response = $this->get($path . '?' . $query);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Email already verified']);
    });

    it('permite reenviar email de verificación', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/email/resend');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Verification email sent']);

        Notification::assertSentTo($user, CustomVerifyEmailNotification::class);
    });

    it('no reenvía email si ya está verificado', function () {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/email/resend');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Email already verified']);

        Notification::assertNotSentTo($user, CustomVerifyEmailNotification::class);
    });

    it('requiere autenticación para reenviar email', function () {
        $response = $this->postJson('/api/email/resend');

        $response->assertStatus(401);
    });

    it('rechaza enlaces de verificación expirados', function () {
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email_verified_at' => null,
        ]);

        // Crear URL que ya expiró
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(10), // Expiró hace 10 minutos
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $parsedUrl = parse_url($expiredUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        $response = $this->get($path . '?' . $query);

        // Laravel's signed middleware returns 403 for expired URLs
        $response->assertStatus(403);

        // Verificar que el email no fue verificado
        $user->refresh();
        expect($user->email_verified_at)->toBeNull();
    });

    it('verifica que el hash coincida con el email del usuario', function () {
        $user = User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'correct@example.com',
            'email_verified_at' => null,
        ]);

        // Usar hash de un email diferente
        $wrongEmailHash = sha1('wrong@example.com');

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => $wrongEmailHash,
            ]
        );

        $parsedUrl = parse_url($verificationUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';

        $response = $this->get($path . '?' . $query);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Invalid verification link']);

        $user->refresh();
        expect($user->email_verified_at)->toBeNull();
    });
});
