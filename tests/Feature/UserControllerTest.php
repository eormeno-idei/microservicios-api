<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @property User $admin Usuario administrador para tests
 * @property User $user Usuario regular para tests
 */
beforeEach(function () {
    // Crear roles necesarios
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'editor']);
    Role::create(['name' => 'user']);

    // Crear usuario admin para las pruebas
    /** @var User $admin */
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    // Crear usuario sin rol admin
    /** @var User $user */
    $this->user = User::factory()->create();
    $this->user->assignRole('user');
});

// ============================================
// TESTS DE AUTENTICACIÓN Y AUTORIZACIÓN
// ============================================

test('usuarios no autenticados no pueden acceder a la API de users', function () {
    $response = $this->getJson('/api/users');

    $response->assertStatus(401);
});

test('usuarios sin rol admin no pueden acceder a la API de users', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(403);
});

test('usuarios con rol admin pueden acceder a la API de users', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(200);
});

// ============================================
// TESTS DE INDEX
// ============================================

test('index retorna lista de usuarios con paginación', function () {
    User::factory()->count(25)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'roles', 'created_at', 'updated_at']
            ],
            'pagination' => [
                'current_page',
                'total_pages',
                'per_page',
                'total_items',
                'from',
                'to',
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
        ]);
});

test('index puede buscar usuarios por nombre', function () {
    User::factory()->create(['name' => 'Juan Pérez']);
    User::factory()->create(['name' => 'María García']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users?search=Juan');

    $response->assertStatus(200)
        ->assertJsonFragment(['name' => 'Juan Pérez'])
        ->assertJsonMissing(['name' => 'María García']);
});

test('index puede buscar usuarios por email', function () {
    User::factory()->create(['email' => 'test@example.com']);
    User::factory()->create(['email' => 'otro@example.com']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users?search=test@');

    $response->assertStatus(200)
        ->assertJsonFragment(['email' => 'test@example.com']);
});

test('index puede ordenar por nombre ascendente', function () {
    User::factory()->create(['name' => 'Zulema']);
    User::factory()->create(['name' => 'Alberto']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users?sort_by=name&sort_direction=asc');

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data[0]['name'])->toBe('Alberto');
});

test('index puede ordenar por email descendente', function () {
    User::factory()->create(['email' => 'a@test.com']);
    User::factory()->create(['email' => 'z@test.com']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users?sort_by=email&sort_direction=desc');

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data[0]['email'])->toBe('z@test.com');
});

test('index puede ordenar por cantidad de roles', function () {
    $user1 = User::factory()->create();
    $user1->assignRole(['admin', 'editor']);

    $user2 = User::factory()->create();
    $user2->assignRole('user');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users?sort_by=roles&sort_direction=desc');

    $response->assertStatus(200);
    $data = $response->json('data');

    expect($data[0]['id'])->toBe($user1->id);
});

test('index muestra roles como string separado por comas y ordenado alfabéticamente', function () {
    $user = User::factory()->create();
    $user->assignRole(['editor', 'admin', 'user']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users');

    $response->assertStatus(200);

    $userData = collect($response->json('data'))
        ->firstWhere('id', $user->id);

    expect($userData['roles'])->toBe('admin, editor, user');
});

test('index puede cambiar items por página', function () {
    User::factory()->count(30)->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users?per_page=5');

    $response->assertStatus(200);

    expect($response->json('pagination.per_page'))->toBe(5)
        ->and(count($response->json('data')))->toBe(5);
});

// ============================================
// TESTS DE STORE
// ============================================

test('admin puede crear un nuevo usuario', function () {
    $userData = [
        'name' => 'Nuevo Usuario',
        'email' => 'nuevo@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/users', $userData);

    $response->assertStatus(201)
        ->assertJsonFragment([
            'message' => 'Usuario creado exitosamente',
        ])
        ->assertJsonPath('data.name', 'Nuevo Usuario')
        ->assertJsonPath('data.email', 'nuevo@example.com');

    $this->assertDatabaseHas('users', [
        'name' => 'Nuevo Usuario',
        'email' => 'nuevo@example.com',
    ]);
});

test('admin puede crear usuario con roles', function () {
    $userData = [
        'name' => 'Usuario con Roles',
        'email' => 'roles@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'roles' => ['editor', 'user'],
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/users', $userData);

    $response->assertStatus(201);

    $user = User::where('email', 'roles@example.com')->first();
    expect($user->hasRole('editor'))->toBeTrue()
        ->and($user->hasRole('user'))->toBeTrue();
});

test('no puede crear usuario con email duplicado', function () {
    $existingUser = User::factory()->create(['email' => 'existe@example.com']);

    $userData = [
        'name' => 'Nuevo Usuario',
        'email' => 'existe@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('no puede crear usuario sin confirmación de contraseña', function () {
    $userData = [
        'name' => 'Sin Confirmación',
        'email' => 'sinconf@example.com',
        'password' => 'Password123!',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/users', $userData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('no puede crear usuario con campos requeridos faltantes', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/users', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});

// ============================================
// TESTS DE SHOW
// ============================================

test('admin puede ver un usuario específico', function () {
    $user = User::factory()->create();
    $user->assignRole('editor');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.name', $user->name)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'roles' => [],
            ]
        ]);
});

test('show retorna 404 para usuario inexistente', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/users/99999');

    $response->assertStatus(404);
});

// ============================================
// TESTS DE UPDATE
// ============================================

test('admin puede actualizar un usuario', function () {
    $user = User::factory()->create();

    $updateData = [
        'name' => 'Nombre Actualizado',
        'email' => 'actualizado@example.com',
    ];

    $response = $this->actingAs($this->admin, 'sanctum')
        ->putJson("/api/users/{$user->id}", $updateData);

    $response->assertStatus(200)
        ->assertJsonFragment([
            'message' => 'Usuario actualizado exitosamente',
        ])
        ->assertJsonPath('data.name', 'Nombre Actualizado')
        ->assertJsonPath('data.email', 'actualizado@example.com');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nombre Actualizado',
        'email' => 'actualizado@example.com',
    ]);
});

test('admin puede actualizar solo el nombre del usuario', function () {
    $user = User::factory()->create(['email' => 'original@example.com']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", [
            'name' => 'Solo Nombre',
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->name)->toBe('Solo Nombre')
        ->and($user->email)->toBe('original@example.com');
});

test('admin puede actualizar contraseña del usuario', function () {
    $user = User::factory()->create();
    $oldPassword = $user->password;

    $response = $this->actingAs($this->admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", [
            'password' => 'NuevaPassword123!',
            'password_confirmation' => 'NuevaPassword123!',
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->password)->not->toBe($oldPassword);
});

test('admin puede actualizar roles del usuario', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", [
            'roles' => ['admin', 'editor'],
        ]);

    $response->assertStatus(200);

    $user->refresh();
    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasRole('editor'))->toBeTrue()
        ->and($user->hasRole('user'))->toBeFalse();
});

test('no puede actualizar con email duplicado', function () {
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->patchJson("/api/users/{$user2->id}", [
            'email' => 'user1@example.com',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('puede actualizar usuario manteniendo su propio email', function () {
    $user = User::factory()->create(['email' => 'mismo@example.com']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->patchJson("/api/users/{$user->id}", [
            'name' => 'Nuevo Nombre',
            'email' => 'mismo@example.com',
        ]);

    $response->assertStatus(200);
});

// ============================================
// TESTS DE DESTROY
// ============================================

test('admin puede eliminar un usuario', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson("/api/users/{$user->id}");

    $response->assertStatus(200)
        ->assertJsonFragment([
            'message' => 'Usuario eliminado exitosamente',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
        'deleted_at' => null,
    ]);
});

test('destroy retorna 404 para usuario inexistente', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->deleteJson('/api/users/99999');

    $response->assertStatus(404);
});
