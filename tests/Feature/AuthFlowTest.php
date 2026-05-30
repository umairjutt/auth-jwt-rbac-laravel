<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::firstOrCreate(['name' => 'user']);
    Role::firstOrCreate(['name' => 'admin']);
});

test('user can register and receives token', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Alice',
        'email' => 'alice@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertCreated()->assertJsonStructure(['user', 'token']);
    expect(User::where('email', 'alice@example.com')->exists())->toBeTrue();
});

test('user can login with correct credentials', function () {
    User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'password']);

    $this->postJson('/api/auth/login', [
        'email' => 'bob@example.com',
        'password' => 'password',
    ])->assertOk()->assertJsonStructure(['token']);
});

test('login fails with wrong password', function () {
    User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'password' => 'password']);

    $this->postJson('/api/auth/login', [
        'email' => 'bob@example.com',
        'password' => 'wrong',
    ])->assertUnprocessable();
});

test('admin can list users, regular user cannot', function () {
    $admin = User::create(['name' => 'Admin', 'email' => 'a@a.com', 'password' => 'password']);
    $admin->assignRole('admin');

    $regular = User::create(['name' => 'Reg', 'email' => 'r@r.com', 'password' => 'password']);
    $regular->assignRole('user');

    $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users')->assertOk();
    $this->actingAs($regular, 'sanctum')->getJson('/api/admin/users')->assertForbidden();
});
