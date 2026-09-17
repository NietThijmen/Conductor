<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('lists users', function () {
    User::factory()->count(3)->create();

    $this->artisan('user:manage', ['action' => 'list'])
        ->assertSuccessful();
});

it('creates a user', function () {
    $this->artisan('user:manage', [
        'action' => 'create',
        '--name' => 'Jane Doe',
        '--email' => 'jane@example.com',
        '--password' => 'secure-password',
    ])
        ->assertSuccessful();

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Jane Doe')
        ->and(Hash::check('secure-password', $user->password))->toBeTrue();
});

it('shows a user', function () {
    $user = User::factory()->create();

    $this->artisan('user:manage', [
        'action' => 'show',
        '--id' => $user->id,
    ])
        ->assertSuccessful();
});

it('deletes a user when forced', function () {
    $user = User::factory()->create();

    $this->artisan('user:manage', [
        'action' => 'delete',
        '--id' => $user->id,
        '--force' => true,
    ])
        ->assertSuccessful();

    expect(User::find($user->id))->toBeNull();
});

it('rejects invalid actions', function () {
    $this->artisan('user:manage', ['action' => 'invalid'])
        ->assertFailed();
});
