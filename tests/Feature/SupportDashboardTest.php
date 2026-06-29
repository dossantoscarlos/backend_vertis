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

function makeSupportUser(string $externalId, string $name, string $email): User
{
    $role = makeSupportRole($externalId, $name);

    $user = User::factory()->create([
        'email' => $email,
        'password' => 'password123',
        'status' => 'ativo',
    ]);

    $user->syncRoles([$role->name]);

    return $user;
}

function writeSupportLog(string $fileName, string $message): string
{
    $path = storage_path("logs/{$fileName}");

    File::put($path, sprintf(
        "[2026-06-27 10:15:00] local.ERROR: %s\n[2026-06-27 10:16:00] local.ERROR: %s",
        $message,
        $message . ' final'
    ));

    return $path;
}

test('support users can open the workspace shell', function (): void {
    $user = makeSupportUser('role-suporte-n1', 'Suporte N1', 'suporte.n1@vertis.com.local');

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Vertis Support Workspace')
        ->assertSee('Bases de dados')
        ->assertSee('Schema')
        ->assertSee('Status: Pronto');
});

test('guests are redirected to login from the dashboard', function (): void {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('guests can open the login page', function (): void {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Entrar no workspace');
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
    $user = makeSupportUser('role-suporte-n2', 'Suporte N2', 'suporte.n2@vertis.com.local');

    Livewire::test(LoginForm::class)
        ->set('email', $user->email)
        ->set('password', 'password123')
        ->call('login')
        ->assertRedirect(route('dashboard'));
});

test('support n2 can inspect the schema and execute read only sql', function (): void {
    $user = makeSupportUser('role-suporte-n2', 'Suporte N2', 'suporte.n2@vertis.com.local');
    $account = User::factory()->create([
        'email' => 'consulta.alvo@vertis.com.local',
        'password' => 'password123',
        'status' => 'ativo',
    ]);

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->assertSee('Schema')
        ->assertSee('Bases de dados')
        ->call('setTab', 'sql')
        ->assertSet('activeTab', 'sql')
        ->set('sqlQuery', "select email from users where email = '{$account->email}' limit 1;")
        ->call('executeQuery')
        ->assertSet('sqlAffectedRows', 1)
        ->assertSet('sqlRows.0.email', $account->email)
        ->assertSee($account->email);
});

test('support n2 cannot access the debug tab', function (): void {
    $user = makeSupportUser('role-suporte-n2', 'Suporte N2', 'suporte.n2@vertis.com.local');

    Livewire::actingAs($user)
        ->test(Dashboard::class)
        ->call('setTab', 'debug')
        ->assertSet('activeTab', 'schema');
});

test('support n3 can use terminal commands and log selection', function (): void {
    $user = makeSupportUser('role-suporte-n3', 'Suporte N3', 'suporte.n3@vertis.com.local');
    $logFile = writeSupportLog('vertis-support-debug.log', 'Falha de integração');

    try {
        Livewire::actingAs($user)
            ->test(Dashboard::class)
            ->call('setTab', 'debug')
            ->assertSet('activeTab', 'debug')
            ->set('debugCommand', 'printf vertis')
            ->call('runTerminal')
            ->assertSet('debugExitCode', 0)
            ->assertSet('debugOutput', 'vertis')
            ->assertSee('Baixar arquivo completo')
            ->call('selectTail', $logFile)
            ->assertSet('tailPath', $logFile);
    } finally {
        File::delete($logFile);
    }
});
