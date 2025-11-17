<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

describe('Authentication', function () {

    it('permite registrar un usuario', function () {

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $response = $this->postJson('/api/register', [
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertStatus(201);
        expect($response->json())->toHaveKeys(['status', 'data', 'message']);
        expect($response->json('status'))->toBe('success');
        expect($response->json('data'))->toHaveKey('user');
    });

    it('permite loguear un usuario registrado', function () {
        // Crear un usuario primero
        $user = User::factory()->create([
            'email' => 'juan@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'juan@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200);
        expect($response->json())->toHaveKeys(['status', 'data', 'message']);
        expect($response->json('status'))->toBe('success');
        expect($response->json('data'))->toHaveKey('token');
    });

    it('rechaza login con credenciales inválidas', function () {
        $user = User::factory()->create([
            'email' => 'juan@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'juan@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        expect($response->json('status'))->toBe('error');
    });

    it('valida campos requeridos en registro', function () {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422);
        expect($response->json())->toHaveKey('errors');
    });
});
