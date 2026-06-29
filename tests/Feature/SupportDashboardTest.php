<?php

use App\Livewire\Auth\LoginForm;
use App\Livewire\Support\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function makeSupportRole(string $externalId, string $name): Role
{
    $role = Role::query()->create([
        'name' => $name,
        'guard_name' => 'web',
    ]);

    $role->forceFill([
        'external_id' => $externalId,
        'description' => 'Perfil de suporte para validação do painel.',
    ])->save();

    return $role;
}

function writeSupportLog(string $date, string $message): string
{
    $path = storage_path("logs/laravel-{$date}.log");

    File::put($path, sprintf(
        "[%s 10:15:00] local.ERROR: %s {\"exception\":\"[object] (RuntimeException(code: 0): %s)\"}\n",
        $date,
        $message,
        $message,
    ));

    return $path;
}

test('support users can open the dashboard', function (): void {
    $role = makeSupportRole('role-suporte-n1', 'Suporte N1');
    $user = User::factory()->create([
        'email' => 'suporte.n1@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);
    $user->syncRoles([$role->name]);

    $this->actingAs($user)
        ->get('/support')
        ->assertOk()
        ->assertSee('Workspace ExtJS')
        ->assertSee('Área de Trabalho')
        ->assertSee('Painel de suporte');
});

test('guests are redirected to login from the dashboard', function (): void {
    $this->get('/support')->assertRedirect('/login/supports');
});

test('guests can open the support login page', function (): void {
    $this->get('/login/supports')
        ->assertOk()
        ->assertSee('Entrar no suporte');
});

test('backend login alias redirects to the support login page', function (): void {
    $this->get('/login')->assertRedirect('/login/supports');
});

test('login form rejects non-support profiles', function (): void {
    $user = User::factory()->create([
        'email' => 'usuario.normal@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertHasErrors(['email']);
});

test('login form authenticates support profiles', function (): void {
    $role = makeSupportRole('role-suporte-n2', 'Suporte N2');
    $user = User::factory()->create([
        'email' => 'suporte.n2@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);
    $user->syncRoles([$role->name]);

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect(route('support.dashboard'));
});

test('support dashboard shows logs grouped by day', function (): void {
    $role = makeSupportRole('role-suporte-n1', 'Suporte N1');
    $user = User::factory()->create([
        'email' => 'suporte.n1@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);
    $user->syncRoles([$role->name]);

    $olderLog = writeSupportLog('2099-01-01', 'Falha antiga de integração');
    $newerLog = writeSupportLog('2099-01-02', 'Falha recente de integração');

    try {
        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('setSection', 'logs')
            ->assertSee('Logs por dia')
            ->assertSee('02/01/2099')
            ->assertSee('Falha recente de integração')
            ->set('selectedLogDay', '2099-01-01')
            ->assertSee('01/01/2099')
            ->assertSee('Falha antiga de integração');
    } finally {
        File::delete($olderLog);
        File::delete($newerLog);
    }
});

test('support n2 can view the acl section in read only mode', function (): void {
    $role = makeSupportRole('role-suporte-n2', 'Suporte N2');
    $user = User::factory()->create([
        'email' => 'suporte.n2@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);
    $user->syncRoles([$role->name]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('setTab', 'roles')
        ->assertSee('Perfis e permissões')
        ->assertSee('Leitura da matriz de ACL');
});

test('support dashboard opens with workspace tabs', function (): void {
    $role = makeSupportRole('role-suporte-n1', 'Suporte N1');
    $user = User::factory()->create([
        'email' => 'suporte.n1@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);
    $user->syncRoles([$role->name]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Workspace ExtJS')
        ->assertSee('Área de Trabalho')
        ->assertSee('Jobs')
        ->assertSee('Logs');
});
